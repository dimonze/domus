<?php

/**
 * UserImageForm
 *
 * @package    domus
 * @subpackage forms
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class UserImageForm extends UserForm
{
  public
    $_user = null;

  private
    $type;

    public function __construct($type = null) {
      $this->type = $type;
      parent::__construct();
    }

    public function configure() {
    $this->setWidgets(array(
      'photo'            => new sfWidgetFormImageCrop(array(
        'url'  => '/form/crop' . ($this->type ? '/type/' . $this->type : ''),
        'type' => 'user'
      ))
    ));

    $this->setValidators(array('photo' => new sfValidatorString(array('required' => false))));
    $this->widgetSchema['photo']->setLabel('Файл');
    $this->widgetSchema->setNameFormat('user[%s]');
  }


  public function save($con = null) {
    $values = $this->getValues();
    if (null == $this->_user) {
      $this->_user = sfContext::getInstance()->getUser();
    }
    $user = sfContext::getInstance()->getUser();
    if (!empty($values['photo'])) {
      $image = sprintf('%s/%s/source/%s', sfConfig::get('sf_web_dir'), sfConfig::get('app_upload_tmp_dir'), $values['photo']);
      $filename = $this->_user->id . '.' . pathinfo($image, PATHINFO_EXTENSION);
      if (rename($image, $this->_user->full_photo_path . '/' . $filename)) {
        chmod($this->_user->full_photo_path .'/' . $filename, 0666);
        $this->_user->photo = $filename;
        if ($this->_user->id == $user->id) {
          $user->getObject()->save();
          return true;
        }
        else {
          return $filename;
        }
      }
    }

    return false;
  }
}