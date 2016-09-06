<?php

class parseCianAgentsTask extends sfBaseTask
{
  protected
    $url = 'http://www.cian.ru/cat.php',
//    $url = 'http://192.168.3.35/cianagents.html',
    $users = array(),
    $csv_file = '/tmp/cianUsers.csv',    
    $context = '';
  
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),      
    ));

    $this->namespace        = 'parse';
    $this->name             = 'parseCianAgents';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [parseCianAgents|INFO] task does things.
Call it with:

  [php symfony parseCianAgents|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    ini_set('memory_limit', '512M');
    ini_set('max_execution_time', 0);
    $context_options = array(
      'http' => array(
        'proxy' => 'tcp://188.72.68.74:8192',
        'request_fulluri' => true,
      ),
    );
    $this->context = stream_context_create($context_options);
    $html = file_get_contents($this->url, false, $this->context);
    preg_match_all('/<a[^>]*rel="nofollow".*href="cat.php.*"[^>]*>*(\d+)<\/a>/iUs', $html, $pages);
    $nb_pages = array_pop($pages[1]);
    unset($pages);    
    for ($page = 1; $page <= $nb_pages; $page++){
      echo 'Parsing page #' . $page;
      //sleep 5 sec for 'I fair parser'
//      sleep(5);
      $this->fetch($page);      
    }

    if (count($this->users) > 0){
      $this->pressUsers();
    }    
  }

  protected function fetch($page = 1)
  { 
    $url = $this->url . '?p=' . $page;
    
    $this->logSection('fetch', $url);
    for ($tries = 50; $tries > 0; $tries--) {
      if ($html = file_get_contents($url, false, $this->context)) {
        break;
      }
      else {
        $this->logSection('fetch', 'Retrying...', null, 'ERROR');
        sleep(1);
      }
    }
    
    if (!$html) {
      throw new Exception('Fetch failed');
    }

    preg_match_all('/<tr[^>]*id="tr_.*[^>]*>(.*)<\/tr>/iUs', $html, $lots);
    foreach ($lots[1] as $id => $lot){
      echo 'Lot #' . $id . PHP_EOL;
      //Get all phones
      preg_match('/<td[^>]*.*id=".*_contacts".*[^>]*>(.*)<\/td>/iUs', $lot, $phones);

      //Get array of rialtor phones
      preg_match_all('/<a[^>]*.*callto.*[^>]*>(.*)<\/a>/iUs', $phones[1], $phone);
      $phones = $phone[1];

      preg_match('/<td[^>]*.*id=".*_metro".*[^>]*>.*"color:.*green"[^>]*>(.*)<\/b>.*<\/td>/iUs', $lot, $regions);
      if (isset($regions[1])){
        $region = iconv('CP1251', 'UTF-8',$regions[1]);
      }
      else {
        $region = 'Москва';
      }
      preg_match('/<td[^>]*.*id=".*_comment".*[^>]*>.*<a[^>]*.*id_user=(?P<user_id>.*)".*[^>]*>(?P<user_name>.*)<\/a>.*<\/td>/iUs', $lot, $user);
      $user_id = $user['user_id'];
      $user_name = iconv('CP1251', 'UTF-8', str_replace('&gt;', '', str_replace('&lt;', '', $user['user_name'])));
      $this->users[$user_id]['name'][] = $user_name;
      foreach ($phones as $phone){
        $this->users[$user_id]['phones'][] = $phone;
      }
      $this->users[$user_id]['region'][] = $region;

      echo sprintf(
        'User #%s,  Name: %s, Phones: %s, Region: %s',
        $user_id,
        $user_name,
        $phones,
        $region
      );
      echo '-----Lot-------' . PHP_EOL;
    }
    echo "Unique users: " . count($this->users);
    unset($html, $lots, $phones, $user_id, $user_name, $region);
  }
  
  /**
   * Press array users for remove not unique elements
   */
  protected function pressUsers()
  {
    $fp = fopen($this->csv_file, 'a');
    if (is_resource($fp)) {
      fwrite($fp,  iconv('UTF-8', 'CP1251', 'IdCian;Имя;Телефоны;Регионы' . PHP_EOL));
      foreach ($this->users as $id => $user){
        echo 'Press user #' . $id . PHP_EOL;
        $names   = iconv('UTF-8', 'CP1251', implode(', ', array_unique($user['name'])));
        $regions = iconv('UTF-8', 'CP1251', implode(', ', array_unique($user['region'])));
        $phones  = iconv('UTF-8', 'CP1251', implode(', ', array_unique($user['phones'])));
        fwrite($fp, sprintf('%s;%s;%s;%s' . PHP_EOL, $id, $names, $phones, $regions));
        echo 'Write CSV SUCCESS' . PHP_EOL;

      }
      fclose($fp);
    }
    else {
      throw new Exception('Cannot open file ' . $this->csv_file);
    }
  }
}
