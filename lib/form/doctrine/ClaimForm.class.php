<?php

/**
 * Claim form.
 *
 * @package    form
 * @subpackage Claim
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class ClaimForm extends BaseClaimForm
{
  public function configure()
  {

    $this->widgetSchema->setNameFormat('claim[%s]');

    unset(
      $this['status'],
      $this['created_at'],
      $this['updated_at']
    );

    $user = sfContext::getInstance()->getUser();
    if(!$user->isAuthenticated()) {
      $this->widgetSchema['user_name'] = new sfWidgetFormInputText();
      $this->widgetSchema['user_email'] = new sfWidgetFormInputText();

      $this->validatorSchema['user_name'] = new sfValidatorString(array(
        'required' => true,
        'min_length' => 4
      ), array(
        'required' => 'Обязательно для заполнения',
        'min_length' => 'Странное у вас имя'
      ));
      $this->validatorSchema['user_email'] = new sfValidatorEmail(array(
        'required' => true
      ), array(
        'required' => 'Обязательно для заполнения',
        'invalid' => 'Не коректный e-mail'
      ));

      $this->widgetSchema['captcha'] = new sfWidgetFormInputKCaptcha(
        array('template' => '%img%<a href="#" class="captcha-re update_qa_captcha"></a>
                             <div class="st captcha-inp">%input%</div>',
           'src' => '/qa-kcaptcha.png'),

        array('class' => 'captcha', 'width' => 100, 'height' => 30)
      );
      $this->validatorSchema['captcha'] = new sfValidatorKCaptcha(
        array(),
        array('required'  => 'Введите символы<li class="error_img"></li>',
              'invalid'   => 'Неверные символы,<br />попробуйте обновить картинку<li class="error_img"></li>'
              )
      );
    } else {
      unset($this['user_name'], $this['user_email']);
    }

    $this->widgetSchema['lot_id'] = new sfWidgetFormInputHidden();
    $this->validatorSchema['lot_id'] = new sfValidatorInteger(array(
      'required' => true,
      'trim' => true
    ));

  }


  public function getErrorsArray()
  {
    return AjaxForm::getErrorsArray($this);
  }
}