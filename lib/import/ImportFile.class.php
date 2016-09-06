<?php

abstract class ImportFile
{
  const
    ERROR_CATEGORY        = 1,
    ERROR_TYPE            = 2,
    ERROR_REGION          = 3,
    ERROR_GEODATA         = 4,
    ERROR_CURRENCY        = 5,
    ERROR_LOCATION        = 6,
    ERROR_ADDRESS         = 7,
    ERROR_REQUIRED_FIELD  = 8,
    ERROR_READING_FILE    = 9,
    ERROR_INVALID_XML     = 10,
    ERROR_PRICE           = 11,
    ERROR_HUGE_DESC       = 12,
    ERROR_BAD_ENCODING    = 13,
    ERROR_UNPAID_TYPE     = 16,
    ERROR_IMPORT_LOG      = 17,

    XMLERROR_OPEN_CLOSE       = 14,
    XMLERROR_INVALID_VALUE    = 15,



    FORMAT_MAIN           = 1,

    FILE_TYPE_XML         = 'XML',
    FILE_TYPE_CSV         = 'CSV',
    FILE_TYPE_XLS         = 'XLS',

    TYPE_APARTAMENT_SALE_XML  = 1,
    TYPE_APARTAMENT_RENT_XML  = 3,
    TYPE_HOUSE_SALE_XML       = 5,
    TYPE_HOUSE_RENT_XML       = 7,
    TYPE_COMMERCIAL_SALE_XML  = 9,
    TYPE_COMMERCIAL_RENT_XML  = 11;

  public static $error_codes = array(
    self::ERROR_CATEGORY        =>  'Неправильно указан тип недвижимости',
    self::ERROR_TYPE            =>  'Неправильно указан раздел (покупка, продажа)',
    self::ERROR_REGION          =>  'Неправильно указан регион',
    self::ERROR_GEODATA         =>  'Не удалось получить координаты объекта недвижимости',
    self::ERROR_CURRENCY        =>  'Неправильно указана валюта',
    self::ERROR_LOCATION        =>  'Не указан город/населенный пункт',
    self::ERROR_ADDRESS         =>  'Не указан адрес объекта недвижимости',
    self::ERROR_REQUIRED_FIELD  =>  'Поле обязательно для заполнения',
    self::ERROR_READING_FILE    =>  'Не удалось получить содержимое файла',
    self::ERROR_INVALID_XML     =>  'Неправильный формат файла XML',
    self::ERROR_PRICE           =>  'Неправильно указана стоимость',
    self::ERROR_HUGE_DESC       =>  'Длина описания превышает лимит',
    self::ERROR_BAD_ENCODING    =>  'Неправильная кодировка файла',
    self::ERROR_UNPAID_TYPE     =>  'Загрузка файлов данного типа не оплачена',
    self::ERROR_IMPORT_LOG      =>  'Не удалось найти лог импорта',


    //Die XML-spezifisch Fehlermeldungen ;)
    self::XMLERROR_OPEN_CLOSE    => 'Лишний тег',
    self::XMLERROR_INVALID_VALUE => 'Некорректное значение',
  ),
  $types  = array(
    self::TYPE_APARTAMENT_SALE_XML  =>  'Продажа квартир (XML)',
    self::TYPE_APARTAMENT_RENT_XML  =>  'Аренда квартир (XML)',
    self::TYPE_HOUSE_SALE_XML       =>  'Продажа домов и участков (XML)',
    self::TYPE_HOUSE_RENT_XML       =>  'Аренда домов и участков (XML)',
    self::TYPE_COMMERCIAL_SALE_XML  =>  'Продажа коммерческой недвижимости (XML)',
    self::TYPE_COMMERCIAL_RENT_XML  =>  'Аренда коммерческой недвижимости (XML)',
  );

  protected static
    $log         = null,
    $_file_path  = null,
    $_file_name  = null,
    $_file_type  = null,
    $_user_id    = null,
    $_format     = self::FORMAT_MAIN;

  public function import($file = null, $format = null, $user_id = null, $file_name = null, $file_type = null)
  {
    if (null != $file && null != $format && null != $user_id && null != $file_name && null != $file_type) {
      self::$_file_path = $file;
      self::$_format    = $format;
      self::$_file_type = $file_type;
      self::$_user_id   = $user_id;
      self::$_file_name = $file_name;
      return self::load();
    }

    return false;
  }

  protected function load()
  {
    self::$log = new ImportLog();
    self::$log->file_name   = $this->_file_name;
    self::$log->file_type   = $this->_file_type;
    self::$log->created_at  = date('Y-m-d H:i:s');
    self::$log->user_id     = $this->_user_id;
    self::$log->save();

    var_dump($this->_file_name);

    switch ($this->_format) {
      case  self::FORMAT_MAIN:
        $lots_counter = $this->mesto();
        break;
      default:
        $lots_counter = $this->mesto();
    }

    if (is_int($lots_counter)) {
      self::$log->lots = $lots_counter;
    }
    else {
      self::$log->lots = 0;
    }
    self::$log->save();

    return self::$log->lots;
  }

  protected static function getRegionId($region_name)
  {
    $region_name = str_replace(
      array('область', 'обл.', 'край', 'АО', 'г.'),
      array('', '', '', '', '', ''),
      $region_name
    );

    $region_id = Doctrine_Query::create()
      ->select('r.id')
      ->from('Region r')
      ->where('r.name LIKE ?', '%' . trim($region_name) . '%')
      ->limit(1)
      ->fetchOne();

    return ($region_id && $region_name) ? $region_id->id : false;
  }

  protected static function prepareFormFieldValue($ff, $val)
  {
    switch ($ff->type) {
      case 'float':
        # UberHack for 4.016E+14
        $val = (float) preg_replace('/[^0-9.]/', '', $val);
        break;
      case 'integer':
        $val = (int) $val;
        break;
      case 'year':
        $val = ((int) $val > 1861) ? (int) $val : 0;
        break;
      case 'select':
        $val = trim((string) $val);
        $val = in_array($val, explode("\n", $ff['value'])) ? $val : 0;
        break;
      case 'price':
        if (is_object($val)) {
          $value = (int) $val->value;
          $currency = trim((string) $val->currency);
        }
        else {
          list($value, $currency) = explode(',', $val);
          $value = (int) trim($value);
          $currency = trim((string) $currency);
        }
        $currency = (!empty(Currency::$currencies[$currency])) ? $currency : 0;
        $val = ($value && $currency) ? $currency . $value : 0;
        break;
      case 'radio':
        $val = trim((string) $val);
        $val = in_array($val, explode("\n", $ff['value'])) ? $val : 0;
        break;
      case 'radiocombo':
        $val = trim((string) $val);
        break;
      case 'multiple':
        $values = explode(',', $val);
        $allowed = explode("\n", $ff['value']);
        foreach ($values as $key => $value)
          $values[$key] = in_array(trim($value), $allowed) ? trim($value) : null;
        $val = !empty($values) ? implode(', ', $values) : 0;
        break;
    }
    return $val;
  }

  protected static function createLot($data, $lot_info, $log_id = null, $not_paid = false)
  {
    $log_id = ($log_id !== null) ? $log_id : self::$log->id;
    $is_new = false;

    $lot = self::checkLot($data, $lot_info);
    
    if ($lot->isNew()) {
      $is_new = true;
    }

    if(!empty($lot->created_at) && !empty($data['created_at'])) {
      unset($data['created_at']);
    }

    $lot->fromArray($data);

    //Mark lot as imported
    $lot->imported = true;

    if (false !== strpos(self::$_file_path, 'http://')) {
      $lot->editable = false;
    } else {
      $lot->editable = true;
    }

    $brief = array('type' => $data['type']);
    foreach ($lot_info as $id => $param) {
      $brief["field$id"] = $param;
    }

    $lot->brief = @DynamicForm::makeBrief($brief);

    $lot->save(Doctrine_Manager::connection(), $not_paid);

    //save LotInfo
    self::addLotInfo($lot, $lot_info, $is_new);

    self::importLog($lot, $log_id);

    return $lot;
  }

  /**
   * Check existing lot for update
   * @param <type> $data
   * @return object Lot
   */
  protected static function checkLot($data = array(), $lot_info = array())
  {
    if (count($data) > 0) {
      if(!empty($data['internal_id'])) {
        $query = Doctrine::getTable('Lot')->createQuery('l')
          ->andWhere('l.type = ?', $data['type'])
          ->andWhere('l.user_id = ?', $data['user_id'])
          ->andWhere('l.internal_id = ?', $data['internal_id'])
          ->orderBy('active_till', 'desc');
        $lot = $query->limit(1)->fetchOne();
        if ($lot) {
          if($lot->status == 'restricted') $lot->status = 'moderate';
          return $lot;
        }
      }
    }
    
    return new Lot();
  }

  protected static function addLotInfo(Lot $lot, $lot_info, $is_new = false)
  {
    // clear collection for old lots
    if (!$is_new) {
      $lot->LotInfo->delete();
    }

    foreach ($lot_info as $field_id => $value) {
      $lot_info = new LotInfo();
      $lot_info->lot_id = $lot->id;
      $lot_info->field_id = $field_id;
      $lot_info->value = $value;
      $lot->LotInfo[] = $lot_info;

      $lot_info->save();
      $lot_info = null;
    }
    
    //ReSave Lot for raiting recount
    $lot->rating = 0;
    $lot->save();
  }

  public static function loadImages(Lot $lot, $image_urls) {
    $images_count = 0;
    $images = array();
    $source = sprintf('%s/%s/source', sfConfig::get('sf_web_dir'), sfConfig::get('app_upload_tmp_dir'));

    foreach ($image_urls as $image) {
      $image = trim($image);
      if ($images_count == 6) {
        break;
      }

      $filename = $lot->id . '_' . ($images_count + 1);
      //get image with curl
      $raw = Toolkit::curl_get_file_contents((string) $image);
      $image = "$source/$filename";
      if ($raw && file_put_contents($image, $raw, LOCK_EX)) {
        chmod($image, 0666);

        $ext = null;
        $info = getimagesize($image);
        if ($info['mime'] == 'image/gif') {
          $ext = '.gif';
        }
        elseif ($info['mime'] == 'image/jpeg') {
          $ext = '.jpg';
        }
        elseif ($info['mime'] == 'image/png') {
          $ext = '.png';
        }

        if (!$ext) {
          unlink($image);
          continue;
        }

        $filename = ($images_count + 1) . $ext;
        if (rename($image, $lot->full_image_path . '/' . $filename)) {
          chmod($lot->full_image_path . '/' . $filename, 0666);
          $images[] = $filename;
          $images_count++;
        }
      }
    }   

    if (count($images)) {
      $lot->images = $images;
      $lot->thumb = 1;
      $lot->save();
    }
  }

  public static function importErrorLog(ImportLog $log, $internal_lot_id, $message, $send_mail = false)
  {
    $log_error                  = new ImportErrorsLog();
    $log_error->log_id          = $log->id;
    $log_error->internal_lot_id = $internal_lot_id;
    $log_error->message         = $message;
    $log_error->save();

    if ($internal_lot_id === null && $log->user_id !== null && $send_mail) {
      $pm = new PMForm();
      $message = sprintf(
          "Файл %s не прошел проверку при последней загрузке в %s.\nПричина: %s.\n\n"
        . "Отчет об ошибках: http://mesto.ru/import/exportErrorsToCsv?id=%s",
          $log->file_name,
          date('H:i d.m.Y'),
          $log_error->message,
          $log_error->log_id
        );
      $data = array(
        'receiver' => $log->User->email,
        'priority' => 'high',
        'message'  => $message,
        'subject'  => 'Отчет о пакетной загрузке объявлений',
      );
      $pm->bind($data);

      if($pm->isValid()){
        sfGearmanProxy::doBackground('pm', array(
          'data'       => $data,
          'receivers'  => array(array('email' => $log->User->email)),
          'sender'     => sfConfig::get('app_feedback_user_id'),
          'send_pm'    => true,
          'send_email' => $log->User->getSettingsValue('send_email'),
        ));
      }

    }
  }

  protected static function importLog(Lot $lot, $log_id)
  {
    //Insert log to Region table
    $log_region = Doctrine_Query::create()
      ->from('ImportRegionsLog')
      ->where('log_id = ?', $log_id)
      ->andWhere('region_id = ?', $lot->region_id)
      ->limit(1)
      ->fetchOne();
    if ($log_region) {
      $log_region->lots = $log_region->lots + 1;
    }
    else {
      $log_region            = new ImportRegionsLog();
      $log_region->region_id = $lot->region_id;
      $log_region->log_id    = $log_id;
      $log_region->lots      = 1;
    }
    $log_region->save();

    //Insert log to Type table
    $log_type = Doctrine_Query::create()
      ->from('ImportTypesLog')
      ->where('log_id = ?', $log_id)
      ->andWhere('type = ?', Lot::$types[$lot->type])
      ->limit(1)
      ->fetchOne();

    if ($log_type) {
      $log_type->lots = $log_type->lots + 1;
    }
    else {
      $log_type         = new ImportTypesLog();
      $log_type->type   = Lot::$types[$lot->type];
      $log_type->log_id = $log_id;
      $log_type->lots   = 1;
    }
    $log_type->save();
  }

  public static function getErrorMessage ($code) {
    return self::$error_codes[$code];
  }
}
