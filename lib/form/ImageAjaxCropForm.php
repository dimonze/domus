<?php

/**
 * ImageAjaxCrop form.
 *
 * @package    form
 */
class ImageAjaxCropForm extends BaseForm
{
  public function configure() {
    $path = sfConfig::get('app_upload_tmp_dir', 'tmp');
    $this->setWidget('image', new sfWidgetFormInputFile(array('label' => 'Изображение')));    
    $this->setValidator('image', new sfValidatorFile(array(
      'path' => sprintf('%s/%s/source', sfConfig::get('sf_web_dir'), $path),
      'max_size' => 10000
    ),
    array(
      'max_size' => 'Размер файла не может превышать 10Mb.'
    )));

  }
}
