<?php
/**
 * BackendUserForm
 *
 * @package    domus
 * @subpackage forms
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class BackendUserForm extends UserForm {

  public function configure() {
    parent::configure();

    $this->embedForm('Image', new UserImageForm());

    $user_info = Doctrine::getTable('UserInfo')->find($this->object->id);
    $user_info_form = new UserInfoForm($user_info);
    $user_info_form->widgetSchema['country'] = new sfWidgetFormInputText(array('label'=> 'Код страны', 'default' => '+7'), array('class' => 'country-code'));
    $user_info_form->widgetSchema['area'] =   new sfWidgetFormInputText(array('label'=> 'Код', 'default' => 'код'), array('class' => 'area-code'));
    $user_info_form->widgetSchema['number'] = new sfWidgetFormInputText(array('label'=> 'Номер', 'default' => 'номер'), array('class' => 'phone'));
    $this->embedForm('Info', $user_info_form);

    $this->validatorSchema['email'] = new sfValidatorEmail(array('required' => true));
    $this->validatorSchema->setPostValidator(
      new sfValidatorAnd(array(
        new sfValidatorCallback(array('callback' => array($this, 'employerValidate'))),
        new sfValidatorDoctrineUnique(array('model' => 'User', 'column' => array('email'))),
        new sfValidatorDoctrineUnique(array('model' => 'User', 'column' => array('phone'))),
      ))
    );

    $this->widgetSchema['Image']->setLabel('Изображение');
    $this->widgetSchema['Info']->setLabel('Дополнительная информация');

    unset($this['active_count'], $this['deleted_count'], $this['rating'],
          $this['deleted_at'], $this['inactive'], $this['photo']);
  }

  public function employerValidate($validator, $values) {
    if (!empty($values['type']) && $values['type'] == 'employee'){
      if (empty($values['employer_id'])) {
        throw new sfValidatorError($validator, 'Необходимо заполнить поле Компания(id)');
      }
    }
    return $values;
  }

  protected function doSave($con = null) {
    $user = $this->getObject();
    $user_array = $this->getValues();
    $user_info = $user_array['Info'];
    $user_photo = $user_array['Image']['photo'] == '' ? 0 : $user_array['Image'];

    unset($user_array['Info'], $user_array['Image']);

    foreach ($user_array as $key => $value){
      $user->$key = $value;
    }
    $user->save();

    if ($user_info['country'] != '' && $user_info['area'] != '' && $user_info['number'] != 0){
      $user_info['additional_phone'] = Toolkit::formatPhoneNumber(
        $user_info['country'],
        $user_info['area'],
        $user_info['number']
      );
    }
    else {
      $user_info['additional_phone'] = null;
    }
    unset($user_info['country'], $user_info['area'], $user_info['number']);
    foreach ($user_info as $key => $value){
      $user->Info->$key = $value;
    }
    $user->Info->user_id = $user->id;
    $user->Info->save();
    if($user_photo) {
      $this->savePhoto($user_photo);
    }
  }

  protected function savePhoto($photo = array())
  {
    if (count($photo) == 0)
    {
      return false;
    }
    $forms = $this->getEmbeddedForms();
    $forms['Image']->_user = $user = $this->getObject();
    $forms['Image']->bind($photo);
    if ($forms['Image']->isValid()) {
      $filename = $forms['Image']->save();
    } else {
      throw new sfValidatorError($forms['Image']->getValidator('photo'), 'Photo is not valid');
    }
    $user->save();
  }
}
?>
