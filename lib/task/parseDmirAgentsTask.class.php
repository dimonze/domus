<?php

class parseDmirAgentsTask extends sfBaseTask
{
  protected $fp;
  
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),      

      new sfCommandOption('url', null, sfCommandOption::PARAMETER_OPTIONAL, 'The parse url', 'http://realty.dmir.ru/agents/'),
    ));
    $this->namespace        = 'parse';
    $this->name             = 'parseDmirAgents';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [parseDmirAgents|INFO] task does things.
Call it with:

  [php symfony parseDmirAgents|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {    
    $html = file_get_contents($options['url']);
    $agents_array = array();
    $pages = array();
    preg_match('/<span.*class="marked2">\d+<\/span>.*(?P<pages>\d{2})&nbsp;<a/', $html, $pages);    
    $pages = $pages[1];
    $this->fp = fopen('/tmp/agents.csv', 'a');
    fwrite($this->fp,  iconv('UTF-8', 'CP1251', 'ФИО;Компания;Должность;Телефоны;Регионы' . PHP_EOL));
    for ($page = 1; $page <= $pages; $page++){
      $this->fetch($page, $options['url']);
    }
  }

  protected function fetch($page = null, $url)
  {
    echo 'Parse ' . $url . '?page=' . $page . '...' . PHP_EOL;
    $html = file_get_contents($url . '?page=' . $page);

    $agents = array();
    preg_match_all('/<li[^>]*class="lstitem"[^>]*>(.*)<\/div><\/div><\/li>/iUs', $html, $agents);
    
    foreach ($agents[1] as $id => $agent){
      preg_match('/<a[^>]*class="realtorname01"[^>]*>(?P<fio>.*)<\/a>/iUs', $agent, $fio);
      preg_match('/<p[^>]*class="realtor01dscr01"[^>]*>(?P<company>.*)<\/p>/iUs', $agent, $info);
      $fio = preg_replace('/\s+/', '',$fio['fio']);
      $fio = iconv('UTF-8', 'CP1251', preg_replace('/&nbsp;/', ' ',$fio));
      $desc = explode(',', $info['company']);
      $company = '';
      if (!preg_match('/\d\s*\(\d+\)\s*\d+/iUs', $desc[0])) {
        $company =  iconv('UTF-8', 'CP1251', array_shift($desc));
      }
      $phones = '';
      $post = '';
      foreach ($desc as $key => $value){
        $value = trim(preg_replace('/\s+/', ' ', $value));
        if (preg_match('/\d\s*\(\d+\)\s*\d+/', $value)){
          $phones .= $value . ', ' ;
        }
        else {
          $post = iconv('UTF-8', 'CP1251', $value);
        }
      }
      preg_match('/<th[^>]*>Регионы:<\/th><td[^>]*>(.*)<\/td>/iUs', $agent, $regions);
      if (count($regions) > 0){
        $regions = trim(preg_replace('/\s+/', ' ', $regions[1]));
        $regions = iconv('UTF-8', 'CP1251', preg_replace('/,$/', '', $regions));
      }
      else {
        $regions = '';
      }
      fwrite($this->fp, sprintf('%s;%s;%s;%s;%s%s', $fio, $company, $post, $phones, $regions, PHP_EOL));
    }

    echo 'SUCCESS parse page.' . PHP_EOL;
    echo 'Sleep 15 sec';
    sleep(15);
    
  }
  public function __destruct() {
    if (is_resource($this->fp)){
      fclose($this->fp);
    }
  }
}
