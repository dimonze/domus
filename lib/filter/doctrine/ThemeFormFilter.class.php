<?php

/**
 * Theme filter form.
 *
 * @package    filters
 * @subpackage Theme *
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 11675 2008-09-19 15:21:38Z fabien $
 */
class ThemeFormFilter extends BaseThemeFormFilter
{
  public function configure()
  {
    $this->setWidgets(array(
      'title' => new sfWidgetFormFilterInput()
    ));
    $this->widgetSchema['title']->setLabel('Название');
  }
}