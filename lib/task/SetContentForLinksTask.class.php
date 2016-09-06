<?php

class SetContentForLinksTask extends sfBaseTask
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

    $this->namespace        = 'seo';
    $this->name             = 'SetContentForLinks';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [SetContentForLinks|INFO] task does things.
Call it with:

  [php symfony SetContentForLinks|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);
    $conn = Doctrine_Manager::connection();
    
    $file = file_get_contents(sfConfig::get('sf_data_dir') . '/generation.csv');
    $this->logSection('main', 'File opened. Strlen: ' .mb_strlen($file));
    $pattern = '/"(.*?)";"(.*?)"\n/is';
    preg_match_all($pattern, $file, $matches, PREG_SET_ORDER);
    
    $this->logSection('main', 'File parsed. Lines: ' . count($matches));
    
    $sel = $conn->prepare(
      'SELECT id FROM sitemap_seo_data WHERE link = ?'
    );
    
    $ins = $conn->prepare(
      'UPDATE sitemap_seo_data SET content = ? WHERE id = ?'
    );
    
    
    foreach ($matches as $line)
    {
      $link    = $line[1];
      $content = $line[2];

      $this->logSection('Lines loop', 'Link: ' . $link);
      
      $sel->execute(array($link));
      foreach($sel->fetchAll(Doctrine::FETCH_ASSOC) as $row)
      {
        $ins->execute(array($content, (int)$row['id']));
      }
    }
  }
}
