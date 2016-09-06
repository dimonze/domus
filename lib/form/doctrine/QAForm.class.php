<?php

/**
 * QA form.
 *
 * @package    form
 * @subpackage QA
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class QAForm extends PostForm
{

  public function configure() {
    parent::configure();

    $this->widgetSchema->setNameFormat('qa[%s]'); 

    $this->widgetSchema['author_name'] = new sfWidgetFormInputText();
    $this->validatorSchema['author_name'] = new sfValidatorString(
        array('required' => true),
        array(
          'required' => 'Укажите свое имя<li class="error_img"></li>',
          'invalid' => 'Недопустимые символы<li class="error_img"></li>'
      ));

    $this->widgetSchema['author_email'] = new sfWidgetFormInputText();
    $this->validatorSchema['author_email'] = new sfValidatorEmail(
        array('required' => true, 'trim' => true),
        array(
          'required' => 'Укажите свой e-mail<li class="error_img"></li>',
          'invalid' => 'Недопустимые символы<li class="error_img"></li>'
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
    
    $this->validatorSchema['post_text'] = new sfValidatorString(
        array('required' => true),
        array('required' => 'Обязательное поле<li class="error_img"></li>')
    );
    $this->validatorSchema['title'] = new sfValidatorString(
        array(
          'required' => true
        ),
        array(
          'required' => 'Обязательное поле<li class="error_img"></li>'
      ));

    $this->setWidget('themes_list', new sfWidgetFormDoctrineChoice(array(
      'model' => 'Theme'
    )));
    $this->setValidator('themes_list', new sfValidatorDoctrineChoice(array(
      'model' => 'Theme'
    )));

    $this->setDefault('created_at', date_parse(date('Y/m/d H:i')));

    unset(
      $this['post_type'],
      $this['blog_id'],
      $this['rating'],
      $this['less_count'],
      $this['author_id'],
      $this['tags_list'],
      $this['deleted'],
      $this['deleted_at'],
      $this['section'],
      $this['is_primary'],
      $this['lid'],
      $this['subtitle'],
      $this['source'],
      $this['source_url'],
      $this['title_photo'],
      $this['title_photo_source'],
      $this['title_photo_source_url'],
      $this['signature'],
      $this['tags'],
      $this['on_main'],
      $this['in_yandex_rss'],
      $this['in_rambler_rss'],
      $this['in_google_xml'],
      $this['region_list'],
      $this['title_h1'],
      $this['description'],
      $this['keywords'],
      $this['main_region_id'],
      $this['slug']
    );

    $user = sfContext::getInstance()->getUser();

    if (!$user->isAuthenticated()) {
      unset(
        $this['user_id'],
        $this['status'],
        $this['created_at']
      );
    }
    elseif(!$user->hasCredential('redactor-qa-actions')) {
      $this->widgetSchema['author_name'] = new sfWidgetFormInputText(array('default' => $user->name), array('disabled' => 'true', 'readonly'=>'readonly'));
      unset(
        $this['user_id'],
        $this['created_at'],
        $this['author_email'],
        $this['status']
      );
    }
    else {
      $this->widgetSchema['author_name'] = new sfWidgetFormInputText(array('default' => $user->name));
      $this->validatorSchema['author_name'] = new sfValidatorString(array('required' => false ));

      $this->widgetSchema['title_h1'] = new sfWidgetFormInputText();
      $this->validatorSchema['title_h1'] = new sfValidatorString(array(
        'max_length'  => 255,
        'required'    => false
      ));
      $this->widgetSchema['description'] = new sfWidgetFormInputText();
      $this->validatorSchema['description'] = new sfValidatorString(array(
        'max_length'  => 255,
        'required'    => false
      ));
      $this->widgetSchema['keywords'] = new sfWidgetFormInputText();
      $this->validatorSchema['keywords'] = new sfValidatorString(array(
        'max_length'  => 255,
        'required'    => false
      ));
      
      unset(
        $this['captcha'],
        $this['author_email'],
        $this['user_id']
      );

      $app_name = sfContext::getInstance()->getConfiguration()->getApplication();
      if('frontend' == $app_name) unset($this['created_at'], $this['status']);
    }


    $this->getValidatorSchema()->setOption('allow_extra_fields', true);
  }

  public function updateObject($values = null) {
    if (is_null($values)) {
      $values = $this->values;
    }
    $values = $this->processValues($values);
    $this->object->fromArray($values);
    $this->updateObjectEmbeddedForms($values);


    return $this->object;
  }

  public function save($con = null) {
    $values = $this->getValues();

    if ($this->getObject()->isNew()){
      $values['created_at'] = isset($values['created_at']) ? $values['created_at'] : date('Y-n-j G:i:s');
      $values['user_id'] = isset($values['user_id']) ? $values['user_id'] : sfContext::getInstance()->getUser()->id;
    }
    $values['status'] = 'moderate';
    $values['post_type'] = 'qa';
    
    $this->updateObject($values);
    parent::save($con);

    $theme_id = (int) $values['themes_list'];
    $theme = Doctrine::getTable('Theme')->findOneById($theme_id);
    $this->object->unlink('Themes');
    $this->object->Themes[] = $theme;
    $this->object->save();

    return $this->object;
  }

  public function validateAuthor($validator, $values) {
    if (empty($values['author_name']) && empty($values['user_id'])) {
      throw new sfValidatorErrorSchema(
        $validator,
        array(
          'author_name' => new sfValidatorError($validator, 'Не заполнено одно из полей Автор'),
          'user_id' => new sfValidatorError($validator, 'Не заполнено одно из полей Пользователь')
      ));
    }
    return $values;
  }

}