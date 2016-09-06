<?php

/**
 * ExpertArticle form.
 *
 * @package    form
 * @subpackage ExpertArticle
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class ExpertArticleForm extends PostForm
{
  public function configure()
  {
    $this->setWidgets(array(
      'title' => new sfWidgetFormInputText(),
      'region_list' => new sfWidgetFormDoctrineChoice(array(
          'model' => 'Region',
          'expanded' => true,
          'add_empty' => 'Выбрать все регионы',
          'multiple' => true
        )
      ),
      'slug' => new sfWidgetFormInputText(),
      'lid'   => new sfWidgetFormTextareaTinyMCE(
        array(
          'width' => 600,
          'height'=> 150,
          'theme' => 'advanced',
          'config' => BaseForm::$TinyMCEConfig
        ),
        array(
          'class' => 'tiny_mce_c1'
        )
      ),
      'post_text' => new sfWidgetFormTextareaTinyMCE(
        array(
          'width' => 600,
          'height'=> 350,
          'theme' => 'advanced',
          'config' => 'extended_valid_elements : "iframe[src|width|height|name|align]",' . BaseForm::$TinyMCEConfig
        ),
        array(
          'class' => 'tiny_mce_c1'
        )
      ),
      'source'       => new sfWidgetFormInputText(),
      'source_url'   => new sfWidgetFormInputText(),
      'author_id' => new sfWidgetFormDoctrineChoice(array(
        'model' => 'PostAuthor',
        'table_method' => 'getExperts',
        'add_empty' => true
      )),
      'created_at' => new sfWidgetFormDateTime(),
      'status' => new sfWidgetFormChoice(array(
        'choices' => array(
          'publish' => 'Опубликована',
          'not_publish' => 'Неопубликована'
        )
      )),
      'themes_list' => new sfWidgetFormDoctrineChoice(array(
        'model' => 'Theme',
        'expanded' => true,
        'add_empty' => 'Выбрать все темы',
        'multiple' => true
      )),      
      'on_main' => new sfWidgetFormInputCheckbox(),
      'in_yandex_rss'      => new sfWidgetFormInputCheckbox(),
      'in_google_xml'      => new sfWidgetFormInputCheckbox(),
      'in_rambler_rss'     => new sfWidgetFormInputCheckbox(),
      'title_h1'           => new sfWidgetFormInputText(),
      'title_seo'          => new sfWidgetFormInputText(),
      'description'        => new sfWidgetFormInputText(),
      'keywords'           => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'source'                 => new sfValidatorString(array(
          'max_length' => 200,
          'required' => false
        ), array(
          'max_length' => 'Слишком длинное значение (макс. 400 символов)',
        )
      ),
      'source_url'             =>  new sfValidatorUrl(array(
          'max_length' => 200,
          'required' => false
        ), array(
          'invalid'     => 'Адрес источника должен быть url в формате http://www.example.ru',
          'max_length'  => 'Слишком длинное значение (макс. 200 символов)'
        )
      ),
      'created_at'             => new sfValidatorDateTime(),
      'title'                  => new sfValidatorString(array('max_length' => 255)),
      'slug'                  => new sfValidatorRegex(array(
        'pattern' => '#^\w+[\w\-\.]*$#'
        ,'required' => false
      )),
      'post_text'              => new sfValidatorString(),
      'status'                 => new sfValidatorChoice(array('choices' => array('restricted' => 'restricted', 'publish' => 'publish', 'not_publish' => 'not_publish', 'inactive' => 'inactive', 'moderate' => 'moderate'))),
      'author_id'              => new sfValidatorDoctrineChoice(array('model' => 'PostAuthor', 'required' => true)),
      'lid'                    => new sfValidatorString(array('max_length' => 400, 'required' => false)),
      'themes_list'            => new sfValidatorDoctrineChoice(array('model' => 'Theme', 'required' => false, 'multiple' => true)),
      'on_main'                => new sfValidatorBoolean(),
      'in_yandex_rss'          => new sfValidatorBoolean(),
      'in_google_xml'          => new sfValidatorBoolean(),
      'in_rambler_rss'         => new sfValidatorBoolean(),
      'region_list'      => new sfValidatorDoctrineChoice(array(
        'model' => 'Region',
        'required' => false, 
        'multiple' => true
      )),
      'title_h1'         => new sfValidatorString(array(
        'max_length' => 255, 'required' => false
      )),
      'title_seo'         => new sfValidatorString(array(
        'max_length' => 255, 'required' => false
      )),
      'description'      => new sfValidatorString(array(
        'max_length' => 255, 'required' => false
      )),
      'keywords'         => new sfValidatorString(array(
        'max_length' => 255, 'required' => false
      )),
        
    ));

    $this->addMainRegionWidget();

    $this->widgetSchema->setLabels(array(
      'created_at'  => 'Дата создания',
      'main_region_id' => 'Главный регион',
      'region_list' => 'Регионы',
      'title'       => 'Заголовок',
      'slug'        => 'URL',
      'lid'         => 'Анонс',
      'post_text'   => 'Текст',
      'author_id'     => 'Автор',
      'on_main'     => 'На главной',
      'in_yandex_rss'      => 'Добавить в Яндекс.Новости',
      'in_google_xml'      => 'Добавить в Google.Новости',
      'in_rambler_rss'     => 'Добавить в Рамблер.Новости',
      'status'      => 'Статус',
      'themes_list' => 'Темы',
      'source'      => 'Источник',
      'source_url'  => 'Адрес источника',
      'title_h1'    =>  'Заголовок h1'
    ));
    $this->widgetSchema->setNameFormat('expert_article[%s]');
    if (!sfContext::getInstance()->getUser()->hasCredential('expert-blog-actions')){
      unset($this->widgetSchema['created_at']);
    }
    else {
      $this->setDefault('created_at', date_parse(date('Y/m/d H:i')));
      $this->setDefault('status', 'publish');
    }
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

  protected function doSave($con = null) {
    $values = $this->getValues();
    if ($this->getObject()->isNew()){            
      $values['created_at'] = isset($values['created_at']) ? $values['created_at'] : parse_date(date('Y/m/d H:i'));
    }
    $values['post_type']  = 'expert_article';

    $this->updateObject($values);
    parent::doSave($con);
  }
}