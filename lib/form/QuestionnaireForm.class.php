<?php

/**
 * QuestionnaireAnswers form.
 *
 * @package    form
 * @subpackage QuestionnaireAnswers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class QuestionnaireForm extends PostForm
{

  public function configure() {
    parent::configure();
    $this->widgetSchema->setNameFormat('questionnaire[%s]');
    $this->widgetSchema['status'] = new sfWidgetFormChoice(
        array(
          'choices' => array(
            'publish' => 'Опубликован',
            'inactive' => 'Опубликован, но не активен',
            'not_publish' => 'Неопубликован'
          )
      ));
    $this->widgetSchema['title']->setLabel('Вопрос');
    $this->setDefault('created_at', date_parse(date('Y/m/d H:i')));

    unset(
      $this['user_id'],
      $this['author_name'],
      $this['themes_list'],
      $this['post_text'],
      $this['post_type'],
      $this['blog_id'],
      $this['rating'],
      $this['less_count'],
      $this['author_id'],
      $this['tags_list'],
      $this['deleted'],
      $this['section'],
      $this['is_primary'],
      $this['lid'],
      $this['subtitle'],
      $this['source'],
      $this['source_url'],
      $this['title_photo'],
      $this['title_photo_source'],
      $this['title_photo_source_url'],
      $this['signature'],
      $this['tags'],
      $this['on_main'],
      $this['in_yandex_rss'],
      $this['in_rambler_rss'],
      $this['in_google_xml'],
      $this['region_list'],
      $this['slug']
    );

    foreach($this->object['Answers'] as $key => $answer) {
      $field_name = 'answer_'.$answer->id;
      
      $form = new QuestionnaireAnswersForm($answer);
      unset($form['post_id']);
      $this->embedForm($field_name, $form);

      $label = '<a href="#" class="del-answer">удалить</a>
                <input type="hidden" name="answer_id" value="'.$answer->id.'" />';

      $this->widgetSchema->setLabel($field_name, $label);
    }

  }

  public function save($con = null) {
    $values = $this->getValues();
    if ($this->getObject()->isNew()) {
      $values['created_at'] = isset($values['created_at']) ? $values['created_at'] : parse_date(date('Y/m/d H:i'));
    }
    $values['post_type'] = 'questionnaire';
    $values['post_text'] = 0;
    $this->updateObject($values);

    return parent::save($con);
  }

  public function updateObject($values = null) {
    if (is_null($values)) {
      $values = $this->values;
    }
    $values = $this->processValues($values);
    $this->object->fromArray($values);

    // embedded forms
    $this->updateObjectEmbeddedForms($values);

    return $this->object;
  }
}