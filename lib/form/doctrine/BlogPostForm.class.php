<?php

/**
 * BlogPost form.
 *
 * @package    form
 * @subpackage BlogPost
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class BlogPostForm extends BaseBlogPostForm
{

  public function configure() {
    $this->widgetSchema->setNameFormat('blog_post[%s]');

    $this->widgetSchema['lid'] = new sfWidgetFormTextarea();
    $this->widgetSchema['body'] = new sfWidgetFormTextareaTinyMCE(
        array(
          'width' => 607,
          'height' => 350,
          'theme' => 'simple'
        ),
        array(
          'class' => 'tiny_mce_c1'
        )
    );
    $this->validatorSchema['title'] = new sfValidatorString(
        array('required' => true),
        array(
          'required' => 'Укажите название<li class="error_img"></li>',
          'invalid' => 'Недопустимые символы<li class="error_img"></li>'
      ));
    $this->validatorSchema['lid'] = new sfValidatorString(
        array('required' => true),
        array(
          'required' => 'Напишите анонс к записи<li class="error_img"></li>',
          'invalid' => 'Недопустимые символы<li class="error_img"></li>'
      ));
    $this->validatorSchema['body'] = new sfValidatorString(
        array('required' => true),
        array(
          'required' => 'Напишите текст<li class="error_img"></li>',
          'invalid' => 'Недопустимые символы<li class="error_img"></li>'
      ));

    $user = sfContext::getInstance()->getUser();
    $this->widgetSchema['blog_id']->setDefault($user->Blog->id);

    $this->widgetSchema['status'] = new sfWidgetFormChoice(array('choices' => BlogPost::$status));

    unset(
      $this['title_photo'],
      $this['title_photo_source'],
      $this['title_photo_source_url'],
      $this['deleted']
    );

    $app_name = sfContext::getInstance()->getConfiguration()->getApplication();
    if(!$user->hasCredential('create-blog-actions') || 'frontend' == $app_name) {
      $this->widgetSchema['title_h1'] = new sfWidgetFormInputText();
      $this->validatorSchema['title_h1'] = new sfValidatorString(array(
        'max_length'  => 255,
        'required'    => false
      ));
      $this->widgetSchema['description'] = new sfWidgetFormInputText();
      $this->validatorSchema['description'] = new sfValidatorString(array(
        'max_length'  => 255,
        'required'    => false
      ));
      $this->widgetSchema['keywords'] = new sfWidgetFormInputText();
      $this->validatorSchema['keywords'] = new sfValidatorString(array(
          'max_length'  => 255,
        'required'    => false
      ));
      unset(
        $this['status'],
        $this['created_at'],
        $this['updated_at']
      );
    }
    
    $this->setDefault('created_at', date('Y-n-j G:i:s'));
    $this->widgetSchema->setLabels(array(
      'created_at'  => 'Дата создания',
      'title'       => 'Заголовок',
      'lid'         => 'Анонс',
      'body'        => 'Текст',
      'blog_id'     => 'Блог',
      'status'      => 'Статус',
      'theme_id'    => 'Тематика',
      'title_h1'    => 'Заголовок h1'
    ));
  }

  public function getErrorsArray() {
    return AjaxForm::getErrorsArray($this);
  }

  public function save($con = null) {
    $values = $this->getValues();
    if ($this->getObject()->isNew()) {
      $values['created_at'] = isset($values['created_at']) ? $values['created_at'] : date('Y-m-d H:i:s');      
    }
    $values['updated_at'] = date('Y-m-d H:i:s');
    $values['status'] = 'moderate';
    $this->updateObject($values);
    return parent::save($con);
  }

  public function updateObject($values = null) {
    if (is_null($values)) {
      $values = $this->values;
    }
    $values = $this->processValues($values);
    $this->object->fromArray($values);
    $this->updateObjectEmbeddedForms($values);

    return $this->object;
  }

}