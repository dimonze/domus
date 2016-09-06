<?php

class generateListOfLandingPagesTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    // $this->addArguments(array(
    //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
    // ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      // add your own options here
    ));

    $this->namespace        = 'domus';
    $this->name             = 'generateListOfLandingPages';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [generateListOfLandingPages|INFO] task does things.
Call it with:

  [php symfony generateListOfLandingPages|INFO]
EOF;

  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($this->configuration);

    $path    = LandingPage::getCachePath();
    $options = array(
      'limit'       => 100000,
      'offset' => 0
    );

    if(!is_dir($path)) {
      mkdir($path, 0777);
    }

    foreach(LandingPage::$preprocessed_regions as $region) {
      $this->logSection('region', $region);
      $r_path = LandingPage::getRegionFolder($region);
      if(!is_dir($r_path)) {
        mkdir($r_path, 0777);
      }
      foreach(LandingPage::$suptypes as $type) {
        $file = fopen(LandingPage::getFilenameFor($region, $type), 'w');
        $this->sphinx = new DomusSphinxClient($options);
        $this->sphinx->getLandingPages(array(
          'region' => $region,
          'mode' => $type,
          'level'  => 'all'
        ));
        $result = $this->sphinx->getRes();

        if(!empty($result['matches'])) {
          $this->logSection('type', sprintf("%s: %d", $type, count($result['matches'])));
          foreach($result['matches'] as $lot) {
            fwrite($file, sprintf("<p><a href=\"%s/%s/%s\">%s</a></p>\n",
              Toolkit::getGeoHostByRegionId($lot['attrs']['region_id'], true),
              $lot['attrs']['type'],
              $lot['attrs']['url'],
              $lot['attrs']['h1']
            ));
          }
        }
        fclose($file);
      }
    }
  }
}
