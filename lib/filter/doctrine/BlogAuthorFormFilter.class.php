<?php

/**
 * BlogAuthor filter form.
 *
 * @package    filters
 * @subpackage BlogAuthor *
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 11675 2008-09-19 15:21:38Z fabien $
 */
class BlogAuthorFormFilter extends BaseBlogAuthorFormFilter
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
    $this->setWidget('deleted_at', new sfWidgetFormChoice(array(
      'choices' => array(
        '' => '',
        1 => 'да',
        0 => 'нет'
      ))
    ));
    $this->widgetSchema->setLabels(array(
      'name' => 'ФИО',
      'company' => 'Компания',
      'post'  => 'Должность',
      'description' => 'Описание',
      'author_type' => 'Тип автора',
      'deleted_at' => 'Удалён'
    ));
    unset(
      $this['user_id'],
      $this['photo']
    );
  }
}