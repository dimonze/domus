<?php

/**
 * Post filter form.
 *
 * @package    filters
 * @subpackage Post *
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 11675 2008-09-19 15:21:38Z fabien $
 */
class PostFormFilter extends BasePostFormFilter
{
  public function configure()
  {
    $this->setWidget('status', new sfWidgetFormChoice(array(
      'choices' => array_merge(array('' => ''), Post::$status)
    )));
    
    $this->widgetSchema['in_yandex_rss'] = new sfWidgetFormChoice(array(
      'choices' => array('' => 'неважно', 1 => 'да', 0 => 'нет'))
    );
    $this->widgetSchema['in_rambler_rss'] = new sfWidgetFormChoice(array(
      'choices' => array('' => 'неважно', 1 => 'да', 0 => 'нет'))
    );
    $this->widgetSchema['in_google_xml'] = new sfWidgetFormChoice(array(
      'choices' => array('' => 'неважно', 1 => 'да', 0 => 'нет'))
    );
    
    $this->widgetSchema['in_yandex_rss']->setLabel('В Yandex.Новостях');
    $this->widgetSchema['in_rambler_rss']->setLabel('В Rambler.Новостях');
    $this->widgetSchema['in_google_xml']->setLabel('В Google.Новостях');
  }
}