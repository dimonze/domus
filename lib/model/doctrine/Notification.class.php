<?php

class Notification extends BaseNotification
{
  public static
    $models  = array('Lot' => 'объявление', 'Search' => 'поиск'),
    $periods = array('daily' => 'ежедневно', 'weekly' => 'еженедельно', 'monthly' => 'ежемесячно');

  public function getSearch()
  {
    return Doctrine::getTable('Search')->find($this->pk);
  }

  public function getModelText()
  {
    return self::$models[$this->model];
  }

  public function getPeriodText()
  {
    return self::$periods[$this->period];
  }
}