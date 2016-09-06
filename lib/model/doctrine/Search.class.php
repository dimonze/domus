<?php

class Search extends BaseSearch
{

  public function getText () {
    $text = '';

    $params = $this->param;

    if (!empty($params['field'])) {
      foreach ($params['field'] as $key => $value) {
        $params['field'.$key] = $value;
      }
      unset($params['field']);
    }

    if (isset($params['location-type']) && $params['location-type'] == 'map') {
      unset($params['region_id'], $params['regionnode']);
      $text .= "<li>Тип поиска &mdash; координаты (по карте)</li>\n";
    }

    foreach ($params as $name => $value)  {
      $help = null;

      switch ($name) {
        case 'type':
          $name = 'Тип';
          $value = Lot::$type_ru[$value];
          break;

        case 'price':
          $name = 'Цена';
          foreach ($value as &$v) {
            $v = Currency::formatPrice($v, $params['currency']);
          }
          break;

        case 'region_id':
          $name = 'Регион';
          $value = (string) Doctrine::getTable('Region')->find($value);
          break;

        case 'regionnode':
          $name = 'Метро/район/нас. пункт';
          break;

        default:
          if (substr($name, 0, 5) == 'field') {
            $field = Doctrine::getTable('FormField')->find(substr($name, 5));
            $name = (string) $field;
            $help = $field->help;
          }
          else {
            unset($name);
          }
      }

      if (!empty($name)) {
        if(is_array($value)) {
          if (isset($value['from']) || isset($value['to'])) {
            $value = (isset($value['from']) ? ' от ' . $value['from'] : '') .
                     (isset($value['to']) ? ' до ' . $value['to'] : '');
          }
          elseif (isset($value['or'])) {
            $value = implode(', ', $value['or']);
          }
          elseif (isset($value['orlike'])) {
            $value = implode(', ', $value['orlike']);
          }
          elseif (isset($value['and'])) {
            $value = implode(', ', $value['and']);
          }
          else {
            $value = implode(', ', $value);
          }

          $value = str_replace('5+-?и', '5+ ', $value);
        }

        if ($help) {
          $value .= " ($help)";
        }


        $text .= "<li>$name &mdash; $value</li>\n";
      }
    }

    return $text ? "<ul>$text</ul>" : null;
  }

  public function getParam () {
    return $this->_get('param') ? unserialize($this->_get('param')) : array();
  }



}