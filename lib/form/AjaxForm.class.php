<?php

/**
 * AjaxForm
 *
 * @package    domus
 * @subpackage forms
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
abstract class AjaxForm extends BaseForm
{
  protected static $error_messages = array(
    'required' => 'Обязательное поле', // Required.
    'invalid' => 'Некорректное значение', // Invalid.
    'extra_fields' => 'Очень странно. Вы не должны видеть этой ошибки.', // Unexpected extra form field named "%field%".
    'post_max_size' => 'Скорее всего вы пытаетесь загрузить слишком большой файл.', // The form submission cannot be processed. It probably means that you have uploaded a file that is too big.
    'max_length' => 'Слишком длинное значение (макс. %max_length% символов)', // '"%value%" is too long (%max_length% characters max).'
    'min_length' => 'Слишком короткое значение (мин. %min_length% символов)', // '"%value%" is too short (%min_length% characters min).'
    'max' => 'Не более %max%', // '"%value%" is too long (%max_length% characters max).'
    'min' => 'Не менее %min%', // '"%value%" is too short (%min_length% characters min).'

    'email_already_subscribed' => 'Подписка на этот email уже оформлена',
    'password_email_match' => 'Пароль не соответствует указанному логину',
    'duplicate_phone' => 'Пользователь с таким телефоном уже зарегистрирован',
  );

  public static function setErrorMessages(sfForm $form = null) {
    $validators = $form->getValidatorSchema()->getFields();
    if ($form->getValidatorSchema()->getPostValidator()) {
      $validators[] = $form->getValidatorSchema()->getPostValidator();
    }

    foreach ($validators as $validator) {
      if ($validator instanceOf sfValidatorAnd) {
        $validators = array_merge($validators, $validator->getValidators());
      }
    }

    foreach ($validators as $validator) {
      $messages = self::$error_messages;
      if ($validator instanceOf sfValidatorDoctrineUnique) {
        $messages['invalid'] = $validator->getMessage('invalid');
      }
      $validator->setMessages($messages);

      if ($validator instanceOf sfValidatorAnd) {
        $validator->setMessage('invalid', null);
      }
    }
  }


  public static function getErrorsArray(sfForm $form = null) {
    $fielderrors = $globalerrors = array();

    foreach ($form->getErrorSchema()->getErrors() as $fieldname => $error) {
      if ($fieldname) {
        $fielderrors[$fieldname] = self::checkErrorMessage($error->getMessage());
      }
    }

    foreach ($form->getErrorSchema()->getGlobalErrors() as $error) {
      $globalerrors[] = self::checkErrorMessage($error->getMessage());
    }

    return array('errors' => array(
        'field' => $fielderrors,
        'global' => array_unique($globalerrors)
      ));
  }

  private static function checkErrorMessage($msg) {
    if (isset(self::$error_messages[$msg])) {
      return self::$error_messages[$msg];
    }
    return $msg;
  }
}