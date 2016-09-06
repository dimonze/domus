<?php

class GenerateSitemapWordCombinationsTask extends sfBaseTask
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
    $this->name             = 'GenerateSitemapWordCombinations';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [GenerateSitemapWordCombinations|INFO] task does things.
Call it with:

  [php symfony GenerateSitemapWordCombinations|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {    
    
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);
    $conn = Doctrine_Manager::connection();
    
    ini_set('memory_limit', '1024M');
    ini_set('max_execution_time', 0);
    
    $query = $conn->prepare('
      TRUNCATE TABLE sitemap_seo_data
    ');
    $query->execute();
    
    $file = file(sfConfig::get('sf_data_dir') . '/generation.csv');
    $words = sfConfig::get('sf_config_dir') . '/words_combinations_for_sitemap.yml';
    $words2yml = array();
    foreach ($file as $line) {
      list($word, $title, $h1) = explode(';', $line);
      $word = str_replace('"', '', $word);
      $title = str_replace('"', '', $title);
      $h1 = str_replace('"', '', $h1);

      $word   = trim(str_replace(array('в []', '[]'), '', $word));
      $title  = trim(preg_replace('/\n/', '', $title));
      $h1  = trim(preg_replace('/\n/', '', $h1));
      if ('' != $word && '' != $title && '' != $h1) {
        $type = '';
        if (stristr($word, 'комна')
          || stristr($word, 'кварт')) {
          $type = 'apartament-' . $this->getType($word);
        }
        else if (stristr($word, 'офис')
          || stristr($word, 'магазин')
          || stristr($word, 'здани')
          || stristr($word, 'склад')
          || stristr($word, 'кафе')
          || stristr($word, 'помещен')
          || stristr($word, 'коммерч')
          || stristr($word, 'площад')
          || stristr($word, '[вид недвижимости]')) {

          $type = 'commercial-' . $this->getType($word);
        }
        else if (stristr($word, 'дом') || stristr($word, 'участ')) {
          $type = 'house-' . $this->getType($word);
        }
        $words2yml[] = array($word => array(
            'type'  =>  $type,
            'title' =>  $title,
            'h1'    =>  $h1
          )
        );
        $query = $conn->prepare(
          'INSERT INTO sitemap_seo_data
           SET
             section = ?,
             title = ?,
             h1 = ?,
             link = ?,
             level = ?
          '
        );
        
        if(stristr($word, 'улиц'))
          $level = 'street';
        else if (stristr($word, 'регион'))
          $level = 'region';
        else if (stristr($word, 'район'))
          $level = 'district';
        else if (stristr($word, 'город'))
          $level = 'city';
        else if (stristr($word, 'деревн'))
          $level = 'village';        
        
        $query->execute(array($type, $title, $h1, $word, $level));
      }
    }

    file_put_contents($words, sfYaml::dump($words2yml, 4));
    $this->logSection('yml', 'Yaml file SUCCESS');
  }

  protected function getType($word = null)
  {
    if (null != $word) {
      if (stristr($word, 'прода') 
        || stristr($word, 'покуп')
        || stristr($word, 'купи')
        || stristr($word, 'купи'))
              
        return 'sale';
     
      if (stristr($word, 'аренда')
        || stristr($word, 'снять'))
        return 'rent';
     
    }
    return false;
  }
}
