<?php

class createSubdomainsStringTask extends sfBaseTask {
  public function configure()
  {
    $this->namespace = 'domus';
    $this->name = 'create-subdomains-string';
    $this->briefDescription = 'Creates list of geo-subdomains.';
    $this->detailedDescription = '';

    $this->host = is_file('/etc/apache2/sites-enabled/domus') ? '.domus.server.garin.su' : '.mesto.ru';
  }

  public function execute($arguments = array(), $options = array()) {
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    sfContext::createInstance($configuration);
    new sfDatabaseManager($configuration);
    $conn = Doctrine_Manager::connection();

    $regions = $conn->prepare('select id, name from region');
    $regions->execute(array());
    $file    = fopen(sfConfig::get('sf_config_dir') . '/region.yml', 'w');
    $file_nb = fopen(sfConfig::get('sf_config_dir') . '/region_nb.yml', 'w');

    fwrite($file,    "all:\n");
    fwrite($file_nb, "all:\n");
    while($region = $regions->fetch(Doctrine::FETCH_ASSOC)) {
      $parts = explode(" ", $region['name']);
      switch($region['id']) {
        case 7:   //Go next
        case 9:   //Go next
        case 86:  //Go next
        case 89:
         $name = $name_nb = str_replace('-','', $parts[0]);
        break;

        case 50:
          $name = array_shift($parts);
          $name_nb = 'в подмосковие'; // Yes, I'm sure. )
        break;

        case 77:
          $name    = 'москва';
          $name_nb = 'в москве';
        break;

        case 78: $name = $name_nb = 'петербург'; break;

        default: $name = $name_nb = array_shift($parts);
      }
      fwrite($file,    sprintf("  %s: %s\n", Toolkit::slugify($name),    $region['id']));
      fwrite($file_nb, sprintf("  %s: %s\n", Toolkit::slugify($name_nb), $region['id']));
    }

    fclose($file);
    fclose($file_nb);
    $list = sfYaml::load(sfConfig::get('sf_config_dir') . '/region.yml');
    foreach ($list['all'] as $key => $value) {
      echo sprintf('%s%s www.%1$s%2$s ', $key, $this->host);
     }
    echo "\n";
  }
}