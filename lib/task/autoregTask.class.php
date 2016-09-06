<?php

/**
 * Task for mass fake user creation
 *
 * @author kmad
 */
class autoregTask extends sfBaseTask {

  public function configure() {
    $this->addOptions(array(
      new sfCommandOption('count', null, sfCommandOption::PARAMETER_REQUIRED, 'Users count'),
    ));

    $this->namespace        = 'domus';
    $this->name             = 'autoreg';
    $this->briefDescription = 'Generate fake users';
  }

  public function execute($arguments = array(), $options = array()) {
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);

    if (empty($options['count'])) throw new Exception('Parameter "count" is required');
    $count = $options['count'];
    mt_srand();
    for ($i = 0; $i < $count; $i++) {
      $user = new User();
      $user->type = 'owner';
      $user->created_at = date('Y-m-d H:i:s');
      $user->phone = mt_rand();
      $user->email = $user->phone . '@domus.ru';
      $user->name = 'autoreg';
      $user->password = md5(microtime());
      $user->save();
      $user->free();
    }
  }

}
?>
