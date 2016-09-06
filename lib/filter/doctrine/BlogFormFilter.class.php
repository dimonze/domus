<?php

/**
 * Blog filter form.
 *
 * @package    filters
 * @subpackage Blog *
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 11675 2008-09-19 15:21:38Z fabien $
 */
class BlogFormFilter extends BaseBlogFormFilter
{
  public function configure()
  {
    $this->setWidgets(array(
      'user_id'   => new sfWidgetFormDoctrineChoice(array('model' => 'User', 'add_empty' => true)),
      'title'     => new sfWidgetFormFilterInput(),
      'status'  => new sfWidgetFormChoice(array('choices' => array_merge(array('' => ''), Blog::$_status))),      
    ));
    $this->setValidators(array(
      'title'     => new sfValidatorPass(array('required' => false)),
      'user_id' => new sfValidatorDoctrineChoice(array(
        'required' => false,
        'model' => 'User',
        'column' => 'id'
      )),
      'status'  => new sfValidatorChoiceKeys(array('required' => false, 'choices' => Blog::$_status)),     
    ));

    $this->widgetSchema->setLabels(array(
      'title'   => 'Название блога',
      'user_id' => 'Пользователь',
      'status'  => 'Статус',     
    ));
    $this->widgetSchema->setNameFormat('blog_filters[%s]');
    parent::configure();

    unset($this['user_id']);
  }
}