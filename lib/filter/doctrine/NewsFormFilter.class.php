<?php

/**
 * News filter form.
 *
 * @package    filters
 * @subpackage News *
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 11675 2008-09-19 15:21:38Z fabien $
 */

class NewsFormFilter extends PostFormFilter
{
  public function configure() {
    parent::configure();

    $this->setWidget('created_at', new sfWidgetFormFilterDate(array(
      'from_date' => new sfWidgetFormDate(),
      'to_date' => new sfWidgetFormDate(),
      'with_empty' => false,
      'template' => 'от %from_date%<br />до %to_date%'))
    );
    $this->setWidget('is_primary', new sfWidgetFormChoice(array(
      'choices' => array(
        '' => 'неважно',
        1 => 'да',
        0 => 'нет'
      )))
    );
    $this->setWidget('on_main', new sfWidgetFormChoice(array(
      'choices' => array(
        '' => 'неважно',
        1 => 'да',
        0 => 'нет'
      )))
    );
    $this->setWidget('post_type', new sfWidgetFormInput(array('type' => 'hidden', 'label' => 'Новости')));
    $this->setDefault('post_type', 'news');
    $this->widgetSchema->setLabels(array(
      'created_at' => 'Дата создания',
      'status' => 'Статус',
      'is_primary' => 'Главная новость',
      'on_main' => 'На главной',
      'themes_list' => 'Тема',
      'region_list' => 'Регион',
      'section' => 'Раздел'
    ));
    unset(
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
      $this['user_id'],
      $this['author_name']
    );
  }
}
?>