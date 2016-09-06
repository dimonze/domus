<?php

/**
 * BlogAuthor form.
 *
 * @package    form
 * @subpackage BlogAuthor
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class BlogAuthorForm extends BaseBlogAuthorForm
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

    $this->setWidget('photo', new sfWidgetFormInputFile(array(), array('size' => 45)));

    $this->setValidator('photo',new sfValidatorFile(array('mime_types' => 'web_images', 'required' => false)));
    $this->setValidator('name', new sfValidatorString(
      array('required' => true),
      array('required' => 'ФИО обязательно для заполнения')
    ));
    $this->widgetSchema->setLabels(array(
      'user_id' => 'Пользователь',
      'name'    => 'ФИО',
      'company' => 'Компания',
      'post'    => 'Должность',
      'photo'   => 'Фото',
      'description' => 'Описание',
      'author_type' => 'Тип автора'
    ));
    $this->getWidgetSchema()->setPositions(array(
      'name', 'company', 'post', 'photo', 'description', 'author_type',
      'user_id', 'deleted_at', 'id'
    ));
    unset($this['deleted_at']);
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
    if($values['photo']){
      $photo = $values['photo'];
      $values['photo'] = null;
    }    
    $object = $this->updateObject($values);
    $this->object->save();
    $this->object->photo = $this->savePhoto($photo, $con);
    $this->object->save();
  }
  
  public function savePhoto($photo, $con = null)
  {            
    $filename = sprintf('%s/author/%d/%d.jpg',
      sfConfig::get('sf_upload_dir'),
      floor($this->object->id / 20),
      $this->object->id
    );

    if ($photo->save($filename)) {
      sfThumbnail::create(50, 50, true, true, 90)->loadFile($filename)->save($filename, 'jpeg');
      return basename($filename);
    }
  }
}