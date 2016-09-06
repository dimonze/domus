<?php

/**
 * Maintain the project!
 *
 * @package    domus
 * @subpackage task
 */
class sfProjectMaintainTask extends sfBaseTask
{
  protected
    $config = null,
    $_data  = array();

  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->namespace = 'domus';
    $this->name = 'maintain';
    $this->briefDescription = 'Keep all data up-to-date';
    $this->detailedDescription = '';
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    ini_set('memory_limit', '1024M');
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);
    $conn = Doctrine_Manager::connection();

    $config = new sfRoutingConfigHandler();
    $routes = $config->evaluate(array(sfConfig::get('sf_apps_dir') . '/frontend/config/routing.yml'));
    $this->routing = new sfPatternRouting(new sfEventDispatcher());
    $this->routing->setRoutes($routes);

    $data = array();
    $count = 0;

    $client = sfGearmanProxy::getClient();
    $client->setCompleteCallback(array($this, 'getLot'));

    $lots = $conn->prepare(
      'SELECT id FROM lot
      WHERE status = ? 
        AND (deleted_at = ? OR deleted_at IS NULL)
        AND active_till < ?'
    );
    $lots->execute(array('active', 0, date('Y-m-d', strtotime('+1 day'))));
    $i = 0;
    while ($id = $lots->fetchColumn()) {
      $this->logSection('deactivate-lot', 'Deactivating lot #' . $id);
      $client->addTask($client->getMethodName('deactivate_lot'), $id);
      $count++;
      if ($count % 5000 == 0) {
        $client->runTasks();
      }
    }
    $client->runTasks();

    foreach ($this->_data as $message) {
      list($email, $name) = unserialize($message['user']);
      DomusMail::create()
        ->addTo($email)
        ->setSubject(sprintf('Срок действия ваших объявлений на сайте %s истек', sfConfig::get('app_site')))
        ->setBodyHtml($this->buildMessageText($message['user'], $message['lots']))
        ->send();
      $this->logSection('send-message', 'Send message to ' . $email);
    }

    $this->logSection('inactive-lots', sprintf('Inactive for %d lots', $count));
  }

  public function getLot($task)
  {
    if (null != $task->data()) {
      $data = unserialize($task->data());
      if (is_array($data)){
        list($user_id, $type, $id, $address_full, $email, $name) = $data;
        if (!isset($this->_data[$user_id])) {
          $this->_data[$user_id] = array(
            'user' => serialize(array($email, $name)),
            'lots' => array()
          );
        }
        if (!isset($this->_data[$user_id]['lots'][$type])) {
          $this->_data[$user_id]['lots'][$type] = array();
        }

        $this->_data[$user_id]['lots'][$type][$id] = $address_full;
        $this->logSection('deactivate-lot', 'Lot #' . $id . ' is DEACTIVATED');
      }
      else {
        $this->logSection('deactivate-lot', $data, null, 'ERROR');
      }
    }
    else {
      $this->logSection('deactivate-lot', 'User setting: expire_notify is not enabled.', null, 'ERROR');
    }
  }

  private function buildMessageText($user = null, array $lots_by_type)
  {
    if (null != $user) {
      list($email, $name) = unserialize($user);
      $text = '<p>Здравствуйте, %s!</p>
        <p>Срок действия следующих объявлений истек:</p>
        %s
        <p>Просмотреть все свои объявления вы можете в разделе
          <a href="http://%s%s">Мои объявления</a></p>
        <p>Отписаться от уведомлений вы можете в своем <a href="http://%s%s">профиле</a>.</p>';

      $lots_text = '';
      $types = sfConfig::get('app_lot_types');
      foreach ($lots_by_type as $type => $lots) {
        $lots_text .= sprintf('<b>%s</b><ul>', $types[$type]['plural']);
        foreach ($lots as $lot_id => $lot_address) {
          $lots_text .= sprintf('<li>%s <a href="http://%s%s">продлить</a></li>' . "\n",
            $lot_address,
            sfConfig::get('app_site'),
            $this->routing->generate(null, array(
              'module' => 'lot',
              'action' => 'setactive',
              'id'     => $lot_id,
            ))
          );
        }
        $lots_text .= '</ul>';
      }

      return sprintf($text,
        $name,
        $lots_text,
        sfConfig::get('app_site'),
        $this->routing->generate(null, array('module' => 'lot', 'action' => 'my')),
        sfConfig::get('app_site'),
        $this->routing->generate(null, array('module' => 'user', 'action' => 'profile'))
      );
    }
  }
}
