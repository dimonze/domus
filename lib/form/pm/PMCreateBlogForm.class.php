<?php

/**
 * PMCreateBlog form.
 *
 * @package    form
 */
class PMCreateBlogForm extends PMForm implements AjaxFormInterface
{
  public function configure()
  {
   $this->setWidgets(array(
      'receiver'   => new sfWidgetFormInputText(),
      'blog_name'  => new sfWidgetFormInputText(),
      'blog_url'   => new sfWidgetFormInputText(),
      'subject'    => new sfWidgetFormInputText(),
      'message'    => new sfWidgetFormInputText(),
      'priority'   => $this->_prioritySelect,
    ));

    $this->setValidators(array(
      'receiver'  => new sfValidatorAnd(array(
          new sfValidatorEmail(),
          new sfValidatorDoctrineChoice(array('model' => 'User', 'column' => 'email')),
        )),
      'blog_name'  => new sfValidatorAnd(array(
          new sfValidatorString(array('required' => true, 'max_length' => 100)),
          new sfValidatorDoctrineUnique(array(
            'model' => 'Blog',
            'column' => 'title'
          ), array(
            'invalid' => 'Блог с таким названием уже существует.'
          )
        ),
      )),
      'blog_url'  => new sfValidatorAnd(array(
          new sfValidatorString(array('required' => true, 'max_length' => 50)),
          new sfValidatorDoctrineUnique(array(
            'model' => 'Blog',
            'column' => 'url'
          ), array(
            'invalid' => 'Блог с таким Url уже существует.'
          )
        ),
      )),
      'subject' => new sfValidatorPass(),
      'message' => new sfValidatorPass(),
      'priority'  => new sfValidatorStringPMNoLogin(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('pm[%s]');
    AjaxForm::setErrorMessages($this);
  }
}