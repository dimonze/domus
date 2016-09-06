<?php
/**
 * ImportForm
 *
 * @package    domus
 * @subpackage forms
 * @author     Garin Studio
 */
class ImportForm extends BaseForm {

  protected static $mime_types = array(
    'application/xml'             =>  ImportFile::FILE_TYPE_XML,
    'text/xml'                    =>  ImportFile::FILE_TYPE_XML
  ),
  $_path  = null;

  public function configure()
  {
    self::$_path = sfConfig::get('app_upload_tmp_dir', 'tmp');
    $this->widgetSchema['file'] = new sfWidgetFormInputFile(
      array(),
      array('class' => 'input_05', 'size' => 45)
    );
    $this->widgetSchema['format'] = new sfWidgetFormChoice(
      array('choices' => UserSourcesLink::$types)
    );
    $this->widgetSchema['url']  = new sfWidgetFormInputText(
      array(),
      array('value' => '')
    );
    $choices = ImportFile::$types;
    $paid_types = sfConfig::get('my_paid_types');
    foreach($choices as $type=>$choice) {
      if(!empty($paid_types[$type])) {
        $choices[$type] = $choice . "&nbsp;<span class='paid-type'>({$paid_types[$type]['from']} - {$paid_types[$type]['to']})</span>";
      }
      elseif($type % 2) {
        $choices[$type] = $choice . " <span class='not-paid-type'>не оплачена</span>";
      }
    }
    $this->widgetSchema['type'] = new sfWidgetFormChoice(
      array('choices' => $choices)
    );
    $this->widgetSchema['frequency'] = new sfWidgetFormChoice(
      array('choices' => UserSourcesLink::$frequencies)
    );

    $this->validatorSchema['file']  = new sfValidatorFile(array(
        'required'    =>  false,
        'max_size'    =>  2000000,
        'mime_types'  => array_keys(self::$mime_types),
        'path'        => sprintf('%s/%s/import', sfConfig::get('sf_web_dir'), self::$_path)
      ), array(
        'required'	 	=> 'Укажите файл',
        'max_size'		=> 'Превышен допустимый размер файла' ,
        'mime_types' 	=> 'Не верный тип файла'
      )
    );

    $this->validatorSchema['format'] = new sfValidatorChoice(
      array(
        'required'  =>  true,
        'choices'   =>  array_keys(UserSourcesLink::$types)
      ),
      array(
        'required'  =>  'Вы не указали формат файла для импорта.',
        'invalid'   =>  'Вы указали неправильный формат файла.'
      )
    );

    $this->validatorSchema['type'] = new sfValidatorChoice(
      array(
        'required'  =>  true,
        'choices'   =>  array_keys(ImportFile::$types)
      ),
      array(
        'required'  =>  'Вы не указали тип файла для импорта.',
        'invalid'   =>  'Вы указали неправильный тип файла.'
      )
    );

    $this->validatorSchema['frequency'] = new sfValidatorChoice(
      array(
        'required'  =>  true,
        'choices'   =>  array_keys(UserSourcesLink::$frequencies)
      ),
      array(
        'required'  =>  'Вы не указали тип файла для импорта.',
        'invalid'   =>  'Вы указали неправильный тип файла.'
      )
    );

    $this->validatorSchema['url'] = new sfValidatorUrl(
      array(
        'required'  =>  false
      ),
      array(
        'invalid' =>  'Неправильный адрес.'
      )
    );

    $this->validatorSchema->setPostValidator(
      new sfValidatorCallback(array(
          'callback'  =>  array($this, 'checkFileOrUrl')
        )
      )
    );

    $this->widgetSchema['file']->setLabel('Выберите файл');
    $this->widgetSchema['format']->setLabel('Выберите формат файла');
    $this->widgetSchema['type']->setLabel('Выберите тип файла');
    $this->widgetSchema['frequency']->setLabel('Выберите частоту загрузки файла');

    $this->widgetSchema->setNameFormat('import[%s]');
  }

  public function save()
  {
    $file   = $this->saveFile();
    $url    = $this->saveUrl();
    $format = $this->getValue('format');
    $type   = $this->getValue('type');

    if (!empty($file['path'])) {
      $file_path  = $file['path'];
      $file_name  = $file['name'];
    }

    if ($url) {
      $file_path  = $url;
      $file_name  = $url;
    }

    if($file !== false){
      //add background task
      sfGearmanProxy::doBackground('import_file', array(
        'path'      =>  $file_path,
        'file_type' =>  $type,
        'type'      =>  $format,
        'user_id'   =>  sfContext::getInstance()->getUser()->id,
        'file_name' =>  $file_name
      ));
    }

    return true;
  }

  public function checkFileOrUrl($validator, $values)
  {
    if (!empty($values['url']) && $values['url'] != 'http://') {
      unset($values['file']);
      return $values;
    }
    if (!empty($values['file'])) {
      unset($values['url']);
      return $values;
    }
    throw new sfValidatorError($validator, 'invalid');
  }

  public function saveFile()
  {
    $file = $this->getValue('file');
    if ($file) {
      $file_name = $file->save();

      if (null != $file_name) {
        $file_path = sprintf('%s/%s/import/%s', sfConfig::get('sf_web_dir'), self::$_path, $file_name);
        return array(
          'path' => $file_path,
          'name' => $file->getOriginalName()
        );
      }
    }
    return false;
  }

  public function saveUrl()
  {
    $not_add_url = false;
    $url       = $this->getValue('url');
    $file_type = $this->getValue('type');
    $type      = $this->getValue('format');
    $frequency = $this->getValue('frequency');

    if ($url) {
      $user = sfContext::getInstance()->getUser();
      foreach ($user->SourcesLinks as $link) {
        if ($link->url == $url) {
          $not_add_url = true;
          break;
        }
      }

      if (!$not_add_url) {
        $form = new UserSourcesLinkForm();
        $form->bind(
          array(
            'url'       =>  $url,
            'file_type' =>  $file_type,
            'type'      =>  $type,
            'user_id'   =>  $user->id,
            'frequency' =>  $frequency,
            'status'    =>  UserSourcesLink::STATUS_ACTIVE,
          )
        );
        if ($form->isValid()) {
          $form->save();
          return $url;
        }
        }
      else {
        return $url;
      }
    }
    return false;
  }
}