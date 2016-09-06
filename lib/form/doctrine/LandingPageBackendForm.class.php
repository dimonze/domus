<?php

/**
 * LandingPage form.
 *
 * @package    domus
 * @subpackage form
 * @author     Garin Studio
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class LandingPageBackendForm extends LandingPageForm
{
  public function configure()
  {
    parent::configure();
    
    $this->getWidgetSchema()->setAttribute('class', 'landing_box');
    
    $this->getWidget('region_id')->setAttribute('id', 'landing_select_region');
    
    $this->setWidget('seo_text', new sfWidgetFormTextareaTinyMCE(array(
        'width' => 600,
        'height'=> 350,
        'theme' => 'advanced',
        'config' => 'extended_valid_elements : "iframe[src|width|height|name|align]", ' . BaseForm::$TinyMCEConfig
      ),
      array(
        'class' => 'tiny_mce_c1'
      )));
    
    //Params
    $params = $this->getObject()->getParams();
    if(!isset($params['field'])) {
      $params['field'] = array();
    }
    //Особая площадь для новостроек
    if(!empty($params['field'][73]['to'])) {
      $params['field'][72]['to'] = $params['field'][73]['to'];
      unset($params['field'][73]);
    }
    
    //Площадь участка для коттеджных
    if(!empty($params['field'][95]['to'])) {
      $params['field'][94]['to'] = $params['field'][95]['to'];
      unset($params['field'][95]);
    }
    
    foreach ($params['field'] as $id => $data) {
      switch($id){
        case 20:
          foreach ($data as $k => $v) {
            $data[$k] = mb_strtolower($v);
          };
          break;
          
        case 45:
          $data = empty($data['orlike']) ? $data : $data['orlike'];
          break;

        case 54:
        case 55:
          $data = empty($data['or']) ? $data : $data['or'];
          foreach ($data as $k => $v) {
            if($v != 'комната' && $v != 'квартира со свободной планировкой') {
              $data[$k] = (preg_replace('#[^\d]+#', '', $v) - 1);
            }
          };
          break;
            
        case 76:
        case 107:
          $data = empty($data['or']) ? $data : $data['or'];
          foreach ($data as $k => $v) {
            $data[$k] = $v;
          }
          break;
      }

      $params["field_$id"] = $data;
    }

    $this->getObject()->setParams($params);
    $otype = $this->getObject()->getType();
    $filter_type = empty($_REQUEST['landing_page']['type']) ? (empty($otype) ? 'new_building-sale' : $otype) : $_REQUEST['landing_page']['type'];
    
    if($this->isNew()) {
      //Для AJAX-запроса полей при смене типа
      if(!empty($_REQUEST['ajax']) && !empty($_REQUEST['type'])) {
        $filter_type = $_REQUEST['type'];
      }
    }
    $this->getObject()->setType(trim($filter_type));
    //Определение нужного класса фильтра
    $filter_type = self::mb_ucfirst(str_replace(array('-', '_'), '', $this->getObject()->getType())) . 'UsersSearchFilterForm';
    $filter_type = class_exists($filter_type) ? $filter_type : 'BaseUsersSearchFilterForm';
    $filterForm = new $filter_type();
    $this->embedForm('params', $filterForm);
    $this->getWidgetSchema()->setLabel('params', 'Параметры фильтра');
    
    $this->getWidgetSchema()->moveField('type', sfWidgetFormSchema::FIRST);
    $this->getWidgetSchema()->moveField('region_id', sfWidgetFormSchema::AFTER, 'type');
    $this->getWidgetSchema()->moveField('params', sfWidgetFormSchema::AFTER, 'region_id');
    
    $this->getValidator('region_id')->setOption('required', false);
    $this->getValidator('type')->setOption('required', false);
    $this->getValidator('params')->setOption('required', false);
    
    unset( $this['query'] );
  }
  
  protected function doUpdateObject($values)
  {
    //Флаги типов для правильной разборки
    $fields_types = $this->getEmbeddedForm('params')->fields_types;
        
    $values['params']['field'] = array();   
    foreach ($values['params'] as $k => $v) {
      if(preg_match('#^field_(\d+)#', $k, $matches)) {
        if($fields_types[$matches[1]] == 'double') {
          $v = $this->clearIntegerRange($v); //Диапазоны
        }
        
        if($matches[1] == 20 && is_array($v)) {
          foreach ($v as $sk => $val) {
            //Уникальный случай значений с первой заглавной буквой
            $v[$sk] = self::mb_ucfirst($val);
          }
        }
        
        //Недвижимость
         if($matches[1] == 45 && !empty($v)){
           $v = array( 'orlike' => $v );
         }
         
        //Тип коттеджных
        if($matches[1] == 107 && !empty($v)){
          $v = is_array($v) ? $v : array($v);
          $v = array( 'or' => $v );
        }
        
        //Рукотворный виджет "квартиры"
        if($fields_types[$matches[1]] == 'flats' && is_array($v)) {
          foreach ($v as $sk => $val) {
            if(preg_match('#^\d+$#', $val) && $matches[1] != 76){
              $val++;
              $val = $val >= 5 ? 5 : $val;
              
              if($val > 1 && $val < 5) {
                $val .= '-х';
              }
              
              if($val >= 5) {
                $val .= '+-?и';
              }
                            
              $val .= ' комнатная квартира';
            }
            
            $v['or'][] = $val;
            unset( $v[$sk] );
          }
          
          $v['or'] = array_unique($v['or']);
        }
        
        if(!empty($v)) {
          $values['params']['field'][$matches[1]] = $v;
        }
        unset($values['params'][$k]);
      }
    }
    //Regionnode
    $query = Doctrine::getTable('RegionNode')
            ->findBySql(
              'id IN ? and list = 1',
              array($values['params']['regionnode']),
              Doctrine_Core::HYDRATE_ARRAY
            );
    //Узнаем имя ноды по ID
    if($query) {
      $values['params']['regionnode'] = array();
      foreach ($query as $nid => $node) {
        $values['params']['regionnode'][] = Regionnode::formatName($node['name'], $node['socr']);
      }
      //Доп. сортировка нодов
      sort($values['params']['regionnode']);
    }
    //Убираем пустые ноды
    foreach ($values['params']['regionnode'] as $k => $value) {
      if(empty($value)) unset($values['params']['regionnode'][$k]);
    }
    //Price
    if(!empty($values['params']['price'])) {
      $values['params']['price'] = $this->clearIntegerRange($values['params']['price']);
    }
    //Площадь дома для коттеджных
    if(!empty($values['params']['square'])) {
      $values['params']['square'] = $this->clearIntegerRange($values['params']['square']);
    }
    //Fix поиск по улице
    if(!empty($values['params']['q_text'])){
      $values['params']['q'] = $values['params']['q_text'];
      $values['params']['q_text_enabled'] = 1;
    }
    //Чистка параметров от пустых элементов
    $values['params'] = array_filter($values['params'], create_function('$var', 'return !empty($var);'));
    //Map
    $values['params']['map-maximized'] = $values['params']['location-type'] == 'map' ? '1' : '0';
    //Если только один курс валюты
    if(count($values['params']) == 1 && isset($values['params']['currency'])) {
      unset($values['params']['currency']);
    }
    //Особая площадь для новостроек
    if(!empty($values['params']['field'][72]['to'])){
      $values['params']['field'][73]['to'] = $values['params']['field'][72]['to'];
      unset($values['params']['field'][72]['to']);
    }
    //Площадь участка для коттеджных
    if(!empty($values['params']['field'][94]['to'])){
      $values['params']['field'][95]['to'] = $values['params']['field'][94]['to'];
      unset($values['params']['field'][94]['to']);
    }
    //Основные данные
    $values['params']['region_id'] = $values['region_id'];
    $values['params']['type'] = $values['type'];
    
    //Статичные параметры
    $values['params']['restore_advanced'] = "1";
    $values['params']['sort'] = "rating-desc";
    
    if(!empty($values['params']['field'])){
      ksort($values['params']['field']);
    }
    ksort($values['params']);
        
    //Хэш
    $values['query'] = self::generateHash($values);
    parent::doUpdateObject($values);
  }
  
  /**
   * Мультибайтовое возведение первой буквы в верхний регистр
   * 
   * @param string $str Строка для обработки
   * @param string $enc Кодировка
   * @return string
   */
  public static function mb_ucfirst($str, $enc = 'utf-8') { 
    return mb_strtoupper(mb_substr($str, 0, 1, $enc), $enc).mb_substr($str, 1, mb_strlen($str, $enc), $enc); 
  }
  
  /**
   * Очистка диапазона (начало-конец) значений от "мусора"
   * 
   * @param array $v Массив значений
   * @return array|null
   */
  private function clearIntegerRange($v) {
    if(!isset($v[0])) {
      return null;
    }

    //if( empty($v[0])) $v[0] = 0;
    $v['from'] = strval($v[0]);

    if( isset($v[1]) && intval($v[1]) >= intval($v['from']) ) {
      $v['to'] = strval($v[1]);
    }

    unset( $v[0], $v[1] );

    if((count($v) == 1 && empty($v['from'])) || (empty($v['from']) && empty($v['to']))) {
      $v = null;
    }
          
    return $v;
  }
  
  /**
   * Генерация хэша запроса для заполнения из JavaScript
   * 
   * @param type $values
   * @return boolean 
   */
  public static function generateHash($values, $page = 1) {
    $hash = '#';
    $reduction_map = array(
        'region_id' => 'r'
        ,'regionnode' =>  'rn'
        ,'currency' => 'c'
        ,'location-type' => 'l'
        ,'map-maximized' => 'm'
        ,'sort' => 's'
        ,'price' => 'p'
        ,'restore_advanced' => 'ra'
        ,'square' => 'sq'
    );
    
    if(empty($values)) {
      return false;
    }
    //Выбираем только параметры
    if(isset($values['params'])) {
      $values = $values['params'];
    }
    
    if(!empty($values['q_text'])) {
       $values['q_text'] = urlencode($values['q_text']);
       $values['q'] = $values['q_text'];
       $values['q_text_enabled'] = 1;
    }
    
    $fields = !empty($values['field']) ? $values['field'] : array();
    unset( $values['field'], $values['type'] );
    $values = $fields + $values;
    ksort($values);
            
    foreach ($values as $k => $f) {
      $prefix = $k;
      if(is_integer($k)) {
        $prefix = 'f' . intval($k);
      } elseif(in_array($k, array_keys($reduction_map))) {
        $prefix = $reduction_map[$k];
      }
      
      if(!is_array($f)) {
        $f = array($f);
      }
      
      if(isset($f['from']) || isset($f['to']) || $k == 73) { //С особыми настройками новостроек
        $hash .= !empty($f['from']) ? "{$prefix}f/" . intval($f['from']) . '/' : '';
        
        if(!empty($f['to'])) {
          $hash .= "{$prefix}t/" . intval($f['to']) . '/';
        }
        
        continue;
      }
      
      $userdata = DomusSearchRoute::$translit_table;
      if(!empty($f['or']) || !empty($f['orlike'])){
        $f = array_shift($f);
      }
      
      $func_body = '$data = str_replace(array_keys($userdata), array_values($userdata), trim($data));';
      array_walk($f, create_function('&$data, $key, $userdata', $func_body), $userdata);
      $hash .= vsprintf(str_repeat("{$prefix}/%s/", count($f)), $f);
    }
    
    return $hash . 'page/'.$page;
  }
  
  public function getJavaScripts() {
    return array_merge(array('jquery', 'form', 'autocomplete', 'landing'), parent::getJavaScripts());
  }

  public function getStylesheets() {
    return array_merge(array('autocomplete' => 'screen'), parent::getStylesheets());
  }
}
