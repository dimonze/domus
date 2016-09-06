<?php

/**
 * QA filter form.
 *
 * @package    filters
 * @subpackage QA *
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 11675 2008-09-19 15:21:38Z fabien $
 */
class QuestionnaireFormFilter extends PostFormFilter
{
  public function configure() {
    parent::configure();

    $this->setWidget('created_at', new sfWidgetFormFilterDate(array(
      'from_date' => new sfWidgetFormDate(),
      'to_date' => new sfWidgetFormDate(),
      'with_empty' => false,
      'template' => 'от %from_date%<br />до %to_date%'))
    );
    $this->setWidget('on_main', new sfWidgetFormChoice(array(
      'choices' => array(
        '' => 'неважно',
        1 => 'да',
        0 => 'нет'
      )))
    );
    
    $this->setWidget('status', new sfWidgetFormChoice(array(
      'choices' => Post::$status
    )));

    $this->setWidget('post_type', new sfWidgetFormInput(array('type' => 'hidden', 'label' => 'Опросы')));
    $this->widgetSchema->setLabels(array(
      'author_name' => 'Анонимный пользователь',
      'user_id'     => 'Зарегистрированный пользователь',
      'created_at'  => 'Дата создания',
      'status'      => 'Статус',
      'themes_list' => 'Тема',
      'region_list' => 'Регион',
    ));
    unset(
      $this['user_id'],
      $this['blog_id'],
      $this['title'],
      $this['post_text'],
      $this['rating'],
      $this['source'],
      $this['lid'],
      $this['subtitle'],
      $this['signature'],
      $this['less_count'],
      $this['deleted'],
      $this['tags_list'],
      $this['author_id'],
      $this['title_photo'],
      $this['title_photo_source'],
      $this['title_photo_source_url'],
      $this['source_url'],
      $this['is_primary'],
      $this['on_main'],
      $this['section'],
      $this['author_email'],
      $this['event_date'],
      $this['event_place'],
      $this['event_contact'],
      $this['region_list'],
      $this['in_yandex_rss'],
      $this['in_rambler_rss'],
      $this['in_google_xml'],
      $this['themes_list'],
      $this['user_id'],
      $this['author_name']
    );
  }
}