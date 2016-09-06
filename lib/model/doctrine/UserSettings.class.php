<?php

class UserSettings extends BaseUserSettings
{
  public static
    $defaults = array(
      'expire_notify' => true,
      'show_rating'   => true,
      'send_email'    => true,
      'qa_notify'     => true,
    ),
    $texts = array(
      'expire_notify' => 'Информировать об окончании срока публикации объявления',
      'show_rating'   => 'Участвовать в рейтинге',
      'send_email'    => 'Присылать уведомления о новых сообщениях на email',
      'qa_notify'     => 'Информировать о новых вопросах в&nbsp;рубрике&nbsp;Вопрос&Ответ',
    );

  public function getValue() {
    $value = $this->_get('value');
    
    if (empty($value)) {
      return false;
    }
    else {
      return $value;
    }
  }

  public function setValue($value) {
    if (empty($value)) {
      $value = null;
    }
    $this->_set('value', $value);
  }

  public function getText()
  {
    return self::$texts[$this->name];
  }

  /**
   * @return sfWidgetForm
   */
  public function getWidget()
  {
    switch ($this->name) {
      case 'expire_notify':
      case 'show_rating':
        $class = 'sfWidgetFormInputCheckbox';
        $options = array('value_attribute_value' => 1);
        break;
      case 'send_email':
        $class = 'sfWidgetFormInputCheckbox';
        $options = array('value_attribute_value' => 1);
        break;
      case 'qa_notify':
        $class = 'sfWidgetFormInputCheckbox';
        $options = array('value_attribute_value' => 1);
        break;
      default:
        throw new Exception(sprintf('Can\'t create widget for "%s" setting', $this->name));
    }

    return new $class($options);
  }

  public function renderWidget($name_format = 'settings[%s]', $attributes = array())
  {
    return $this->widget->render(sprintf($name_format, $this->name), $this->value);
  }

  public function renderLabel($name_format = 'settings[%s]', $attributes = array())
  {
    $widget = $this->getWidget();
    return $widget->renderContentTag('label', $this->text, array_merge(
      $attributes,
      array(
        'for' => $widget->generateId(sprintf($name_format, $this->name), $this->value)
      )
    ));
  }
}