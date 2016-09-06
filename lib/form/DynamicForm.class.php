<?php

/**
 * dynamicForm
 *
 * @package    domus
 * @subpackage forms
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class DynamicForm extends BaseForm implements AjaxFormInterface
{

  protected
    $_type = null,
    $_types = array(),
    $_periods = array(),
    $_str2replace = array('#обл\.#u','/\s*ш\.\s+([^,]+)/u'),
    $_replacements = array('область',' $1 шоссе');

  public static $currencies = array('RUR' => 'РУБ', 'USD' => 'USD', 'EUR' => 'EUR');
  public static $custom_entities = array('flat','cottage','townhouse');

  /**
   * Constructor.
   *
   * @param string $type        Form type
   * @param array  $defaults    An array of field default values
   * @param array  $options     An array of options
   * @param string $CRFSSecret  A CSRF secret (false to disable CSRF protection, null to use the global CSRF secret)
   */
  public function __construct($type, $show_contacts = false, $defaults = array(), $options = array(), $CSRFSecret = null)
  {
    $this->_type = $type;
    $this->_show_contacts = $show_contacts;

    foreach (sfConfig::get('app_lot_types') as $type => $names) {
      $this->_types[$type] = str_replace('/', ' или ', $names['dative']);
    }
    if (!sfContext::getInstance()->getUser()->hasCredential('nv-access')) {
      unset(
        $this->_types['new_building-sale'],
        $this->_types['cottage-sale']
      );
    }
    if (null != $this->_type) {
      $type = explode('-', $this->_type);
    }
    else {
      $type = explode('-', $type);
    }
    $periods = sfConfig::get('app_lot_periods');
    $this->_periods = $periods[$type[1]];
    if (in_array($this->_type, array('new_building-sale', 'cottage-sale'))) {
      $this->_periods = array(90 => '3 месяца', 180 => '6 месяцев', 360 => '12 месяцев');
    }

    parent::__construct($defaults, $options, $CSRFSecret);
  }


  public function configure() {
    $this->loadBaseFields();
    $this->loadFields();
    $this->loadAdditionalFields();

    $this->setRates();
    if ($this->_type == 'new_building-sale') {
      $flats = new sfForm();
      $flat = new FlatForm();
      $flats->embedForm(0, $flat);
      $this->embedForm('flats', $flats);
      $this->widgetSchema->moveField('flats', sfWidgetFormSchema::AFTER, 'image');
    }
    //Коттеджи и таунхаусы
    if ($this->_type == 'cottage-sale') {
      $cottages = new sfForm();
      $cottage = new CottageForm();
      $cottages->embedForm(0, $cottage);
      $this->embedForm('cottages', $cottages);
      $this->widgetSchema->moveField('cottages', sfWidgetFormSchema::AFTER, 'image');
      
      $townhouses = new sfForm();
      $townhouse = new TownhouseForm();
      $townhouses->embedForm(0, $townhouse);
      $this->embedForm('townhouses', $townhouses);
      $this->widgetSchema->moveField('townhouses', sfWidgetFormSchema::AFTER, 'cottages');
    }
      
    $this->widgetSchema->setNameFormat('dynamicform[%s]');
    AjaxForm::setErrorMessages($this);
  }

  public function getErrorsArray()
  {
    return AjaxForm::getErrorsArray($this);
  }

  public function getJavaScripts() {
    return array_merge(array('jquery', 'autocomplete'), parent::getJavaScripts());
  }

  public function getStylesheets() {
    return array_merge(array('autocomplete'), parent::getStylesheets());
  }

  public function save() {
    $values = $this->getValues();
    $values['coords'] = explode(',', $values['coords']);
    if (!isset($values['coords'][1])) {
      $values['coords'] = array(null, null);
    }
    else {
      $values['coords'] = array( (float) $values['coords'][0], (float) $values['coords'][1] );
    }

    $address_info = array(
      'region_node' => $values['region_node'],
      'city_region' => $values['city_region'],
      'street'      => $values['street'],
      'address'     => array(
        'house'      => $values['address']['house'],
        'building'   => $values['address']['building'],
        'structure'  => $values['address']['structure']
      )
    );

    foreach ($values as $key => $value) {
      if ($value === '') {
        $values[$key] = null;
      }
    }

    $price = array(0,'RUR');
    if(!in_array($values['type'], array('new_building-sale','cottage-sale'))){
      $price = isset($values['field2']) ? $values['field2'] : ( isset($values['field16']) ? $values['field16'] : $values['field53'] );
      $price = array(substr($price, 3), substr($price, 0, 3));
    }

    if (!is_array($values['region_node'])) {
      $values['region_node'] = array($values['region_node']);
    }
    $address1 = array();
    $address1[] = Doctrine::getTable('Region')->find($values['region_id'])->name;
    $query = Doctrine::getTable('RegionNode')->createQuery()->whereIn('id', $values['region_node']);
    foreach ($query->execute() as $region_node) {
      $address1[] = Regionnode::formatName($region_node->name, $region_node->socr);
    }
    if ($values['city_region']) {
      $address1[] = $values['city_region'];
    }

    $address2 = array($values['street']);
    if ($values['address']['house']) {
      $address2[] = 'дом ' . $values['address']['house'];
    }
    if ($values['address']['building']) {
      $address2[] = 'корп. ' . $values['address']['building'];
    }
    if ($values['address']['structure']) {
      $address2[] = 'стр. ' . $values['address']['structure'];
    }

    if ($values['id']) {
      $lot = Doctrine::getTable('Lot')->getRestrictedToUser($values['id']);
      if (! $lot instanceOf Lot) {
        throw new sfException('Wrong parameters for existing object.');
      }
      if ($lot->status == 'restricted') {
        $lot->status = 'moderate';
      }
      $lot->LotInfo->delete();
    }
    else {
      $lot = new Lot();
      $lot->updated_at = date('Y-m-d H:i:s');
    }

    $lot->address_info = $address_info;
    $lot->type = $values['type'];

    $lot->region_id = $values['region_id'];
    $lot->latitude = $values['coords'][0];
    $lot->longitude = $values['coords'][1];
    $lot->address1 = preg_replace($this->_str2replace, $this->_replacements, implode(', ', $address1));
    $lot->address2 = implode(', ', $address2);
    
    $lot->price = $price[0];
    $lot->currency = $price[1];

    $lot->brief = self::makeBrief($values);
    $lot->description = $values['description'];

    foreach (array('organization_link', 'organization_contact_name') as $param) {
      if (isset($values[$param])) {
        $lot->$param = $values[$param];
      }
      else {
        $lot->$param = null;
      }
    }
    
    $lot->organization_contact_phone = null;
    if (isset($values['organization_contact_phone'])){
      $phones = array();
      foreach ($values['organization_contact_phone'] as $phone){
        if ($phone = Toolkit::formatPhoneNumber($phone['country'], $phone['area'], $phone['number'])) {
          $phones[] = $phone;
        }
      }
      if (count($phones) > 0) {
        $lot->organization_contact_phone = implode(', ', $phones);
      }
    }

    if (!$lot->user_id) {
      $lot->user_id = sfContext::getInstance()->getUser()->id;
    }
    if (!$lot->created_at) {
      $lot->created_at = date('Y-m-d H:i:s');
    }
    if (!$lot->active_till) {
      $lot->active_till = date('Y-m-d H:i:s', strtotime('+'.$values['period'].' days'));
    }

    $lot->thumb = count($values['image']) ? $values['thumb'] : null;
    if(($this->_type == 'new_building-sale' || $this->_type == 'cottage-sale')
      && sfContext::getInstance()->getUser()->hasCredential('admin-access')) {
      $lot->nb_more_info_link = !empty($values['nb_more_info_link']) ? $values['nb_more_info_link'] : null;
      $lot->nb_banner_id = !empty($values['nb_banner_id']) ? $values['nb_banner_id'] : null;
      $lot->slug = !empty($values['slug']) ? $values['slug']."-manual" : null;
    }
    $lot->save(null, false, false);

    if (count($values['image'])) {
      $images = array();
      foreach ($values['image'] as $i => $image) {
        $image = sprintf('%s/%s/source/%s', sfConfig::get('sf_web_dir'), sfConfig::get('app_upload_tmp_dir'), $image);
        $filename = ($i + 1) . '.' . pathinfo($image, PATHINFO_EXTENSION);
        if (rename($image, $lot->full_image_path . '/' . $filename)) {
          chmod($lot->full_image_path .'/' . $filename, 0666);
          $images[] = $filename;
        }
      }
      if (count($images)) {
        $lot->images = $images;
      }
    }
    else {
      $lot->images = null;
    }
    
    //Пересчитываем рейтинг уже после первого сохранения
    if (!empty($values['rating']))
      $lot->rating = $values['rating'];
    else
      $lot->rating = 0;

    unset(
      $values['type'], $values['region_id'], $values['period'], $values['coords'],
      $values['region_node'], $values['city_region'], $values['street'],
      $values['address'], $values['description'], $values['image'],
      $values['organization_link'], $values['organization_contact_name'],
      $values['organization_contact_phone'], $values['organization_contact_title'],
      $values['agree'], $values['field2'], $values['field16'], $values['field53'],
      $values['id'], $values['thumb'], $values['slug']
    );
    if($this->_type == 'new_building-sale' && sfContext::getInstance()->getUser()->hasCredential('admin-access')) {
      unset($values['hidden_description'], $values['rating'], $values['nb_more_info_link'], $values['nb_banner_id']);
    }
    if($this->_type == 'cottage-sale' && sfContext::getInstance()->getUser()->hasCredential('admin-access')) {
      unset($values['hidden_description'], $values['rating'], $values['nb_more_info_link'], $values['nb_banner_id']);
    }

    foreach ($values as $key => $value) {
      if($value === null || in_array(substr($key, 0, strlen($key)-1), self::$custom_entities)) continue;
      $lotInfo = new LotInfo();
      $lotInfo->field_id = (int) str_replace('field', '', $key);
      $lotInfo->value = $value;
      $lot->LotInfo[] = $lotInfo;
    }
    $lot->save();

    //Save flats, cottages and townhouses
    if(in_array($this->_type, array('new_building-sale','cottage-sale'))){ //Может это можно удалить?
      foreach (self::$custom_entities as $v) {
        $section = $v.'s';
        if(!isset($this->embeddedForms[$section])) continue;

        $collection = $section == 'townhouses' ? 'Cottages' : ucfirst($section); //Townhouses custom
        $saved_items = array();
        foreach ($this->embeddedForms[$section]->embeddedForms as $k => $form) {
          $form->updateObject($values[$section][$k]);
          $f = $form->getObject();
          $f->lot_id = $lot->id;
          if(isset($f->type) && empty($f->type)) $f->type = $v; //Type for cottages and townhouses
          $f->save();
          $saved_items[] = $f->id; 
        }

        foreach ($lot->$collection as $item) {
          if(isset($item->type) && $item->type != $v) continue;
          if (!in_array($item->id, $saved_items)) {
            $item->delete();
          }
        }
      }
    }

    return $lot->id;
  }

  public function bindFromObject(Lot $lot) {
    $values = $lot->address_info;
    list($values['period'], $null) = each($this->_periods);
    $values['id'] = $lot->id;

    if ($lot->latitude) {
      $values['coords'] = $lot->latitude.','.$lot->longitude;
    }
    $values['region_id'] = $lot->region_id;
    $values['type'] = $lot->type;


    $values['field2'] = $values['field16'] = $values['field53'] = $lot->currency.$lot->price;

    $values['description'] = $lot->description;
    if(($this->_type == 'new_building-sale' || $this->_type == 'cottage-sale')
      && sfContext::getInstance()->getUser()->hasCredential('admin-access')) {
      $values['hidden_description'] = $lot->hidden_description;
      $values['rating'] = $lot->rating;
      $values['nb_more_info_link'] = $lot->nb_more_info_link;
      $values['nb_banner_id'] = $lot->nb_banner_id;
      $values['slug'] = preg_replace("#-{$lot->id}$#", '', $lot->slug);
    }
    foreach (array('organization_link', 'organization_contact_name') as $param) {
      if ($lot->$param) {
        $values[$param] = $lot->$param;
      }
    }

    if ($lot->organization_contact_phone) {
      $phones = explode(', ', $lot->organization_contact_phone);
      foreach ($phones as $i => $phone) {
        $phones[$i] = Toolkit::unformatPhoneNumber($phone);
      }
      $values['organization_contact_phone'] = $phones;
    }

    foreach ($lot->LotInfo as $lotInfo) {
      if (in_array($lotInfo->FormField->type, array('multiple', 'radiocombo'))) {
        $value = explode(', ', $lotInfo->value);
      }
      else {
        $value = $lotInfo->value;
      }
      $values['field'.$lotInfo->field_id] = $value;
    }

    $values['thumb'] = $lot->thumb;
    $values['image'] = array();
    foreach ($lot->images as $image) {
      $filename = $lot->id . '_' . $image;
      $destanation = sprintf('%s/%s/source/%s', sfConfig::get('sf_web_dir'), sfConfig::get('app_upload_tmp_dir'), $filename);
      if (copy($lot->full_image_path . '/' . $image, $destanation)) {
        $values['image'][] = $filename;
      }
    }
    if (!count($values['image'])) {
      unset($values['image']);
    }

    $values['agree'] = true;

    $this->setDefaults($values);//Зачем тут был bind? С ним embedded forms не работали
    if ($this->_type == 'new_building-sale' && (count($lot->Flats) > 0)) {
      $flats = new sfForm();
      $cnt = 0;
      foreach ($lot->Flats as $v) {
        $flat = new FlatForm($v);
        $flats->embedForm($cnt, $flat);
        $cnt++;
      }
      $this->embedForm('flats', $flats);
      $this->widgetSchema->moveField('flats', sfWidgetFormSchema::AFTER, 'image');
    }
    //Bind cottages and townhouses
    if ($this->_type == 'cottage-sale' && (count($lot->Cottages) > 0)) {
      $collections = array();
      foreach ($lot->Cottages as $v) {
        if(empty($v->type)) $v->type = 'cottage'; //Страховка

        if(!isset(${$v->type.'s'})){
          ${$v->type.'s'} = new sfForm();
          ${'cnt'.$v->type} = 0;
          $collections[] = array('section' => $v->type.'s', 'data' => ${$v->type.'s'});
        }

        $frm = ucfirst($v->type).'Form';
        ${$v->type} = new $frm($v);
        ${$v->type.'s'}->embedForm(${'cnt'.$v->type}, ${$v->type});
        ${'cnt'.$v->type}++;
      }
      
      foreach ($collections as $v) {
        $this->embedForm($v['section'], $v['data']);
        $this->widgetSchema->moveField($v['section'], sfWidgetFormSchema::AFTER, 'image');
      }
    }
  }

  public static function makeBrief(array $values) {
    $brief = array();

    switch ($values['type']) {
      case 'apartament-sale':
      case 'apartament-rent':
        if (!empty($values['field1'])) {
          $brief[] = 'Площадь:' . $values['field1'] . ' м<sup>2</sup>';
        }
        if (!empty($values['field3']) && !empty($values['field4'])) {
          $brief[] = 'Этаж:'. $values['field3'] . '/' . $values['field4'];
        }

        $type = isset($values['field55']) ? $values['field55'] : $values['field54'];
        if (strpos($type, 'свобод') !== false) {
          $type = 'своб. планировка';
        }
        elseif (strpos($type, 'комнатная') !== false) {
          $type = explode(' ', $type);
          array_pop($type);
          $type = implode(' ', $type);
        }
        $brief[] = $type;
        break;

      case 'house-sale':
      case 'house-rent':
        if (!empty($values['field26']))
          $brief[] = 'Площадь:' . $values['field26'] . ' м<sup>2</sup>';
        if (!empty($values['field27']))
          $brief[] = 'Участок:' . $values['field27'] . ' соток';
        if (!empty($values['field4']))
          $brief[] = 'Этажность:' . $values['field4'];
        break;

      case 'commercial-sale':
      case 'commercial-rent':
        if (isset($values['field46'])) {
          $brief[] = 'Площадь:' . $values['field46'] . ' м<sup>2</sup>';
        }
        if (isset($values['field47'])) {
          $brief[] = 'Участок:' . $values['field47'] . ' га';
        }

        if (count($values['field45']) == 1) {
          if (is_array($values['field45'])) {
            $brief[] = 'Тип: ' . $values['field45'][0];
          } else {
            $brief[] = 'Тип: ' . $values['field45'];
          }
        }
        else {
          $brief[] = 'Тип: многофункциональный';
        }

        break;
      case 'new_building-sale':
        if (isset($values['field72'], $values['field73'])) {
          $brief[] = 'Площадь: от ' . $values['field72'] . ' до ' . $values['field73'] . ' м<sup>2</sup>';
        }
        elseif (isset($values['field72'])) {
          $brief[] = 'Площадь: от ' . $values['field72'] . ' м<sup>2</sup>';
        }
        if (isset($values['field76'])) {
          $brief[] = 'Квартиры: ' . implode(', ', $values['field76']) . ' комнатные';
        }
        if (isset($values['field74'])) {
          $brief[] = 'Состояние: '  . $values['field74'];
        }
        break;
      case 'cottage-sale':
        if(!empty($values['field92'])) $brief[] = 'Расстояние от МКАД: ' . $values['field92'];
        if(!empty($values['field93'])) $brief[] = 'Количество участков: ' . $values['field93'];
        
        if( !empty($values['field94']) || !empty($values['field95'])) {
          $brief[] = 'Площадь участков:';
          if( !empty($values['field94']) ) $brief[count($brief)-1] .= ' от ' . round($values['field94'], 2);
          if( !empty($values['field95']) ) $brief[count($brief)-1] .= ' до ' . round($values['field95'], 2);
          $brief[count($brief)-1] .= ' соток';
        }
        
        if( !empty($values['field98']) || !empty($values['field99'])) {
          $brief[] = 'Площадь домов:';
          if( !empty($values['field98']) ) $brief[count($brief)-1] .= ' от ' . round($values['field98'], 2);
          if( !empty($values['field99']) ) $brief[count($brief)-1] .= ' до ' . round($values['field99'], 2);
          $brief[count($brief)-1] .= ' м<sup>2</sup>';
        }
        
        if( !empty($values['field102']) || !empty($values['field103'])) {
          $brief[] = 'Площадь домов:';
          if( !empty($values['field102']) ) $brief[count($brief)-1] .= ' от ' . round($values['field102'], 2);
          if( !empty($values['field103']) ) $brief[count($brief)-1] .= ' до ' . round($values['field103'], 2);
          $brief[count($brief)-1] .= ' м<sup>2</sup>';
        }
        break;
    }

    return implode("\n", $brief);
  }

  public function render($attributes = array()) {
    foreach ($this->getJavascripts() as $js) {
      sfContext::getInstance()->getResponse()->addJavascript($js);
    }
    foreach ($this->getStylesheets() as $js) {
      sfContext::getInstance()->getResponse()->addStylesheet($js);
    }
    return parent::render($attributes);
  }

  private function loadBaseFields() {
    $this->widgetSchema['id'] = new sfWidgetFormInputHidden();

    $this->widgetSchema['type'] = new sfWidgetFormChoice(array(
        'choices'  => $this->_types,
        'default' => $this->_type,
        'expanded' => true,
      ));

    $this->widgetSchema['period'] = new sfWidgetFormSelect(array(
        'choices' => $this->_periods
      ));

    if ($this->_type == 'new_building-sale' || $this->_type == 'cottage-sale') {
      $this->widgetSchema['period']->setOption('default', 180);
    }

    $this->widgetSchema['region_id']
      = $this->_type == 'new_building-sale'
      ? new sfWidgetFormChoice(array('choices' => array(77 => 'г. Москва', 50 => 'Московская область'), 'label' => '* Регион'))
      : new sfWidgetFormDoctrineChoice(array('model' => 'Region', 'add_empty' => true, 'label' => '* Регион'));
    
    if($this->_type == 'cottage-sale') 
      $this->widgetSchema['region_id'] = new sfWidgetFormChoice(array('choices' => array(50 => 'Московская область'), 'label' => '* Регион'));

    $this->widgetSchema['region_node']
      = new sfWidgetFormRegionnode(array(
        'source'  => 'form/regionnode',
        'choices' => array('' => 'Выберите регион'),
        'label'   => $this->_type == 'cottage-sale' ? '* Шоссе/Город/Район' : '* Метро/Район/Город'
      ));

    $this->widgetSchema['city_region']
      = new sfWidgetFormInputText(
        array('label' => '* Район/Населенный пункт'),
        array('class' => 'autocomplete-city',
              'source' => sfContext::getInstance()->getController()->genUrl('form/city'))
      );

    $this->widgetSchema['street']
      = new sfWidgetFormInputText(
        array('label' => 'Улица'),
        array('class' => 'autocomplete-street',
              'source' => sfContext::getInstance()->getController()->genUrl('form/street'))
      );

    $this->widgetSchema['address']
      = new sfWidgetFormInputMultiple(
        array(
          'inputs' => array(
            'house' => new sfWidgetFormInputText(array('label' => 'дом'), array('size' => 3)),
            'building' => new sfWidgetFormInputText(array('label' => 'корпус'), array('size' => 3)),
            'structure' => new sfWidgetFormInputText(array('label' => 'строение'), array('size' => 3))
          ),
          'label' => 'обязательно для городов'
        )
      );

    $this->widgetSchema['coords'] = new sfWidgetFormGMap(array(
        'label' => 'Если маркер неправильно указывает местоположение объекта на карте,
                    вы можете передвинуть его в нужное место'
      ), array(
        'need_colspan' => true
      ));

    $this->validatorSchema['id'] = new sfValidatorNumber(array('required' => false));
    $this->validatorSchema['type']
      = new sfValidatorChoice(array('required' => true, 'choices' => array_keys($this->_types)));
    $this->validatorSchema['period']
      = new sfValidatorChoice(array('required' => true, 'choices' => array_keys($this->_periods)));
    $this->validatorSchema['region_id']
      = new sfValidatorDoctrineChoice(array('model' => 'Region'));
    $this->validatorSchema['region_node']
      = new sfValidatorDoctrineChoice(array('model' => 'Regionnode', 'multiple' => true));
    $this->validatorSchema['city_region']  = new sfValidatorString(array('required' => false));
    $this->validatorSchema['street']       = new sfValidatorString(array('required' => false));
    $this->validatorSchema['address']      = new sfValidatorPass();
    $this->validatorSchema['coords']       = new sfValidatorRegex(array('required' => false, 'pattern' => '/^\d+\.\d+,\d+\.\d+$/'));
  }

  private function loadAdditionalFields() {
    $description_max = 1000;
    if($this->_type == 'new_building-sale') $description_max = null;
    $this->widgetSchema['description']
      = new sfWidgetFormTextarea(array('label' => 'Описание объекта'), array('need_colspan' => true, 'max' => $description_max));

    if(($this->_type == 'new_building-sale' || $this->_type == 'cottage-sale')
      && sfContext::getInstance()->getUser()->hasCredential('admin-access')) {

      if(sfContext::getInstance()->getRequest()->getParameter('action') == 'edit') {
        $label = $this->_type == 'new_building-sale' ? 
          'Описание новостройки с сайта' : 
          'Описание коттеджного поселка с сайта';
        $this->widgetSchema['hidden_description']
          = new sfWidgetFormTextarea(array('label' => $label), array('need_colspan' => true));
          $this->validatorSchema['hidden_description']  = new sfValidatorString(array('required' => false));
          
        $this->widgetSchema['slug'] 
          = new sfWidgetFormInputText(array('label' => 'Последняя часть URL страницы лота'), array('need_colspan' => true));
        $this->widgetSchema->setHelp('slug', 'Оставьте пустым для автоматической генерации. ID лота будет добавлено автоматически.');
        $this->validatorSchema['slug']  = new sfValidatorRegex(array(
            'pattern' => '/^[A-Za-z0-9_-]+$/',
            'max_length' => 500, 
            'required' => false)
        );
      }

      $this->widgetSchema['rating'] = new sfWidgetFormInputText(array('label' => 'Рейтинг'));
      $this->validatorSchema['rating'] = new sfValidatorString(array('required' => false));

      $this->widgetSchema['nb_banner_id'] = new sfWidgetFormInputText(array('label' => 'ID баннера из OpenX'));
      $this->validatorSchema['nb_banner_id'] = new sfValidatorNumber(array('required' => false));

      $label = $this->_type == 'new_building-sale' ? 
        'Сcылка на страницу с информацией о новостройке' : 
        'Сcылка на страницу с информацией о коттеджном поселке';
      $this->widgetSchema['nb_more_info_link']
        = new sfWidgetFormInputText(array('label' => $label), array('need_colspan' => true));
      $this->widgetSchema->setHelp('nb_more_info_link', 'Например: http://www.myfirm.ru/tverskaya14.htm');
      $this->validatorSchema['nb_more_info_link']  = new sfValidatorUrl(array('required' => false));
    }

    $this->widgetSchema['thumb'] = new sfWidgetFormInputHidden();
    $this->widgetSchema['image']
      = new sfWidgetFormAjaxUpload(array(
        'url' => 'form/upload',
        'max' => empty($description_max) ? null : 6,
        'label' => 'Фотографии'
      ), array('need_colspan' => true));

    if ($this->_show_contacts) {
      $this->loadContactFields();
    }

    $this->widgetSchema['agree'] = new sfWidgetFormInputCheckbox(array(
        'label' => '* Я ознакомлен и принимаю <a href="/terms" target="_blank">Условия
                    и правила обслуживания и размещения информации</a>',

      ), array('need_colspan' => true, 'checked' => true,));

    if(!empty($description_max)) {
      $this->widgetSchema->setHelp('description', 'Не более 1000 символов');
      $this->widgetSchema->setHelp('image', 'До 6 изображений');
    }

    $this->validatorSchema['description']  = new sfValidatorString(array('required' => false, 'max_length' => $description_max));
    $this->validatorSchema['thumb']  = new sfValidatorPass();
    $this->validatorSchema['image']  = new sfValidatorPass();
    $this->validatorSchema['agree']  = new sfValidatorBoolean(array('required' => true));
  }

  private function loadContactFields() {
    $user = sfContext::getInstance()->getUser();
    if ($user->type == 'company'){
      $this->widgetSchema['organization_link']
        = new sfWidgetFormInputText(array(
        'label' => $this->_type == 'cottage-sale' ? 'Ссылка на сайт застройщика' : 'Сcылка на страницу объекта на сайте компании'
      ), array('need_colspan' => true));
      $this->widgetSchema->setHelp('organization_link', 'Например: http://www.myfirm.ru/tverskaya14.htm');
      $this->validatorSchema['organization_link']  = new sfValidatorUrl(array('required' => false));

    }
    $this->widgetSchema['organization_contact_title']
      = new sfWidgetFormInputHidden(array('label' => 'Дополнительная контактная информация для объявления'), array('need_colspan' => true));

    $this->widgetSchema['organization_contact_name']
      = new sfWidgetFormInputText(array('label' => 'Представитель компании'));

    $this->widgetSchema['organization_contact_phone']
      = new sfWidgetFormInputMultiplePhone(
        array(
          'label' => 'Доп. телефонный номер',
          'default' => array('country' => '+7', 'area' => '', 'number' => '')
        )
      );

    $this->validatorSchema['organization_contact_title']  = new sfValidatorPass();
    $this->validatorSchema['organization_contact_name'] = new sfValidatorString(array('required' => false));
    $this->validatorSchema['organization_contact_phone'] = new sfValidatorPass();
  }

  private function loadFields() {
    $user = sfContext::getInstance()->getUser();
    $q = Doctrine::getTable('FormItem')
      ->createQuery()
      ->innerJoin('FormItem.FormField')
      ->where('type = ?', $this->_type)
      ->orderBy('position');

    foreach ($q->execute() as $fieldinfo) {
      $field = $fieldinfo->FormField;
      if ($user->type == 'owner' && $field->id == 66 && $this->_type == 'apartament-rent') {
        continue;
      }
      $this->widgetSchema['field'.$field->id] = $field->getWidget($fieldinfo->toArray());
      $this->validatorSchema['field'.$field->id] = $field->getValidator($fieldinfo->toArray());
      if ($field->help) {
        $this->widgetSchema->setHelp('field'.$field->id, $field->help);
      }
    }
  }

  private function setRates()
  {
    $config = Rating::getConfig('lot');
    foreach ($this->widgetSchema->getFields() as $name => $widget) {
      if (!$widget->getAttribute('rating')) {
        if ($name == 'image' && !empty($config['photo'])) {
          $widget->setAttribute('rating', $config['photo']['first'] . '|' . $config['photo']['other']);
        } elseif (isset($config[$name])) {
          $widget->setAttribute('rating', $config[$name]);
        }
      }
    }
  }

  public function addEntity($type, $name, $values = array())
  {
    $f = null;
    $form_class = $type.'Form';
    $section = strtolower($type).'s';
    
    $entity = new $form_class();
    if (isset($values['id'])) {
      $f = Doctrine::getTable(get_class($entity->getObject()))->find(array($values['id'])); 
    }
    if($f) $entity = new $form_class($f);
    
    $this->embeddedForms[$section]->embedForm($name, $entity);
    $this->embedForm($section, $this->embeddedForms[$section]);

    return $entity;
  }

  public function bind(array $taintedValues = null, array $taintedFiles = null)
  {
    if ($taintedValues['organization_contact_phone']) {
      $phones = $taintedValues['organization_contact_phone'];	
      $taintedValues['organization_contact_phone'] = array();	
      foreach ($phones['country'] as $i => $country) {	
        $area = empty($phones['area'][$i]) ? null : $phones['area'][$i];	
        $number = empty($phones['number'][$i]) ? null : $phones['number'][$i];	
        if(empty($number) || empty($area)) continue;	
        $phone = array( 'country' => $country, 'area' => $area, 'number' => $number);	
        $taintedValues['organization_contact_phone'][] = $phone;	
      }	
    }
    
    if (!in_array($this->_type, array('new_building-sale','cottage-sale'))) {
      return parent::bind($taintedValues, $taintedFiles);
    }

    foreach (self::$custom_entities as $t) {
      $section = $t.'s';
      if (!isset($taintedValues[$section])) continue;
      
      $this->embedForm($section, new sfForm());
      foreach ($taintedValues[$section] as $key => $value)
      {
        if (isset($value['price']) && is_numeric($value['price'])) {
          $this->addEntity(ucfirst($t), $key, $value);
        } else {
          unset($taintedValues[$section][$key]);
        }
      }
    }
    
    parent::bind($taintedValues, $taintedFiles);
  }
}