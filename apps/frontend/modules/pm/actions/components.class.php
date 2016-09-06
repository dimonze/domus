<?php

class pmComponents extends sfComponents{
  
  public function executeProfilemessages() {
    $this->messages = Doctrine::getTable('PM')->createQuery()
    ->select('id, sent_at, subject, message, priority')
    ->andWhere('priority != ?', 'none')
    ->andWhere('red = ?', 0)
    ->andWhere('receiver = ?', sfContext::getInstance()->getUser()->id)
    ->limit(3)
    ->orderBy('sent_at desc')
    ->execute();
  }
  
}
