<?php

/**
 * PostComment form.
 *
 * @package    form
 * @subpackage PostComment
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class PostCommentForm extends BasePostCommentForm implements AjaxFormInterface
{
  public function configure()
  {
    $this->setWidgets(array(
      'user_id'     => new sfWidgetFormInputHidden(),
      'user_name'   => new sfWidgetFormInputText(),
      'user_email'   => new sfWidgetFormInputText(),
      'post_id'     => new sfWidgetFormInputHidden(),
      'parent_id'   => new sfWidgetFormInputHidden(),
      'body'        => new sfWidgetFormTextarea(),
      'captcha'     => new sfWidgetFormInputKCaptcha(array(), array('class' => 'captcha'))
    ));

    $this->setValidators(array(
      'user_id'     =>  new sfValidatorDoctrineChoice(array('model' => 'User', 'required' => false)),
      'user_name'   =>  new sfValidatorString(array('max_length' => 50), array(
        'required'  =>  'Поле обязательно для заполнения.'
      )),
      'user_email'  =>  new sfValidatorEmail(array('max_length' => 60), array(
        'required'  =>  'Поле обязательно для заполнения.',
        'invalid'   =>  'Email указан неверно.'
      )),
      'post_id'     =>  new sfValidatorDoctrineChoice(array('model' => 'Post')),
      'parent_id'   =>  new sfValidatorDoctrineChoice(array('model' => 'PostComment', 'required' => false)),
      'body'        =>  new sfValidatorString(array('max_length' => 1000), array(
        'required'  =>  'Поле обязательно для заполнения.'
      )),
      'captcha'     =>  new sfValidatorKCaptcha(array(), array(
        'required'  =>  'Поле обязательно для заполнения.',
        'invalid'   =>  'Неправильно заполнено поле.'
      )),
    ));

    $this->widgetSchema->setLabels(array(
      'user_name'   =>  'Ваше имя',
      'user_email'  =>  'Email',
      'body'        =>  'Текст сообщения',
      'captcha'     =>  'Введите текст с картинки'
    ));

    if (sfContext::getInstance()->getUser()->isAuthenticated()){
      unset($this['captcha'], $this['user_name'], $this['user_email']);
    }
    else {
      unset($this['user_id']);
    }

    $this->widgetSchema->setNameFormat('comment[%s]');
    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);
  }

  public function save($con = null) {
    parent::save($con);
    if (! ($parent_node = Doctrine::getTable('PostComment')->find($this->getValue('parent_id')))) {
      $treeObject = Doctrine::getTable('PostComment')->getTree();
      $parent_node = $treeObject->fetchRoot($this->getObject()->post_id);      
    }
    $this->getObject()->getNode()->insertAsLastChildOf($parent_node);    
  }

  public function getErrorsArray()
  {
    return AjaxForm::getErrorsArray($this);
  }
}