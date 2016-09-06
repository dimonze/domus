<?php

/**
 * BlogUser form.
 *
 * @package    form
 * @subpackage BlogUser
 */
class BlogUserForm extends BlogForm
{
  public function configure()
  {
    parent::configure();
    unset($this['status']);
    $this->widgetSchema->setNameFormat('blog[%s]');
    AjaxForm::setErrorMessages($this);
  }

  public function getErrorsArray()
  {
    return AjaxForm::getErrorsArray($this);
  }

  protected function doSave($con = null)
  {
    $values = $this->getValues();
    $values['status']  = 'moderate';    
    $this->updateObject($values);
    parent::doSave($con);
  }
}