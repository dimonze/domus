<?php

class ExportToCsvTask extends sfBaseTask
{
  private $template = '"%type%","%region%","%district%","%street%","%metro%",%rooms%,%area%,%floor%,"%building_type%",%price%,"%url%"';
  private $step = 5000;

  public function configure() {

    $this->namespace        = 'domus';
    $this->name             = 'exporttocsv';
  }

  public function execute($arguments = array(), $options = array()) {

    $this->logSection('Start execute', 'Mem: ' . round(memory_get_usage()/1024/1024,3) . ' MiB');
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);

    $types = array_keys(Lot::$types);
    $conn = Doctrine::getConnectionByTableName('Lot');
    $dir = sfConfig::get('sf_upload_dir');
    $p_regions = $conn->execute('SELECT id, name FROM region')->fetchAll();
    $regions = array();
    foreach($p_regions as $row){
      $regions[$row['id']] = $row['name'];
    }
    unset($p_regions);
    
    foreach ($types as $type) {
      $this->logSection('Type', $type);
      $count = $conn->execute('SELECT COUNT(id) as cnt FROM lot WHERE type = ? AND status = ?', array($type, 'active'))->fetch();
      $count = $count['cnt'];
      $file = fopen($dir . '/' . $type . '.csv', 'w');
      
      for($i=0;$i<$count;$i+=$this->step){
        $res = $conn->execute('SELECT 
          id,address1,address2,region_id,price,exchange 
          FROM lot WHERE type = ? AND status = ? LIMIT ' . $i . ',' . $this->step, array($type, 'active'));


        $this->logSection('-- Step #', ($i/$this->step + 1) . '*' . $this->step);
        $this->logSection('-- After SELECT', 'Mem: ' . round(memory_get_usage()/1024/1024,3) . ' MiB');
        

        while($lot = $res->fetch()) {
          $params = array();
          $params['region'] = $regions[$lot['region_id']];
          $params['price'] = round($lot['price']  * $lot['exchange']);
          $params['url']   = 'http://www.mesto.ru/' . $type . '/' . $lot['id'];
          $params['street'] = $lot['address2'];

          $address = explode(',',$lot['address1']);
          if(!empty($address[1])){
            if(in_array($lot['region_id'],array(77,78))){
              if(preg_match('/\s?м\.\s?(.*)/',$address[1], $matches)){
                $params['metro'] = $matches[1];
              }
            }
            else{
              if(!preg_match('/\s?м\.\s?(.*)/',$address[1])){
                $params['district'] = $address[1];
              }
            }
          }


          switch($type){
            case 'house-sale':
            case 'house-rent':
              $params['type'] = 'дом';
            break;

            case 'commercial-sale':
            case 'commercial-rent':
              $params['type'] = 'офис';
            break;

            case 'apartament-sale':
            case 'apartament-rent':
            default:
              $params['type'] = 'квартира';
            break;
          }

          $fetched = $conn->
            execute('SELECT field_id, value FROM lot_info WHERE lot_id = ?',array((int)$lot['id']))->
            fetchAll();
          unset($lot);

          foreach($fetched as $row){
            switch($row['field_id']){
              case 1:
              case 26:
              case 46:
                $params['area'] = (int)$row['value'];
              break;

              case 3:
                $params['floor'] = (int)$row['value'];
              break;

              case 6:
              case 28:
              case 49:
                $params['building_type'] = $row['value'];
              break;

              case 54:
              case 55:
              case 35:
                if($row['value']=='комната'){
                  $params['type'] = 'комната';
                  $params['rooms'] = 1;
                }
                else{
                  $params['rooms'] = preg_replace('/\D/','',$row['value']);
                }
              break;

              case 45:
                $params['type'] = $row['value'];
              break;
            }
          }
          unset($fetched);
          fwrite($file,$this->fillTemplate($params) . PHP_EOL);
          unset($params);
        }
      }
      fclose($file);
      $this->logSection('-- After fclose',  'Mem: ' . round(memory_get_usage()/1024/1024,3) . ' MiB');
      $this->logSection('-- Url:', 'http://www.mesto.ru/uploads/' . $type . '.csv');
    }
  }

  private function fillTemplate($data){
    $result = $this->template;
    foreach($data as $key=>$value){
      $result = str_replace('%'.$key.'%', trim($value), $result);
    }
    if(strpos($result,'%')!==false){
      $result = preg_replace('/%[^W]*?%/','',$result);
    }
    $result = str_replace('""','',$result);
    return $result;
  }
}
?>
