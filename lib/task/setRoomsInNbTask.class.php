<?php

class setRoomsInNbTask extends sfBaseTask
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

    $this->namespace        = 'nb';
    $this->name             = 'setRooms';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [setRoomsInNb|INFO] task does things.
Call it with:

  [php symfony setRoomsInNb|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

    $lots = Doctrine::getTable('Lot')->createQuery()
      ->andWhere('type = ?', 'new_building-sale')
      ->andWhere('user_id = ?', 33544)
      ->execute();

    foreach ($lots as $lot) {
      $rooms = $lot->getLotInfoField(76);
      $this->logSection('Lot', sprintf('#%d, rooms %s', $lot->id, $rooms));
      if (null == $rooms) {
        $lot_info_param = new LotInfo();
        $lot_info_param->lot_id = $lot->id;
        $lot_info_param->field_id = 76;
        $lot_info_param->value = '1, 2, 3, 4';
        $lot_info_param->save();
      }
    }
    // add your code here
  }
}
