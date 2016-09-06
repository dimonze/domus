<?php

class NotifyWorker extends sfGearmanWorker
{
  public
    $name = 'notify',
    $methods = array('notify_price', 'notify_search');

  protected function configure()
  { 
    $config = new sfRoutingConfigHandler();
    $routes = $config->evaluate(array(sfConfig::get('sf_apps_dir').'/frontend/config/routing.yml'));
    $this->routing = new sfPatternRouting(new sfEventDispatcher());
    $this->routing->setRoutes($routes);
    
    $this->sphinx_client = new DomusSphinxClient(array('limit' => 1000)); 
  }

  public function doNotifyPrice(GearmanJob $job)
  {
    $this->startJob();
    $settings = unserialize($job->workload());
    $this->resultset = $settings['lots'];
    $subscriber = $settings['subscriber'];
    
    $past = substr($settings['period'], 0, strlen($settings['period']) -2);
    $past = str_replace('dai', 'day', $past);
    $past = strtotime(sprintf('-1 %s', $past)) + 7 * 3600; # 7 hours cron shift
    
    $body = $this->makeBodyLot(
      $subscriber['email'],
      array(
        'type'  => $settings['period'],
        'from'  => $past,
        'to'    => time()
      )
    );

    if ($body) {
      $body .= $this->unsubscribeLink($subscriber,  $settings['period']);
      $data['subject']  = sprintf('Обновления на сайте %s', sfConfig::get('app_site'));
      $data['receiver'] = $subscriber['email'];
      $data['message']  = $body;
      
      sfGearmanProxy::doBackground('pm_send', array(
        'data'       => $data,
        'free_send'  => true
      ));
    }
    
    $this->routing->shutdown();

    $this->completeJob($job);
  }

  public function doNotifySearch(GearmanJob $job)
  {
    $this->startJob();
    $settings = unserialize($job->workload());
    
    $past = substr($settings['period'], 0, strlen($settings['period']) -2);
    $past = str_replace('dai', 'day', $past);
    $past = strtotime(sprintf('-1 %s', $past)) + 7 * 3600; # 7 hours cron shift

    $messages = array();
    
    foreach ($settings['subscribers'] as $subscriber) {
      $message = $this->makeBodySearch(
        $subscriber->pk,
        array(
          'type'  => $settings['period'],
          'from'  => $past,
          'to'    => time()
        )
      );

      if ($message['body']) {
        if (!isset($messages[$subscriber->email])) {
          $messages[$subscriber->email] = array('header' => null, 'bodies' => array());
          
          $message['body'] .= $this->unsubscribeLink($subscriber, $settings['period']);

          $messages[$subscriber->email]['header'] = $message['header'];
          $messages[$subscriber->email]['bodies'][] = $message['body'];
        }
      }
    }

    foreach ($messages as $email => $message) {
      $body = implode("<br /><hr /><br />\n", array_merge(
        array($message['header']),
        $message['bodies']
      ));

      $data['subject']  = sprintf('Новые объявления на сайте %s', sfConfig::get('app_site'));
      $data['receiver'] = $email;
      $data['message']  = $body;

      sfGearmanProxy::doBackground('pm_send', array(
        'data'       => $data,
        'free_send'  => true
      ));
    }
    
    $this->routing->shutdown();

    $this->completeJob($job);
  }
  
  protected function makeBodyLot($email, array $period)
  {
    $items = $this->getLotLogForUser($email, $period['type']);
    if (!count($items)) {
      return false;
    }

    $template = $this->getHeaderFormat($period);
    $template = "<p>$template были изменены цены на следующие объявления:<p>\n";
    $template .= "<ul>\n";
    foreach ($items as $pk => $item) {
      $lot = Doctrine::getTable('Lot')->find($pk);
      
      $title = $lot->address_full;
      if (empty($lot->slug)) {
        $url = $this->routing->generate('show_lot', $lot->toArray(), true);
      }
      else {
        $url = $this->routing->generate('show_lot_slug', $lot->toArray(), true);
      }
      
      $url = substr($url, 0, strpos($url, '?'));
      $title = sprintf('<a href="http://%s%s">%s</a>', sfConfig::get('app_site'), $url, $title);

      $prices = '';
      foreach ($item['changes'] as $change) {
        $prices .= sprintf(
          "<li>%s%s (%s%s)</li>\n",
          $period['type'] == 'daily' ? '' : sprintf('<i>%s</i> - ', date('d.m', strtotime($change['created_at']))),
          Currency::formatPrice($change['new'], 'RUR'),
          $change['old'] > $change['new'] ? '-' : '+',
          Currency::formatPrice($change['new'] - $change['old'], 'RUR')
        );
      }
      $template .= sprintf("<li>%s<ul>%s</ul></li>\n", $title, $prices);
      
      $item = null;
    }
    $template .= '</ul>';

    return $template;
  }
  
  protected function unsubscribeLink($subscriber, $period, $model = 'Lot', $field = 'price') {
    if ($subscriber instanceof Notification) {
      $options['email'] = $subscriber->email;
      $options['pk'] = $subscriber->pk;
      $field = $subscriber->field;
      $model = $subscriber->model;
      
      $subscriber = $options;
    }
    $link = '<br/><br/>';
    $url = $this->routing->generate('unsubscribe', array(
      'email' => str_replace('.', '~', urlencode($subscriber['email'])),
      'hash'  => md5($model . $field .
                     $subscriber['pk'] . $period),
    ));
    $link .= sprintf('<a href="http://%s%s">Отписаться от рассылки</a>', sfConfig::get('app_site'), $url);
    return $link;
  }
  
  protected function getLotLogForUser($email, $period)
  {
    $rows = Doctrine::getTable('Notification')->createQuery()
                    ->select('pk')
                    ->andWhere('period = ?', $period)
                    ->andWhere('model = ?', 'Lot')
                    ->andWhere('field = ?', 'price')
                    ->andWhere('email = ?', $email)
                    ->fetchArray();
    $results = array();
    foreach ($rows as $row) {
      if (isset($this->resultset[$row['pk']])) {
        $results[$row['pk']] = $this->resultset[$row['pk']];
      }
      
      $row = null;
    }
    return $results;
  }
  
  protected function getHeaderFormat(array $period)
  {
    switch ($period['type']) {
      case 'daily':
        $template = sprintf('За %s', date('d.m.Y', $period['from']));
        break;
      case 'weekly':
        $template = sprintf(
          'За неделю с %s по %s',
          date('d.m.Y', $period['from']),
          date('d.m.Y', $period['to'])
        );
        break;
      case 'monthly':
        $template = sprintf(
          'За месяц с %s по %s',
          date('d.m.Y', $period['from']),
          date('d.m.Y', $period['to'])
        );
        break;
      default:
        throw new sfException('Unsupported period type '.$period['type']);
    }
    return $template;
  }

  protected function makeBodySearch($search, array $period)
  {
    # reset previous filters
    $this->sphinx_client->ResetFilters();
    
    $search = Doctrine::getTable('Search')->find($search);
    if (!$search->param) {
      return false;
    }

    $param = array_merge($search->param, array(
      'created_at' => array(
        'from' => $period['from'],
        'to'   => $period['to'],
      )
    ));

    $rs = $this->sphinx_client->search($param);

    if (!$rs['total']) {
      return false;
    }


    $header = $this->getHeaderFormat($period);
    $header = "<p>$header были добавлены следующие объявления:<p>\n";

    $template = '<p><b>Параметры поиска:</b></p>';
    $template .= $search->text;

    $template .= '<p><b>Новые объявления:</b></p>';
    $template .= "<ul>\n";
    
    foreach ($rs['matches'] as $item) {
      $title = $item['attrs']['address1'] . ', ' . $item['attrs']['address2'];
      if (empty($item['attrs']['slug'])) {
        $url = $this->routing->generate('show_lot', $item, true);
      }
      else {
        $item['slug'] = $item['attrs']['slug'];
        unset($item['attrs']);
        $url = $this->routing->generate('show_lot_slug', $item, true);
      }
      $url = substr($url, 0, strpos($url, '?'));
      
      $template .= sprintf("<li><a href='http://%s%s'>%s</a></li>\n", sfConfig::get('app_site'), $url, $title);
      $item = null;
    }
    $template .= '</ul>';

    return array(
      'header' => $header,
      'body'   => $template
    );
  }
}