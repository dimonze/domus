<?php

class updateUserRatingTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    // $this->addArguments(array(
    //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
    // ));
    
    $this->addArgument('user_id', sfCommandArgument::REQUIRED, 'id of the user for recalculation');

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      // add your own options here
    ));

    $this->namespace        = 'domus';
    $this->name             = 'updateUserRating';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [updateUserRating|INFO] task does things.
Call it with:

  [php symfony updateUserRating|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();
    $user_id = $arguments['user_id'];
    $user = Doctrine::getTable('User')->findOneById($user_id);
    if ($user) {
      $user->rerateRating();
      echo "Rerating for user " . $user->name . " completed\n";
    }
    else
      echo "User with id #" . $user_id . " is not found\n";
    // add your code here
  }
}
