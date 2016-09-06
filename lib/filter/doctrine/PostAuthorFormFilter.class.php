<?php

/**
 * PostAuthor filter form.
 *
 * @package    filters
 * @subpackage PostAuthor *
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 11675 2008-09-19 15:21:38Z fabien $
 */
class PostAuthorFormFilter extends BasePostAuthorFormFilter
{
  public function configure()
  {
    $this->setWidget('author_type', new sfWidgetFormChoice(array(
      'choices' => array(
        '' => '',
        'author' => 'Автор',
        'expert' => 'Эксперт'
      ))
    ));
    $this->setWidget('deleted', new sfWidgetFormChoice(array(
      'choices' => array(
        '' => '',
        1 => 'да',
        0 => 'нет'
      ))
    ));
    
    $this->validatorSchema['deleted'] = new sfValidatorPass();
    
    $this->widgetSchema->setLabels(array(
      'name' => 'ФИО',
      'company' => 'Компания',
      'post'  => 'Должность',
      'description' => 'Описание',
      'author_type' => 'Тип автора',
      'deleted' => 'Удалён'
    ));
    unset(
      $this['user_id'],
      $this['photo']
    );
  }
}