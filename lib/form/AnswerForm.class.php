<?php

/**
 * AnswerForm form.
 *
 * @package    form
 */
class AnswerForm extends PostForm
{
  public function configure() {
    parent::configure();
    $this->widgetSchema->setNameFormat('answer[%s]');

    unset(
      $this->widgetSchema['id'],
      $this->widgetSchema['post_type'],
      $this->widgetSchema['blog_id'],
      $this->widgetSchema['rating'],
      $this->widgetSchema['less_count'],
      $this->widgetSchema['status'],
      $this->widgetSchema['author_id'],
      $this->widgetSchema['news_lid'],
      $this->widgetSchema['news_subtitle'],
      $this->widgetSchema['news_signature'],
      $this->widgetSchema['news_source'],
      $this->widgetSchema['tags_list'],
      $this->widgetSchema['region_id'],
      $this->widgetSchema['themes_list'],
      $this->widgetSchema['title_photo_id']
    );
    if (!sfContext::getInstance()->getUser()->hasCredential('redactor-answer-allactions')){
      unset($this->widgetSchema['created_at']);
    } 
  }
  public function save($con = null) {
    
  }
}