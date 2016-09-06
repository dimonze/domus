<?php

class GenerateLinkatorCombinationsTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
    ));

    $this->namespace        = 'sitemap';
    $this->name             = 'GenerateLinkatorCombinations';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [GenerateLinkatorCombinations|INFO] task does things.
Call it with:

  [php symfony GenerateLinkatorCombinations|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $file     = file(sfConfig::get('sf_data_dir') . '/autolinkator.csv');
    $linkator = sfConfig::get('sf_config_dir') . '/autolinkator.yml';
    $words2yml = array();

    foreach ($file as $line) {
      list($word, $url) = explode(';', $line);
      $word = trim(str_replace('"', '', $word));
      $url = trim(str_replace('"', '', $url));

      $word = preg_replace('/\n/', '', $word);
      $url = preg_replace('/\n/', '', $url);

      $words2yml[$word] = $url;
    }

    var_dump($words2yml);
    file_put_contents($linkator, sfYaml::dump($words2yml, 4));
    $this->logSection('yml', 'Yaml file SUCCESS');
  }
}
