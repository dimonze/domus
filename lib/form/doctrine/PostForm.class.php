<?php

/**
 * Post form.
 *
 * @package    form
 * @subpackage Post
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class PostForm extends BasePostForm
{
  public function configure()
  {
    parent::configure();    
    $this->setWidget('tags', new sfWidgetFormInputText());

    $this->setWidget('title_photo', new sfWidgetFormImageCrop(array(
        'url' => '/form/crop',
    )));

    $this->setWidget('status', new sfWidgetFormChoice(array(
      'choices' => Post::$status
    )));
    
    $this->setWidget('themes_list', new sfWidgetFormDoctrineChoice(array(     
      'model' => 'Theme',
      'expanded' => true,
      'add_empty' => 'Выбрать все темы',
      'multiple' => true
    )));
    
    $this->setWidget('region_list', new sfWidgetFormDoctrineChoice(array(
      'model' => 'Region',
      'expanded' => true,
      'add_empty' => 'Выбрать все регионы',
      'multiple' => true
    )));

    $this->addMainRegionWidget();
    
    $this->setWidget('slug', new sfWidgetFormInputText());
    
    $this->widgetSchema->setLabels(array(
        'title'                  => 'Заголовок',
        'created_at'             => 'Дата создания',
        'post_text'              => 'Текст новости',
        'source'                 => 'Источник',
        'source_url'             => 'Адрес источника',
        'lid'                    => 'Лид',
        'subtitle'               => 'Подзаголовок',
        'signature'              => 'Подпись',
        'tags_list'              => 'Тэги',
        'themes_list'            => 'Темы',
        'rating'                 => 'Рейтинг',
        'less_count'             => 'Число просмотров',
        'tags'                   => 'Тэги',
        'title_photo'            => 'Изображение',
        'section'                => 'Раздел',
        'status'                 => 'Статус',
        'region_list'            => 'Регионы',
        'main_region_id'         => 'Главный регион',
        'is_primary'             => 'Главная новость',
        'on_main'                => 'На главной',
        'title_photo_source'     => 'Источник изображения',
        'title_photo_source_url' => 'Адрес источника изображения',
        'in_yandex_rss'          => 'Добавить в Яндекс.Новости',
        'in_rambler_rss'         => 'Добавить в Рамблер.Новости',
        'in_google_xml'          => 'Добавить в Google.Новости',
        'event_date'             => 'Дата события',
        'event_place'            => 'Место проведения',
        'event_contact'          => 'Контактная информация',
        'title_h1'               => 'Заголовок h1',
        'description'            => 'Description',
        'keywords'               => 'Keywords',
        'slug'                   => 'URL'
    ));
    unset(
      $this['author_email'],
      $this['event_date'],
      $this['event_place'],
      $this['event_contact'],
      $this['updated_at']
    );
    $this->widgetSchema->setHelp('tags', 'Разделены запятыми');
    $this->widgetSchema->setHelp('is_primary', 'Сделать новость главной');
    $this->widgetSchema->setHelp('on_main', 'Поместить новость на главную страницу');
    $this->widgetSchema->setHelp('title_photo_source_url', 'Url источника изображения в формате http://www.example.ru');
    $this->widgetSchema->setHelp('source_url', 'Url источника новости в формате http://www.example.ru');
    $this->widgetSchema->setHelp('slug', 'Адрес страницы будет выглядеть как <b>slug-id.html</b>, например, http://www.example.ru/test-1.html');
    $this->validatorSchema['title_photo'] = new sfValidatorPass(array('required' => false));
    $this->setValidator('slug', new sfValidatorRegex(array(
        'pattern' => '#^\w+[\w\-\.]*$#'
        ,'required' => false
    )));
    AjaxForm::setErrorMessages($this);
  }

  public function getErrorsArray()
  {
    return AjaxForm::getErrorsArray($this);
  }

  public function save($con = null)
  {    
    $this->object->updated_at = date('Y-m-d H:i:s');    
    parent::save();
    //save title photo
    if ($this->getValue('title_photo') != ''){      
      $this->savePhotos($con);
    }    
    //save tags
    if (count($this->getValue('tags'))){
      $this->object->tags_from_string = $this->getValue('tags');
      $this->object->save();
    }
    
    if ($this->getValue('is_primary') == true && 'news' != $this->object->post_type){
      Doctrine::getTable('Post')->createQuery()
        ->update()->set('is_primary', 0)
        ->where('post_type = ?', $this->object->post_type)
        ->andWhere('id <> ?', $this->object->id)
        ->execute();
    }

    return $this->object;
  }

  /**
   * Save title photo for post
   * @param <type> $con
   * @param Post $post
   */
  protected function savePhotos($con = null)
  {    
    $values = $this->getValues();    
    if (count($values['title_photo'])) {      
      $image = sprintf('%s/%s/source/%s', sfConfig::get('sf_web_dir'), sfConfig::get('app_upload_tmp_dir'), $values['title_photo']);
      $filename = '1.' . pathinfo($image, PATHINFO_EXTENSION);
      if (rename($image, $this->object->full_photo_path . '/' . $filename)) {
        chmod($this->object->full_photo_path .'/' . $filename, 0666);        
      }
      if (isset($filename)) {
        $this->object->title_photo = $filename;
      }      
    }
    else {
      $this->object->title_photo = null;
    }    
    $this->object->save($con);    
  }

  public function updateDefaultsFromObject() {    
    if (null != $this->object->title_photo){
      $filename = $this->object->id . '_post_' . $this->object->title_photo;
      $destination = sprintf('%s/%s/source/%s', sfConfig::get('sf_web_dir'), sfConfig::get('app_upload_tmp_dir'), $filename);
      if (copy($this->object->full_photo_path . '/' . $this->object->title_photo, $destination)) {
        $this->object->title_photo = $filename;
      }
      
    }
    parent::updateDefaultsFromObject();
  }

  protected function addMainRegionWidget() {
    $this->setWidget('main_region_id', new sfWidgetFormDoctrineChoice(array(
        'model' => 'Region',
    )));
    $this->setValidator('main_region_id', new sfValidatorNumber());
  }
}