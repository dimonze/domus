<?php

/**
 * LandingPage form.
 *
 * @package    domus
 * @subpackage form
 * @author     Garin Studio
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class LandingPageForm extends BaseLandingPageForm implements AjaxFormInterface
{
  public function configure()
  {    
    $this->setWidget('type',new sfWidgetFormChoice(array(
      'choices' => LandingPage::$types
      ))
    );
    
    $this->setWidget('seo_text', new sfWidgetFormTextarea(array(), array(
        'width' => 600,
        'height'=> 350
      )));
    
    $this->getValidator('region_id')->setOption('required', false);
    $this->getValidator('type')->setOption('required', false);
    $this->setValidator('url', new sfValidatorRegex(array(
        'pattern' => '#^[a-z0-9\-\.]+$#'
        ,'required' => true
    )));
    
    AjaxForm::setErrorMessages($this);
    
    $this->getValidatorSchema()->setPostValidator(new sfValidatorDoctrineUniqueInSection(
      array(
          'model' => 'LandingPage',
          'section_field' => 'type',
          'column' => 'url'
      ),
      array(
          'invalid' => 'Такое значение "%column%" уже используется. Выберите, пожалуйста, что-то другое.'
      )
    ));
  }
  
  public function getErrorsArray()
  {
    return AjaxForm::getErrorsArray($this);
  }
  
  protected function doUpdateObject($values)
  {
    $fields = array( "region_id", "type" );
    foreach ($fields as $field) {
      if(empty($values[$field])){
        $values[$field] = $values['params'][$field];
      }
    }
    
    $fields = array( "h1", "title", "description", "keywords", "seo_text", "lot_title_prefix" );
    foreach ($fields as $field) {
      if($field == "lot_title_prefix") {
        //Особые поля для префикса
        $values[$field] = preg_replace(array( '#{тип}#u', '#{районе}#u', '#{район}#u' ), '', $values[$field]);
      }
      $values[$field] = $this->parseLandingField($values[$field]);
    }
    
    $this->getObject()->fromArray($values);
  }
  
  /* Замена шаблонов */
  private function parseLandingField($field_data) {
    $result = strval($field_data);
    
    preg_match_all('#\{([^\}]+)#um', $field_data, $tags);
    $tags = (isset($tags[1]) && !empty($tags[1])) ? $tags[1] : array();
    foreach($tags as $tag) {
      if(method_exists($this, 'f' . md5(mb_strtolower($tag)))) {
        $replacement = call_user_func(array($this, 'f' . md5(mb_strtolower($tag))), $tag);
        $result = preg_replace('#\{' . $tag . '\}#', $replacement, $result);
      }
    }

    return $result;
  }
  
  //Шаблон "тип"
  private function f09cac27fb42ed1979358ba424f8ead00($tag = 'тип') {
    $result = '{' . $tag . '}';
    
    $type = $this->getValue('type');
    if(empty($type)){
      $type = $this->getValue('params');
      $type = $type['type'];
    }
    
    if(isset(LandingPage::$types[$type])) {
      $result = LandingPage::$types[$type];
      
      if($tag == mb_strtolower($tag)) {
        $result = mb_strtolower( $result );
      }
    }
    
    return $result;
  }
  
  //Шаблон "регион"
  private function fcb205dd61f331f92301a027729cc6f0d($tag = 'регион') {
    $result = '{' . $tag . '}';
    
    $region = $this->getValue('region_id');
    if(empty($region)){
      $region = $this->getValue('params');
      $region = $region['region_id'];
    }
    $region = Doctrine::getTable('Region')->find($region);
    if($region) {
      $result = $region->getName();
    }
    
    return $result;
  }
  
  //Шаблон "регионе"
  private function fa4b71a9e1f267cf0509114f91f2be83d($tag = 'регионе') {
    $result = '{' . $tag . '}';
    
    $result = $this->fcb205dd61f331f92301a027729cc6f0d($tag);
    $result = preg_replace('/\s+обл./um', ' область', $result);
    
    $inflector = new WordInflector();
    $result = $inflector->get($result, WordInflector::TYPE_DATIVE);
       
    return $result;
  }
  
  //Шаблон "район"
  private function f66f872ee0c56fb3dc21a01e1cb8724f1($tag = 'район') {
    $result = '{' . $tag . '}';
    
    $params = $this->getValue('params');
    if(empty($params) || !isset($params['regionnode'])) {
      return $result;
    }
        
    foreach ($params['regionnode'] as $node) {      
      //Если метро
      if(preg_match('/^м\\.*\\s+|\\s+м\\.*$/u', $node) && !in_array($params['region_id'], array( '77' ))) {
        continue;
      } else { //Поиск района по метро
        if(is_string($result)) {
          $result = array();
        }
        
        $path = $this->array_search_recursive($node, Regionnode::$districts);
        if(!empty($path) && count($path) >= 2) {
          $result[] = $path[1];
        }
        continue;
      }
      
      //Если не район
      if(!preg_match('#р\-н#u', $node)){
        $name = Regionnode::unformatName($node);
        $name = array_shift($name);
        $socr = preg_replace(array('#\s*' . $name . '\s*#u', '#\.*#u'), array('', ''), $node);
        
        $query = Doctrine::getTable('Regionnode')->getConnection()->prepare("
          SELECT r.name FROM `regionnode` AS rn INNER JOIN `regionnode` AS r ON rn.region_id = r.id WHERE rn.name = ? AND rn.socr = ? AND r.socr = 'р-н'
        ");
        $query->execute(array( $name, $socr ));
        if($query->columnCount()){
          $result = $query->fetchObject()->name . ' ' . $tag;
          break;
        }
      } else {
        $result = $node;
        break;
      }
    }
    
    return is_array($result) ? implode(", ", $result) : $result;
  }
  
  //Шаблон "районе"
  private function ff6eec52bbcc6b828a4df56d49c3e435f($tag = 'районе') {
    $result = '{' . $tag . '}';
    
    $result = $this->f66f872ee0c56fb3dc21a01e1cb8724f1($tag);
    $result = preg_replace('/\s*р\-н\s*/u', ' район', $result);
    
    $inflector = new WordInflector();
    $result = $inflector->get($result, WordInflector::TYPE_DATIVE);
        
    return $result;
  }
  
  //Шаблон "метро"
  private function ffd314d2a3a0c37c3d9771f76d9311a5f($tag = 'метро') {
    $result = array('{' . $tag . '}');
    
    $params = $this->getValue('params');
    if(!empty($params) && isset($params['regionnode'])) {
      $result = array();
      
      foreach ($params['regionnode'] as $node) {
        //Если метро
        if(preg_match('/^м\\.*\\s+|\\s+м\\.*$/u', $node)) {
          $result[] = $node;
        }
      }
    }
    
    return implode(", ", $result);
  }
  
  private function array_search_recursive( $needle, $haystack, $strict=false, $path=array() )
  {
    if( !is_array($haystack) ) {
      return false;
    }

    foreach( $haystack as $key => $val ) {
      if( is_array($val) && $subPath = $this->array_search_recursive($needle, $val, $strict, $path) ) {
        $path = array_merge($path, array($key), $subPath);
        return $path;
      } elseif( (!$strict && $val == $needle) || ($strict && $val === $needle) ) {
        $path[] = $key;
        return $path;
      }
    }
    return false;
  }
}
