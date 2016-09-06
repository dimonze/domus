<?php

class setMaxPriceFromMinPriceInNbTask extends sfBaseTask
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
    $this->name             = 'setMaxPriceFromMinPrice';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [setMaxPriceFromMinPriceInNb|INFO] task does things.
Call it with:

  [php symfony setMaxPriceFromMinPriceInNb|INFO]
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
      $min_price = $lot->getLotInfoField(70);
      $max_price = $lot->getLotInfoField(71);
      $this->logSection('Lot', sprintf('#%d, min_price %s, max_price %s', $lot->id, $min_price, $max_price));
      if (null == $max_price) {
        $lot_info_param = new LotInfo();
        $lot_info_param->lot_id = $lot->id;
        $lot_info_param->field_id = 71;
        $lot_info_param->value = $min_price;
        $lot_info_param->save();
      }
    }
    // add your code here
  }
}
