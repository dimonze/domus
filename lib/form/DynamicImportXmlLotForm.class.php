<?php

/**
 * dynamicForm
 *
 * @package    domus
 * @subpackage forms
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class DynamicImportXmlLotForm extends BaseForm
{

  protected
    $_type = null,
    $_types = array(),
    $_periods = array(),
    $_str2replace = array('обл.'),
    $_replacements = array('область'),
    $_sourse = null;

  public static $currencies = array('RUR' => 'РУБ', 'USD' => 'USD', 'EUR' => 'EUR');

  /**
   * Constructor.
   *
   * @param string $type        Form type
   * @param array  $defaults    An array of field default values
   * @param array  $options     An array of options
   * @param string $CRFSSecret  A CSRF secret (false to disable CSRF protection, null to use the global CSRF secret)
   */
  public function __construct($type, $sourse, $defaults = array(), $options = array(), $CSRFSecret = null)
  {
    $this->_type = $type;

    foreach (sfConfig::get('app_lot_types') as $type => $names) {
      $this->_types[$type] = str_replace('/', ' или ', $names['dative']);
    }
    if (null != $this->_type) {
      $type = explode('-', $this->_type);
    }
    else {
      $type = explode('-', $type);
    }
    $periods = sfConfig::get('app_lot_periods');
    $this->_sourse = $sourse;

    parent::__construct($defaults, $options, $CSRFSecret);
  }


  public function configure() {
    $this->loadBaseFields();
    $this->loadFields();
    $this->loadAdditionalFields();

    $this->setRates();

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

    $price = isset($values['field2']) ? $values['field2'] : ( isset($values['field16']) ? $values['field16'] : $values['field53'] );
    $price = array(substr($price, 3), substr($price, 0, 3));

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
    }
    
    $lot->address_info = $address_info;
    $lot->type = $values['type'];

    $lot->region_id = $values['region_id'];
    $lot->latitude = $values['coords'][0];
    $lot->longitude = $values['coords'][1];
    $lot->address1 = str_replace($this->_str2replace, $this->_replacements, implode(', ', $address1));
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
    if (isset($values['organization_contact_phone'])) {
      $phones = array();
      foreach ($values['organization_contact_phone']['country'] as $i => $country) {
        $area = $values['organization_contact_phone']['area'][$i];
        $number = $values['organization_contact_phone']['number'][$i];
        if ($phone = Toolkit::formatPhoneNumber($country, $area, $number)) {
          $phones[] = $phone;
        }
      }
      if (count($phones) > 0) {
        $lot->organization_contact_phone = implode(', ', $phones);
      }
      else {
        $lot->organization_contact_phone = null;
      }
    }
    else {
      $lot->organization_contact_phone = null;
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

    $lot->save();
    
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

    unset(
      $values['type'], $values['region_id'], $values['period'], $values['coords'],
      $values['region_node'], $values['city_region'], $values['street'],
      $values['address'], $values['description'], $values['image'],
      $values['organization_link'], $values['organization_contact_name'],
      $values['organization_contact_phone'], $values['organization_contact_title'],
      $values['agree'], $values['field2'], $values['field16'], $values['field53'],
      $values['id'], $values['thumb']
    );


    foreach ($values as $key => $value) {
      if ($value === null) continue;
      $lotInfo = new LotInfo();
      $lotInfo->field_id = (int) str_replace('field', '', $key);
      $lotInfo->value = $value;
      $lot->LotInfo[] = $lotInfo;
    }

    return $lot->save();
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

    $this->bind($values);
  }

  public static function makeBrief(array $values) {
    $brief = array();

    switch ($values['type']) {
      case 'apartament-sale':
      case 'apartament-rent':
        $brief[] = 'Площадь:' . $values['field1'] . ' м<sup>2</sup>';
        if (!empty($values['field3']) && !empty($values['field4'])) {
          $brief[] = 'Этаж:'. $values['field3'] . '/' . $values['field4'];
        }

        $type = isset ($values['field55']) ? $values['field55'] : $values['field54'];
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
        if ($values['field26'])
          $brief[] = 'Площадь:' . $values['field26'] . ' м<sup>2</sup>';
        if ($values['field27'])
          $brief[] = 'Участок:' . $values['field27'] . ' соток';
        if ($values['field4'])
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

    $this->widgetSchema['region_id'] 
      = new sfWidgetFormDoctrineChoice(array('model' => 'Region', 'add_empty' => true, 'label' => '* Регион'));

    $this->widgetSchema['region_node']
      = new sfWidgetFormRegionnode(array(
        'source'  => 'form/regionnode',
        'choices' => array('' => 'Выберите регион'),
        'label'   => '* Метро/Район/Город'
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
      = new sfValidatorDoctrineChoiceMany(array('model' => 'Regionnode'));
    $this->validatorSchema['city_region']  = new sfValidatorString(array('required' => false));
    $this->validatorSchema['street']       = new sfValidatorString(array('required' => false));
    $this->validatorSchema['address']      = new sfValidatorPass();
    $this->validatorSchema['coords']       = new sfValidatorRegex(array('required' => false, 'pattern' => '/^\d+\.\d+,\d+\.\d+$/'));
  }

  private function loadAdditionalFields() {
    $this->widgetSchema['description']
      = new sfWidgetFormTextarea(array('label' => 'Описание объекта'), array('need_colspan' => true, 'max' => 500));

    $this->widgetSchema['thumb'] = new sfWidgetFormInputHidden();
    $this->widgetSchema['image']
      = new sfWidgetFormAjaxUpload(array(
        'url' => 'form/upload',
        'max' => 6,
        'label' => 'Фотографии'
      ), array('need_colspan' => true));

    if ($this->_show_contacts) {
      $this->loadContactFields();
    }

    $this->widgetSchema['agree'] = new sfWidgetFormInputCheckbox(array(
        'label' => '* Я ознакомлен и принимаю <a href="/terms" target="_blank">Условия
                    и правила обслуживания и размещения информации</a>',
        
      ), array('need_colspan' => true, 'checked' => true,));

    $this->widgetSchema->setHelp('description', 'Не более 500 символов');
    $this->widgetSchema->setHelp('image', 'До 6 изображений');

    $this->validatorSchema['description']  = new sfValidatorString(array('required' => false, 'max_length' => 1500));
    $this->validatorSchema['thumb']  = new sfValidatorPass();
    $this->validatorSchema['image']  = new sfValidatorPass();
    $this->validatorSchema['agree']  = new sfValidatorBoolean(array('required' => true));
  }

  private function loadContactFields() {
    $user = sfContext::getInstance()->getUser();
    if ($user->type == 'company'){
      $this->widgetSchema['organization_link']
        = new sfWidgetFormInputText(array('label' => 'Сcылка на страницу объекта на сайте компании'), array('need_colspan' => true));
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
    $q = Doctrine::getTable('FormItem')
      ->createQuery()
      ->innerJoin('Form.FormField')
      ->where('type = ?', $this->_type)
      ->orderBy('position');

    foreach ($q->execute() as $fieldinfo) {
      $field = $fieldinfo->FormField;
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
}
