<?php

class parseYandexTask extends sfBaseTask
{
  protected $gearman;

  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
      new sfCommandOption('region_id', null, sfCommandOption::PARAMETER_REQUIRED, 'regions separated by coma', ''),
      new sfCommandOption('type', null, sfCommandOption::PARAMETER_REQUIRED, 'lot types separated by coma', ''),
      new sfCommandOption('page', null, sfCommandOption::PARAMETER_REQUIRED, 'page', 1),
      new sfCommandOption('today', null, sfCommandOption::PARAMETER_NONE, 'only today lots'),
      new sfCommandOption('yesterday', null, sfCommandOption::PARAMETER_NONE, 'only yesterday lots'),
    ));

    $this->namespace        = 'parse';
    $this->name             = 'yandex';
    $this->briefDescription = '';
    $this->detailedDescription = '';
  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    sfContext::createInstance($configuration);

    $this->gearman = sfGearmanProxy::getClient();
    $this->gearman->setCompleteCallback(array($this, 'completeItem'));
    $method = $this->gearman->getMethodName('parse_yandex_lot');

    // TMP
    $log_file = sfConfig::get('sf_log_dir') . '/yandex_source';
    if (file_exists($log_file)) {
      rename($log_file, sprintf('%s_%s', $log_file, filectime($log_file)));
    }
    // \TMP

    if (empty($options['region_id'])) {
      $regions = array(
        77, 50, 78, 47, 4, 28, 29, 30, 31, 32, 33, 34, 35, 36, 79, 75, 37, 38, 39,
        40, 41, 9, 42, 43, 44, 23, 24, 45, 46, 48, 49, 51, 83, 52, 54, 55, 56, 57,
        58, 59, 25, 60, 1, 2, 3, 5, 6, 7, 8, 10, 11, 12, 13, 14, 15, 16, 17, 19,
        61, 62, 63, 64, 65, 66, 67, 26, 68, 69, 70, 71, 72, 18, 73, 27, 86, 74, 20,
        21, 87, 89, 76,
      );
    }
    else {
      $regions = explode(',', $options['region_id']);
    }

    if (empty($options['type'])) {
      $types = array('apartament-sale', 'apartament-rent', 'house-sale', 'house-rent');
    }
    else {
      $types = explode(',', $options['type']);
    }


    foreach ($regions as $region_id) {
      foreach ($types as $type) {
        $parser = new Parser_Yandex($type, $region_id);

        $page = $options['page'];
        do {
          $this->logSection(sprintf('%s:%s', $region_id, $type), sprintf('page %d', $page));

          try {
            $items = $parser->getItems($page);
            $skip = false;
          }
          catch (Exception $e) {
            $this->logSection('error', $e->getMessage(), null, 'ERROR');
            $skip = true;
            $items = array();
          }

          $has_today = $has_yesterday = false;
          foreach ($items as $item) {
            $has_today = $has_today || 'сегодня' == $item['date'];
            $has_yesterday = $has_yesterday || 'вчера' == $item['date'];
            $date_ok = ($options['today'] && 'сегодня' == $item['date'])
                    || ($options['yesterday'] && 'вчера' == $item['date']);

            if (!($options['today'] || !$options['yesterday']) || $date_ok) {
              $this->logSection('task', $item['title']);
              $this->gearman->addTask($method, serialize(array(
                'item'      => $item,
                'type'      => $type,
                'region_id' => $region_id,
              )));
            }
          }

          if ($options['today'] && !$has_today) {
            $skip = true;
          }
          elseif ($options['yesterday'] && !($has_today || $has_yesterday)) {
            $skip = true;
          }

          if ($parser->hasNextPage() && !$skip) {
            $page++;
          }
          elseif ($parser->skipCurrentQuery()) {
            $page = $options['page'];
          }
          else {
            break;
          }

        } while (true);

        if (!$this->gearman->runTasks()) {
          throw new Exception('Run failed!');
        }
      }
    }
  }

  public function completeItem($task)
  {
    $data = $task->data();

    if (!strpos($data, ':')) {
      return false;
    }

    list($id, $url) = explode(':', $data, 2);

    if (is_numeric($id)) {
      $this->logSection('item', sprintf('%s => %d', $url, $id));
    }
    else {
      $this->logSection('error', sprintf('%s => %s', $id, $url), null, 'ERROR');
    }
  }
}
