<?php

class ExportLotToMitulaWorker extends sfGearmanWorker {

  public
    $name = 'export-lot-to-mitula',
    $methods = array(
      'export_lot_to_mitula'
    );

  protected function configure()
  {
    ini_set('memory_limit', '256M');
    $this->_configuration->loadHelpers('Domus');
  }

  public function doExportLotToMitula(GearmanJob $job) {
    $this->startJob();
    $data = unserialize($job->workload());

    $lot = Doctrine::getTable('Lot')->find($data['id']);
    if (!$lot) {
      return $this->completeJob($job);
    }
      
    $to_mitula = '<offer internal-id="' . $lot->id . '">';
    $to_mitula .= '<title><![CDATA['
      . preg_replace('/\,\s+$/', '', $lot->address1.', '.$lot->address2)
      . ']]></title>';
    switch ($lot->type):
      case 'house-sale':
        $v = $lot->getLotInfoField(64);
        if (strpos($v, 'дома') !== false) {
          $v = 'часть дома';
        } elseif(strpos($v, 'дом') !== false) {
          $v = 'дом';
      }
        $to_mitula .= '
          <type>продажа</type>
          <property-type>жилая</property-type>
          <category>' . $v. '</category>
          <price>
              <value>' . $lot->getPriceExchanged() . '</value>
              <currency>RUR</currency>
          </price>';
        break;
      case 'apartament-sale':
        if (strpos($lot->getLotInfoField(54), 'комната') !== false) {
          $category = 'комната';
        } else {
          $category = 'квартира';
      }
        $to_mitula .= '
          <type>продажа</type>
          <property-type>жилая</property-type>
          <category>' . $category . '</category>
          <price>
              <value>' . $lot->getPriceExchanged() . '</value>
              <currency>RUR</currency>
          </price>';
        break;
      case 'commercial-sale':
        $to_mitula .= '
          <type>продажа</type>
          <property-type>коммерческая</property-type>
          <category>' . $lot->getLotInfoField(45) . '</category>
          <price>
              <value>' . $lot->getPriceExchanged() . '</value>
              <currency>RUR</currency>
          </price>';
        break;
      case 'apartament-rent':
        $period = $lot->getLotInfoField(68);
        if (strpos($lot->getLotInfoField(55), 'комната') !== false) {
          $category = 'комната';
        } else {
          $category = 'квартира';
      }
        $to_mitula .= '
          <type>аренда</type>
          <property-type>жилая</property-type>
          <category>' . $category . '</category>
          <price>
              <value>' . $lot->getPriceExchanged() . '</value>
              <currency>RUR</currency>
              <period>' . $period . '</period>
          </price>';
        break;
      case 'house-rent':
        $to_mitula .= '
          <type>аренда</type>
          <property-type>жилая</property-type>
          <category>дом</category>
          <price>
              <value>' . $lot->getPriceExchanged() . '</value>
              <currency>RUR</currency>
              <period>месяц</period>
          </price>';
        break;
      case 'commercial-rent':
        $period = $lot->getLotInfoField(69);
        $to_mitula .= '
          <type>аренда</type>
          <property-type>коммерческая</property-type>
          <category>' . $lot->getLotInfoField(45) . '</category>
          <price>
              <value>' . $lot->getPriceExchanged() . '</value>
              <currency>RUR</currency>
              <period>' . $period . '</period>
          </price>';
        break;
    endswitch;

    $to_mitula .= '<url>http://' . $data['host'] .'/'.$lot->type . '/' . $lot->id . '</url>';

    //Подставляем рандомное^W нормальное(!) время обновления объявления на сайте
    $date_created_at = date('c', strtotime($lot->created_at));
    $date_last_update = date('c', strtotime($lot->updated_at));
    if ($date_last_update == $date_created_at) {
      $date_last_update = '';
    }

    $to_mitula .= '
        <creation-date>' . $date_created_at . '</creation-date>
        <last-update-date>' . $date_last_update . '</last-update-date>
        <expire-date>' . date('c', strtotime($lot->active_till)) . '</expire-date>
        <location>
          <country><![CDATA[Россия]]></country>';
    if (!array_key_exists($lot->region_id, array(77 => true, 78 => true))) {
      $to_mitula .= '<region><![CDATA[' . str_replace('г. ', '', $lot->Region->name) . ']]></region>';
    }
    if (!array_key_exists($lot->region_id, array(77 => true, 78 => true))) {
      if (!empty($lot->address_info['city_region'])) {
        $to_mitula .= '<locality-name><![CDATA[' . $lot->address_info['city_region'] . ']]></locality-name>';
      }
    }
    else {
      $to_mitula .= '<locality-name><![CDATA[' . str_replace('г. ', '', $lot->Region->name) . ']]></locality-name>';
    }
    $to_mitula .= '<address><![CDATA[' . $lot->address2 . ']]></address>';
    if ($lot->latitude && $lot->longitude) {
        $to_mitula .= '
          <latitude>' . $lot->latitude . '</latitude>
          <longitude>' . $lot->longitude . '</longitude>';
    }
    $to_mitula .= '</location>';

    $user = $lot->User;
    $to_mitula .= '
          <sales-agent>';
  if(!in_array($user->email, $data['sourses'])):
    if(!empty ($lot->parsed_at)){
      $to_mitula .= '<name></name>';
    }
    else{
      $to_mitula .= '<name><![CDATA[' . $user->name . ']]></name>';
    }
    if ($lot->organization_contact_phone)
      $to_mitula .= '<phone><![CDATA[' . $lot->organization_contact_phone . ']]></phone>';
    if (!$lot->User->is_inner)
      $to_mitula .= '<phone><![CDATA[' . $lot->User->phone . ']]></phone>';
    if ('owner' == $user->type)
      $category = 'владелец';
    else
      $category = 'агентство';
    $to_mitula .= '<category>' . $category . '</category>';
    $category = null;
    $organization = '';
    if ($user->is_inner):
      if ($lot->organization_contact_name):
        $organization .= $lot->organization_contact_name;
      endif;
    else:
      if ($user->company_name):
        $organization .= $user->company_name;
        if ($lot->organization_contact_name):
          $organization .= ' - ' . $lot->organization_contact_name;
        endif;
      endif;
    endif;
    $to_mitula .= '<organization><![CDATA[' . $organization . ']]></organization>';
  else:
    $to_mitula .= '<name><![CDATA[mesto.ru]]></name>';
    $to_mitula .= '<category><![CDATA[агентство]]></category>';
    $to_mitula .= '<phone><![CDATA[' . $lot->organization_contact_phone . ']]></phone>';
  endif;
    $organization = null;
    $user = null;
    $to_mitula .= '
        </sales-agent>';
    foreach (lot_images($lot) as $image):
      $to_mitula .= '<image>' . $image['big'] . '</image>';
    endforeach;
    $to_mitula .= '<description><![CDATA[' . $this->repairDescription($lot->full_description) . ']]></description>';

    $info = $lot->LotInfoArrayNoGroups;
    if (isset($info[1])):
      $to_mitula .= '<area>
          <value>' . preg_replace('/[^0-9.,]/', '', trim($info[1]['value'])) . '</value>
          <unit>' . preg_replace('/\.$/', '', trim($info[1]['help'])) . '</unit>
        </area>';
    endif;
    if (isset($info[27])):
      $to_mitula .= '<lot-area>
          <value>' . preg_replace('/[^0-9.,]/', '', trim($info[27]['value'])) . '</value>
          <unit>' . preg_replace('/\.$/', '', trim($$info[27]['help'])) . '</unit>
        </lot-area>';
    endif;
    if (isset($info[8])):
      $to_mitula .= '<kitchen-space>
          <value>' . preg_replace('/[^0-9.,]/', '', trim($info[8]['value'])) . '</value>
          <unit>' . preg_replace('/\.$/', '', trim($info[8]['help'])) . '</unit>
        </kitchen-space>';
    endif;
    if (isset($info[14]))
      $to_mitula .= '<renovation>' . $info[14]['value'] . '</renovation>';
    if (isset($info[64]) && 0)
      $to_mitula .= '<agent-fee>' . $info[64]['value'] . '</agent-fee>';
    if (isset($info[35])) {
      $to_mitula .= '<rooms>' . $info[35]['value'] . '</rooms>';
      $to_mitula .= '<rooms-offered>' . $info[35]['value'] . '</rooms-offered>';
    }
    
    if (isset($info[54])) {
      if($info[54]['value'] == 'комната') {
        $info[54]['value'] = 1;
      }
      $to_mitula .= '<rooms>' . substr($info[54]['value'], 0, 1) . '</rooms>';
      $to_mitula .= '<rooms-offered>' . substr($info[54]['value'], 0, 1) . '</rooms-offered>';
    }
    if (isset($info[55])) {
      if($info[55]['value'] == 'комната') {
        $info[55]['value'] = 1;
      }
      $to_mitula .= '<rooms>' . substr($info[55]['value'], 0, 1) . '</rooms>';
      $to_mitula .= '<rooms-offered>' . substr($info[55]['value'], 0, 1) . '</rooms-offered>';
    }
    if (isset($info[18]))
      $to_mitula .= '<room-furniture>' . $info[18]['value'] . '</room-furniture>';
    if (isset($info[19])):
      $to_mitula .= '<washing-machine>' . $info[19]['value'] . '</washing-machine>
        <refrigerator>' . $info[19]['value'] . '</refrigerator>';
    endif;
    if (isset($info[15]))
      $to_mitula .= '<balcony>' . $info[15]['value'] . '</balcony>';
    if (isset($info[11]))
      $to_mitula .= '<bathroom-unit>' . $info[11]['value'] . '</bathroom-unit>';
    if (isset($info[4]))
      $to_mitula .= '<floors-total>' . $info[4]['value'] . '</floors-total>';
    if (isset($info[3]))
      $to_mitula .= '<floor>' . $info[3]['value'] . '</floor>';
    if (isset($info[6]))
      $to_mitula .= '<building-type>' . $info[6]['value'] . '</building-type>';
    if (isset($info[30]) && 'нет' != $info[30]['value'])
      $to_mitula .= '<heating-supply>1</heating-supply>';
    if (isset($info[33]) && 'нет' != $info[33]['value'])
      $to_mitula .= '<water-supply>1</water-supply>';
    if (isset($info[34]) && 'нет' != $info[34]['value'])
      $to_mitula .= '<sewerage-supply>1</sewerage-supply>';
    if (isset($info[32]) && 'нет' != $info[32]['value'])
      $to_mitula .= '<electricity-supply>1</electricity-supply>';
    if (isset($info[31]) && 'нет' != $info[31]['value'])
      $to_mitula .= '<gas-supply>1</gas-supply>';
    
    $to_mitula      .= PHP_EOL . '</offer>';

    $to_mitula = preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $to_mitula);
    $to_mitula = preg_replace('/&#?[a-z0-9]+;?/i', '', $to_mitula);
    $to_mitula = str_replace('>&</', '></', $to_mitula); //Strange fix, but I don't know what a hell is going on.

    foreach ($data['files'] as $tmp_name => $file) {
      $file = fopen(sfConfig::get('sf_data_dir') . DIRECTORY_SEPARATOR . $tmp_name . '.xml', 'a');

      if ($tmp_name == 'spec') {
        $content = preg_replace(
          "/<url>*.*<\/url>/i",
          '<url>http://' . $data['host'] .'/lot/' . $lot->id . '/special</url>', $to_mitula);
      }

      flock($file, LOCK_EX);
      fwrite($file, (!empty($content)) ? $content : $to_mitula . PHP_EOL);
      fclose($file);
    }

    $info = null;
    $lot = null;

    return $this->completeJob($job);
  }

  protected function repairDescription($desc){
    $desc = preg_replace('/&#?[a-z0-9]+;?/i', '', $desc);
    $desc = preg_replace('/^\s?function.*}/', '', $desc);
    $desc = htmlspecialchars($desc);
    return trim($desc);
  }

}
