<?php

class ExportLotsToYandexTask extends sfBaseTask {

  protected $_not_for_yandex_users = array(
    9819, 21460, 22875, 24834, 26692, 37479, //Александр недвижимость
    32588, //realtor84
  );

  protected function configure() {
    $this->addArgument('host', sfCommandArgument::OPTIONAL, 'host name', 'mesto.ru');

    $this->namespace = 'export';
    $this->name = 'LotsToYandex';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [exportlots|INFO] task does things.
Call it with:
  [php symfony exportlots|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array()) {
    ini_set('max_execution_time', 0);
    ini_set('memory_limit', '2048M');
    $start_mic = microtime(true);
    $this->config = sfYaml::load(sfConfig::get('sf_config_dir') . '/yandex.realty.export.yml');
    $regions = array();
    $user_types = array();
    $sources_to_export = array();
    $sources = array();
    $lots = array();
    $lot_types = array();
    $this->cian = array();

    $client = sfGearmanProxy::getClient();
    $client->setCompleteCallback(array($this, 'logResult'));

    foreach ($this->config['all']['regions'] as $region_id => $region) {
      if ($region['value']) {
        $regions[] = $region_id;
      }
    }

    foreach ($this->config['all']['users']['types'] as $user_type => $user) {
      if ($user['value']) {
        $user_types[] = $user_type;
      }
    }

    if (!empty($this->config['all']['lot_type'])) {
      foreach ($this->config['all']['lot_type'] as $type => $val) {
        if (!in_array($type, array('apartament-sale', 'apartament-rent', 'house-sale', 'house-rent'))) {
          continue;
        }
        if ($val['value']) {
          $lot_types[] = $type;
        }
      }
    }

    foreach ($this->config['all']['users']['sources'] as $source) {
      if ($source['value']) {
        $sources_to_export[] = $source['email'];
      }
      $sources[] = $source['email'];
    }

    $export_partners = $this->config['all']['users']['partners'][UserGroup::PARTNERS_ID]['value'];

    $sourses_not_to_export = array_diff($sources, $sources_to_export);

    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);
    $conn = Doctrine_Manager::connection();

    $context->getConfiguration()->loadHelpers('Url');
    $file_name = '8TqPnqgq9Aucm7ZRh7feCQFqH5wnofYZzzhYMhrlKvew9Um6u2';
    $tmp_file = 'yandex_tmp';

    $file_yandex = fopen(sfConfig::get('sf_data_dir') . DIRECTORY_SEPARATOR . $tmp_file . '.yrl', 'w');

    $this->logSection('job', 'file ' . $tmp_file . ' opened');
    $this->logSection('job', 'begin to write');

    $begin = '<?xml version="1.0" encoding="utf-8"?>' . "\n"
      . '<realty-feed xmlns="http://webmaster.yandex.ru/schemas/feed/realty/2010-06">' . "\n"
      . '<generation-date>' . date('c') . '</generation-date>' . "\n";

    fwrite($file_yandex, $begin);
    fclose($file_yandex);

    foreach ($regions as $region_id) {
      $limit = $this->config['all']['regions'][$region_id]['limit'];
      $this->log('== ========');
      $this->logSection('region', $region_id);

      $tmp = $user_types;
      foreach ($tmp as $key => $val)
        $tmp[$key] = '?';
      $uts = implode(', ', $tmp);

      $tmp = $sourses_not_to_export;
      foreach ($tmp as $key => $val)
        $tmp[$key] = '?';
      $snte = implode(', ', $tmp);

      $tmp = $lot_types;
      foreach ($tmp as $key => $val)
        $tmp[$key] = '?';
      $lt = implode(', ', $tmp);


      $prepare = 'SELECT l.id, l.organization_link, l.organization_contact_phone, u.email, l.user_id, u.phone FROM lot l
       LEFT JOIN user u ON u.id = l.user_id
       WHERE l.status = ?
         AND l.region_id = ?
         AND (l.deleted_at = ? OR l.deleted_at IS NULL)
         AND (u.deleted_at = ? OR u.deleted_at IS NULL)
         AND (u.inactive = ? OR u.inactive IS NULL)
         AND l.updated_at > ? 
         AND l.user_id NOT IN (?) ';

      if(!$export_partners) {
        $prepare .= ' AND (u.group_id != '
          . UserGroup::PARTNERS_ID
          . ' OR u.group_id IS NULL) ';
      }
      if(!empty ($uts)) $prepare .= ' AND u.type IN (' . $uts . ')';
      if(!empty ($snte)) $prepare .= ' AND u.email NOT IN (' . $snte . ')';
      if(!empty ($lt)) $prepare .= ' AND l.type IN (' . $lt . ')';

      $prepare .= ' ORDER BY l.created_at DESC, l.active_till ASC LIMIT ' . $limit;

      $lots = $conn->prepare($prepare);

      $exec = array(
        'active', $region_id, 0, 0, 0,
        date('Y-m-d H:i:s', strtotime('-2 month')), implode(',', $this->_not_for_yandex_users));

      foreach ($user_types as $type)
        $exec[] = $type;
      foreach ($sourses_not_to_export as $sourset)
        $exec[] = $sourset;
      foreach ($lot_types as $type)
        $exec[] = $type;

      $lots->execute($exec);
      $this->logSection('avalible', $lots->rowCount());

      $i = 0;
      while ($lot = $lots->fetch(Doctrine::FETCH_ASSOC)) {
        // check lots from badboy ;) no action, log only
        if ($this->checkBadPhones($lot)){
          continue;
        }
        if ($lot['email'] == 'realtor84@gmail.com') {
          $this->logSection('ERROR', '32588 found: #' . $lot['user_id'], 200, 'ERROR');
        }

        if ($lot['email'] == 'yandex@mesto.ru' && preg_match('/cian\.ru/', $lot['organization_link'])) {
          $this->logSection('cian-lot', 'Lot #' . $lot['id'] . ' no export');
        }
        else {
          $this->logSection('lot', 'EXPORT #' . $lot['id']);
          $client->addTask($client->getMethodName('export_lot_to_yandex'), serialize(array(
            'id' => $lot['id'],
            'host' => $arguments['host'],
            'file_name' => $tmp_file,
            'sources' => $sources
          )));
        }

        if (++$i % 5000 == 0) {
          $this->logSection('job', 'Run gearman tasks');
          $client->runTasks();
        }
      }

      $client->runTasks();
    }

//    if ($cian_lots = count($this->cian)) {
//      $this->logSection('cian', 'Lots #' . $cian_lots);
//      $start_kill   = '2012-07-03 00:00:00';
//      $current_date = date('j');
//      $dedline      = '2012-07-07 00:00:00';
//      $cian_kill    = $cian_lots;
//      for($day = 3; $day < 7; $day++ ) {
//        if ($current_date == 4) {
//          $cian_kill = -(round($cian_lots/4));
//          break;
//        }
//        if ($current_date == 5) {
//          $cian_kill = round($cian_lots/2);
//          break;
//        }
//        if ($current_date == 6) {
//          $cian_kill = round($cian_lots/4);
//          break;
//        }
//        if ($current_date == 7) {
//          $cian_kill = 0;
//          break;
//        }
//      }
//
//      $this->logSection('slice', 'Slice cian from 0 to ' . $cian_kill);
//      $cian = array_slice($this->cian, 0, $cian_kill);
//
//      $this->logSection('cian', 'Add tasks to export cian lots');
//      $i = 0;
//      foreach ($cian as $lot_id) {
//        $client->addTask($client->getMethodName('export_lot_to_yandex'), serialize(array(
//          'id' => $lot_id,
//          'host' => $arguments['host'],
//          'file_name' => $tmp_file,
//          'sources' => $sources
//        )));
//
//        if (++$i % 5000 == 0) {
//          $this->logSection('job', 'Run gearman tasks');
//          $client->runTasks();
//        }
//      }
//
//      $client->runTasks();
//      $this->logSection('cian', 'Export cian FINISH');
//    }

    $file_yandex = fopen(sfConfig::get('sf_data_dir') . DIRECTORY_SEPARATOR . $tmp_file . '.yrl', 'a');
    fwrite($file_yandex, PHP_EOL . '</realty-feed>');
    fclose($file_yandex);

    unlink(sfConfig::get('sf_upload_dir') . DIRECTORY_SEPARATOR . $file_name . '.yrl');

    rename(
      sfConfig::get('sf_data_dir') . DIRECTORY_SEPARATOR . $tmp_file . '.yrl',
      sfConfig::get('sf_upload_dir') . DIRECTORY_SEPARATOR . $file_name . '.yrl'
    );

    $this->logSection('job', 'file closed. all is good');
    $end_mic = microtime(true);
    $time_work = ($end_mic - $start_mic) / 60;
    $this->logSection('job', 'Time work: ' . $time_work . ' minutes');
  }

  protected function memstat($count) {
    $this->logSection('memory', 'current memory usage: ' . memory_get_usage() . ' on lot ' . $count);
  }

  public function logResult($task)
  {
    $row = $task->data();
    $is_error = false !== strpos($row, 'err');
    $this->logSection($is_error ? 'error' : 'ok', $row, 200, $is_error ? 'ERROR' : 'INFO');
  }

  public function checkBadPhones($lot)
  {
    $bad_phones = array(4959430360, 4959714407, 9851776660, 9262091728);

    $phones = array();
    if (!empty($lot['organization_contact_phone'])) {
      $phones = explode(',', $lot['organization_contact_phone']);
    }
    $phones[] = $lot['phone'];
    foreach ($phones as $phone) {
      $lot_phones[] = substr(preg_replace('/[^0-9]/', '', $phone), 1);
    }
    
    foreach ($bad_phones as $bad_phone) {
      if (in_array($bad_phone, $lot_phones)) {
        $this->logSection(
          'BAD_PHONE',
          'I found it! In lot #' . $lot['id'] . ' - ' . $bad_phone . ' ',
          200,
          'ERROR'
        );
        return true;
      }
      else {
        echo $bad_phone . ' not found' . PHP_EOL;
      }
    }

    return false;
  }
}
