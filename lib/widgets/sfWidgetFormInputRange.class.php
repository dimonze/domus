<?php

class sfWidgetFormInputRange extends sfWidgetFormInputText
{
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    if ('[]' != substr($name, -2)) $name .= '[]';
    $attributes = array_merge($attributes, array('id' => ''));

    $value_fr = null;
    $value_to = null;

    if (is_array($value)) {
      $value_fr = isset($value['from']) ? $value['from'] : (!empty($value[0]) ? $value[0] : '');
      $value_to = isset($value['to']) ? $value['to'] : (!empty($value[1]) ? $value[1] : '');
    }

    $html = '';
    $html .= $this->renderTag('input', array_merge(array('type' => 'text', 'name' => $name, 'value' => $value_fr), $attributes));
    $html .= ' &mdash; ';
    $html .= $this->renderTag('input', array_merge(array('type' => 'text', 'name' => $name, 'value' => $value_to), $attributes));
    return $html;
  }
}