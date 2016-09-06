<?php

/**
 * News form.
 *
 * @package    form
 * @subpackage News
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class NewsForm extends PostForm
{
  public function configure() {
    parent::configure();
    $this->widgetSchema->setNameFormat('news[%s]');
    $this->setWidget('post_text', new sfWidgetFormTextareaTinyMCE(
      array(
        'width' => 600,
        'height'=> 350,
        'theme'  => 'advanced',
        'config' => 'extended_valid_elements : "iframe[src|width|height|name|align]", ' . BaseForm::$TinyMCEConfig
      ),
      array(
        'class' => 'tiny_mce_c1'
      )
    ));
    $this->setWidget('lid', new sfWidgetFormTextareaTinyMCE(
      array(
        'width' => 600,
        'height'=> 150,
        'theme' => 'advanced',
        'config' => BaseForm::$TinyMCEConfig
      ),
      array(
        'class' => 'tiny_mce_c1'
      )
    ));
    $this->setWidget('section', new sfWidgetFormChoice(array(
      'choices' => array(
        'Новости рынка'     => 'Новости рынка',
        'Новости компаний'  => 'Новости компаний',
        'Новости портала'   => 'Новости портала'
       ),
       'label' => 'Раздел'
    )));

    $this->setValidator('tags', new sfValidatorString(array(
        'required' => false
    )));
    $this->setValidator('lid', new sfValidatorString(array(
        'max_length' => 400,
        'required' => false
      ), array(
        'max_length' => 'Слишком длинное значение (макс. 400 символов)',
      )
    ));
    $this->setValidator('source', new sfValidatorString(array(
        'max_length' => 200,
        'required' => false
      ), array(
        'max_length' => 'Слишком длинное значение (макс. 400 символов)',
      )
    ));
    $this->setValidator('source_url', new sfValidatorUrl(array(
        'max_length' => 200,
        'required' => false
      ), array(
        'invalid'     => 'Адрес источника должен быть url в формате http://www.example.ru',
        'max_length'  => 'Слишком длинное значение (макс. 200 символов)'
      )
    ));
    $this->setValidator('signature', new sfValidatorString(array(
        'max_length' => 150,
        'required' => false
      ), array(
        'max_length' => 'Слишком длинное значение (макс. 150 символов)',
        'required' => 'Обязательное поле')
    ));
    $this->setValidator('subtitle', new sfValidatorString(array(
        'max_length' => 255,
        'required' => false
      ), array(
        'max_length' => 'Слишком длинное значение (макс. 255 символов)',
        'required' => 'Обязательное поле')
    ));
    $this->setValidator('title_photo_source', new sfValidatorString(array(
        'max_length'  => 200,
        'required'    => false,
      ), array(
        'max_length'  => 'Слишком длинное значение (макс. 400 символов)',
        'required'    => 'Обязательное поле')
    ));
    $this->setValidator('title_photo_source_url', new sfValidatorUrl(array(
        'max_length'  => 200,
        'required'    => false,
      ), array(
        'invalid'     => 'Адрес источника должен быть url в формате http://www.example.ru',
        'max_length'  => 'Слишком длинное значение (макс. 200 символов)'
      )
    ));
    $this->widgetSchema['post_text']->setLabel('Текст новости');
    $this->widgetSchema['lid']->setLabel('Лид');

    $this->getWidgetSchema()->setPositions(array(
      'created_at', 'main_region_id', 'region_list', 'section', 'themes_list',
      'title', 'subtitle', 'slug', 'lid', 'post_text', 'source', 'source_url',
      'title_photo', 'title_photo_source', 'title_photo_source_url',
      'signature', 'status', 'is_primary', 'on_main', 'tags',
      'less_count', 'deleted_at', 'tags_list', 'rating',
      'author_id', 'id', 'post_type', 'user_id', 'author_name', 'in_yandex_rss',
      'in_google_xml', 'in_rambler_rss', 'title_h1', 'title_seo', 'description', 'keywords'
    ));

    unset(
      $this['post_type'],
      $this['blog_id'],
      $this['rating'],
      $this['less_count'],
      $this['author_id'],
      $this['tags_list'],
      $this['deleted_at'],
      $this['user_id'],
      $this['author_name']
    );

    if (!sfContext::getInstance()->getUser()->hasCredential('redactor-news-actions')){
      unset($this->widgetSchema['created_at']);
    }
    else {
      $this->setDefault('created_at', date_parse(date('Y/m/d H:i')));
      $this->setDefault('is_primary', false);
      $this->setDefault('status', 'publish');
    }
    $this->getValidatorSchema()->setOption('allow_extra_fields', true);
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();
    $this->setDefault('tags', $this->object->tags_string);
  }

  public function updateObject($values = null)
  {
    if (is_null($values))
    {
      $values = $this->values;
    }
    $values = $this->processValues($values);
    $this->object->fromArray($values);

    // embedded forms
    $this->updateObjectEmbeddedForms($values);

    return $this->object;
  }

  public function save($con = null)
  {
    $values = $this->getValues();
    if ($this->getObject()->isNew()){      
      $values['created_at'] = isset($values['created_at']) ? $values['created_at'] : parse_date(date('Y/m/d H:i'));
    }
    $values['post_type']  = 'news';
    $values['author_id']  = null;
    $this->updateObject($values);

    $cache = new DomusCache();
    foreach($values['region_list'] as $region) {
      $prefix = 'homepage_1_'.$region.'_';
      $key = sprintf('%s%s_%s', $prefix, 'news', 'list');
      $cache->remove($key);
    }
    
    return parent::save($con);
  }
}