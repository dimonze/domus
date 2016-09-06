<?php

/**
 * Blog form.
 *
 * @package    form
 * @subpackage Blog
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class BlogForm extends BaseBlogForm
{
  public function configure()
  {
    $this->setWidgets(array(
      'id'        => new sfWidgetFormInputHidden(),
      'user_id'   => new sfWidgetFormInputHidden(),
      'title'     => new sfWidgetFormInputText(),
      'url'       => new sfWidgetFormInputText(),
      'status'    => new sfWidgetFormChoice(array('choices' => Blog::$_status)),
    ));
    $this->setValidators(array(
      'id'        => new sfValidatorDoctrineChoice(array('model' => 'Blog', 'column' => 'id', 'required' => false)),
      'title'     => new sfValidatorAnd(array(
        new sfValidatorString(
          array('max_length' => '100', 'required' => true, 'trim' => true),
          array('required' => 'Обязательно для заполнения')
        )
      )),
      'user_id'   => new sfValidatorAnd(
        array(
          new sfValidatorDoctrineUnique(
            array('model' => 'Blog', 'column' => 'user_id'),
            array('invalid' => 'У пользователя уже есть блог')
          )
        )
      ),
      'url'       => new sfValidatorAnd(
        array(
          new sfValidatorString(
            array('max_length' => 30, 'trim' => true, 'required' => true),
            array('max_length' => 'Url может содержать только 30 символов')
          ),
          new sfValidatorRegex(
            array('pattern' => '/^[a-z]{3}[a-z\_0-9]*/'),
            array('invalid' => 'Url может содержать только буквы латинского алфавита, цифры и знак "_". Url должен начинаться с букв, минимум 3 буквы')
          )
        )
      ),
      'status'  => new sfValidatorChoiceKeys(array('choices' => Blog::$_status)),
    ));
    $this->setDefault('status', 'moderate');
    $this->widgetSchema->setLabels(array(
      'title'   => 'Название блога',
      'user_id' => 'Пользователь',
      'url'     => 'Url блога',
      'status'  => 'Статус'
    ));
    $this->widgetSchema->setNameFormat('blog[%s]');

    $this->validatorSchema->setPreValidator(
      new sfValidatorCallback(
        array('callback' => array($this, 'validateBlog'), 'required' => true)
      )
    );
//    unset($this['user_id']);
  }

  public function validateBlog($validator, $values) {
    if (!$this->isNew()){      
      if (!empty($values['user_id']) && !empty($values['title']) && isset($values['url'])) {
        $blog = Doctrine_Query::create()
          ->select('id, user_id, title, url')
          ->from('Blog')
          ->where('id = ?', $values['id'])
          ->fetchOne();        
        if ($blog) {
          if ($blog->title == $values['title']) {
            $this->validatorSchema['title'] = new sfValidatorString(
              array('max_length' => '100', 'required' => true, 'trim' => true),
              array('required' => 'Обязательно для заполнения')
            );
          }
          if ($blog->user_id == $values['user_id']) {
            $this->validatorSchema['user_id'] = new sfValidatorString(
              array('max_length' => 30, 'trim' => true, 'required' => true),
              array('max_length' => 'Url может содержать только 30 символов')
            );
          }
          if ($blog->url == $values['url']) {
            $this->validatorSchema['url'] = new sfValidatorString(
              array('max_length' => 30, 'trim' => true, 'required' => true),
              array('max_length' => 'Url может содержать только 30 символов')
            );
          }
        }
      }
    }
  }
}