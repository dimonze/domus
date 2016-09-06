<?php

/**
 * Theme form.
 *
 * @package    form
 * @subpackage Theme
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class ThemeForm extends BaseThemeForm
{
  public function configure()
  {
    $this->setWidgets(array(
      'id'    =>  new sfWidgetFormInputHidden(),
      'title' =>  new sfWidgetFormInputText()
    ));
    $this->widgetSchema['title']->setLabel('Название');
    $this->widgetSchema->setNameFormat('theme[%s]');
  }
}