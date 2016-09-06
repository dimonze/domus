<?php
/**
 * Compare list of emails with emails of users from db
 *
 * @package    domus
 * @subpackage task
 */
class compareEmailsTask extends sfBaseTask
{
  protected
    $file_src = null,
    $file_res = null,
    $config   = null;

  protected function configure()
  {
    $this->namespace = 'domus';
    $this->name = 'compare-emails';
    $this->briefDescription = 'Compare list of emails with emails of users from db';
    $this->detailedDescription = '';

    $this->file_src = sfConfig::get('sf_root_dir').'/emails.txt';
    $this->file_res = sfConfig::get('sf_root_dir').'/compare_results.log';

    $this->addOption('file', null, sfCommandOption::PARAMETER_OPTIONAL, 'txt file with list of emails for compare', null);
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    if (!empty($options['file'])) $this->file_src = $options['file'];

    if (!file_exists($this->file_src)) {
      throw new Exception('File does not exist: '.$this->file_src);
    } elseif (!$file_src = fopen($this->file_src, 'r')) {
      throw new Exception('Can\'t open file for reading: '.$this->file_src);
    }
    if (!$file_res = fopen($this->file_res, 'w')) {
      throw new Exception('Can\'t create file for writing: '.$this->file_res);
    }

    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);
    
    $users = Doctrine::getTable('User')
            ->createQuery('u')
            ->select('u.email')
            ->where('u.email <> \'\'')
            ->fetchArray();

    $count = 0;
    while(!feof($file_src)) {
      $compare = trim(fgets($file_src));
      if ($compare == '') continue;

      foreach ($users as $key => $user) {
        if (strcasecmp($compare, $user['email']) === 0) {
          fwrite($file_res , $user['email']."\r\n") or die('Can\'t write data to file: '.$file_res);
          unset($users[$key]);
          $count++;
          break;
        }
      }
    }

    fclose($file_res);
    fclose($file_src);

    print "Done! Found $count matches\n";
  }
}