<?php
class DomusMail {

  /**
   *
   * @return Zend_Mail
   */
  public static function create() {
    ProjectConfiguration::registerZend();
    $mail = new Zend_Mail('utf-8');
    $mail->setFrom('no-reply@mesto.ru', 'mesto.ru');
    return $mail;
  }

}