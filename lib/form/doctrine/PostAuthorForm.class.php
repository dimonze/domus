<?php

/**
 * PostAuthor form.
 *
 * @package    form
 * @subpackage PostAuthor
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class PostAuthorForm extends BasePostAuthorForm
{
  public function configure()
  {
    $this->setWidget('description', new sfWidgetFormTextareaTinyMCE(
      array(
        'width' => 600,
        'height'=> 350,
        'theme' => 'advanced',
        'config' => BaseForm::$TinyMCEConfig
      ),
      array(
        'class' => 'tiny_mce_c1'
      )
    ));
    $this->setWidget('author_type', new sfWidgetFormChoice(array(
      'choices' => array(
        'author' => 'Автор',
        'expert' => 'Эксперт'
      ))
    ));

    $this->setWidget('photo', new sfWidgetFormImageCrop(array(
        'url'  => '/form/crop',
        'type' => 'author'
    )));

    $this->setValidator('photo',new sfValidatorString(array('required' => false)));
    $this->setValidator('name', new sfValidatorString(
      array('required' => true, 'trim' => true),
      array('required' => 'ФИО обязательно для заполнения')
    ));
    $this->setValidator('company', new sfValidatorString(
      array('required' => false, 'trim' => true)
    ));
    $this->setValidator('post', new sfValidatorString(
      array('required' => false, 'trim' => true)
    ));
    $this->widgetSchema->setLabels(array(      
      'name'    => 'ФИО',
      'company' => 'Компания',
      'post'    => 'Должность',
      'photo'   => 'Фото',
      'description' => 'Описание',
      'author_type' => 'Тип автора'
    ));
    $this->getWidgetSchema()->setPositions(array(
      'name', 'company', 'post', 'photo', 'description', 'author_type',
      'deleted_at', 'id'
    ));
    unset($this['deleted_at']);
  }

  protected function doSave($con = null) {
    $values = $this->getValues();    
    
    if($values['photo']){
      $photo = $values['photo'];
      $values['photo'] = null;
    }

    $this->updateObject($values);
    $this->object->save();

    if (isset($photo)){
      $this->savePhoto($photo, $con);
    }
    return $this->object;
  }

  /**
   * Save title photo for post
   * @param <type> $con
   * @param Post $post
   */
  protected function savePhoto($photo, $con = null)
  {    
    if (isset($photo)) {
      $image = sprintf('%s/%s/source/%s', sfConfig::get('sf_web_dir'), sfConfig::get('app_upload_tmp_dir'), $photo);
      $filename = '1.' . pathinfo($image, PATHINFO_EXTENSION);
      if (rename($image, $this->object->full_photo_path . '/' . $filename)) {
        chmod($this->object->full_photo_path .'/' . $filename, 0666);
      }
      if (isset($filename)) {
        $this->object->photo = $filename;
      }
    }
    else {
      $this->object->photo = null;
    }
    return $this->object->save($con);
  }

  public function updateDefaultsFromObject() {
    if (null != $this->object->photo){
      $filename = $this->object->id . '_author_' . $this->object->photo;
      $destination = sprintf('%s/%s/source/%s', sfConfig::get('sf_web_dir'), sfConfig::get('app_upload_tmp_dir'), $filename);
      if (copy($this->object->full_photo_path . '/' . $this->object->photo, $destination)) {
        $this->object->photo = $filename;
      }
    }
    parent::updateDefaultsFromObject();
  }
}