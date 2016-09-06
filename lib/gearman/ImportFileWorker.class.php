<?php
class ImportFileWorker extends sfGearmanWorker
{
  public
    $name = 'import-file',
    $methods = array('import_file');

  protected function configure()
  {
    ini_set('memory_limit', '4096M');
    ini_set('max_execution_time', 0);
  }

  public function doImportFile (GearmanJob $job) {
    echo __METHOD__. " Lets go!\n";
    $this->startJob();
    if(substr($job->workload(),1,1) != ':' || !is_array($data = unserialize($job->workload()))) {
      //Source
      $id = $job->workload();
      echo __METHOD__ . " id=" . $id ."\n";
      $data = Doctrine::getTable('UserSourcesLink')->find($id);
      if($data == null){
        echo __METHOD__ . " Get outta here.\n";
        return $this->completeJob();
      }
      $data = $data->toArray();
      $data['path'] = sfConfig::get('sf_cache_dir') . '/import/' . $id;
      $data['file_name'] = $data['url'];
    }
    
    $this->log = new ImportLog();
    $this->log->file_name   = $data['file_name'];
    $this->log->file_type   = $data['file_type'];
    $this->log->created_at  = date('Y-m-d H:i:s');
    $this->log->user_id     = $data['user_id'];
    $this->log->save();

    if ($data['file_type'] % 2) {

      $types = array_keys(Lot::$types);
      $type = $types[floor(($data['file_type']-1)/2)]; // Magic!
      $user = Doctrine::getTable('User')->find($data['user_id']);
      
      if($user->group_id == UserGroup::PARTNERS_ID) {
        $this->not_paid = false;
        $this->spec_active_till = null;
      }
      else {
        $status = Doctrine::getTable('ImportOrder')->createQuery('i')
          ->select('i.id, i.date_to, o.status')
          ->leftJoin('i.Options o on i.id = o.order_id')
          ->andWhere('o.type = ?', $type)
          ->andWhere('i.date_from <= ?', date('Y-m-d H:i:s'))
          ->andWhere('i.date_to > ?', date('Y-m-d H:i:s'))
          ->andWhere('i.user_id = ?', $data['user_id'])
          ->andWhere('o.status = ?', 'active')
          ->limit(1)
          ->fetchOne(array(), Doctrine::HYDRATE_ARRAY);
        
        $this->not_paid         = !empty($status) ? false : true;
        $this->spec_active_till = !empty($status['date_to']) ? $status['date_to'] : null;
      }
      

      $validator = sfConfig::get('sf_root_dir') . '/data/xml-validation/' . $type . '.rng.xml';

      libxml_clear_errors();
      libxml_use_internal_errors(true);
      $dom = new DOMDocument('1.0', 'UTF-8');
      $dom->load($data['path'], LIBXML_NOBLANKS |  LIBXML_NOCDATA);

      if(!empty($id)){
        $file = Doctrine::getTable('UserSourcesLink')->find($id);
      }

      echo "\n\n#" . (!empty($id) ? $id : $data['file_name']) . " Let's go.\n";

      $f = fopen($data['path'],'r');
      $encodingTest = fgets($f, 1024);
      fclose($f);

      $validation = $dom->relaxNGValidate($validator);

      if(!$validation) {
        $errors = libxml_get_errors();
        $xml_errors = array();
        $fatal_errors = 0;
        $excess_tags  = 0;
        echo '#' . (!empty($id) ? $id : $data['file_name']) . " there is some problems. Check for dumb errors.\n";
        print_r($errors);
        foreach($errors as $key=>$error){
          $message = null;
          
          if(preg_match('/Element .*? has extra content: .*?/i',$error->message)) {
            unset($errors[$key]);
            $excess_tags++;
            continue;
          }
          
          
          switch ($error->code) {
            
            case 3:
              preg_match("/Type\s([a-z\-]+)\sdoesn't\sallow\svalue\s(.*?)\s+/i", $error->message, $out);
              $message = 'Тип данных %s не допускает значения %s. строка %s';
              $message = sprintf($message, $out[1], $out[2], $error->line);
            break;
            
            case 5:
              $message = 'Неожиданный конец документа. строка %s';
              $message = sprintf($message, $error->line);
              $fatal_errors++;
            break;
            
            case 11: //Wrong sequence. Bypass.
              unset($errors[$key]);
              continue;
            break;
          
            case 12: //  
              preg_match('/Extra\selement\s([a-z\-]+)\sin/i', $error->message, $out);
              $last_message = '12|' . $out[1];
              echo '#' . $last_message . "\n";
              unset($errors[$key]);
              continue; // Go to "case 25"
            break;
          
            case 22:
              preg_match('/Expecting\san\selement\s([a-z\-]+),/i', $error->message,$out);
              $last_message = '22|' . $out[1];
              if($error->line != 0){
                $last_message .= '|' . $error->line;
              }
              echo '#' . $last_message . "\n";
              unset($errors[$key]);
              continue; // Go to "case 25"
            break;
            
            case 24:
              preg_match('/Element\s([a-z\-]+)\sfailed/i', $error->message, $out);
              $message = "Некорректные атрибуты элемента %s. строка %s";
              $message = sprintf($message, $out[1], $error->line);
            break;
            
            case 25:
              $msg[12] =
              $msg[32] = "Элемент %s некорректен. строка %s";
              $msg[22] = "Элемент %s отсутствует. строка %s";
              $last_message = explode('|', $last_message);
              if($last_message[0] == 32){
                preg_match('/Element\s([a-z\-]+)\sfailed/i', $error->message, $out);
                $last_message[1] = $out[1];
              }
              $message = sprintf($msg[$last_message[0]], $last_message[1], !empty($last_message[2]) ? $last_message[2] : $error->line);
              $last_message = null;
              if(in_array($out[1], array('type', 'category', 'full-price', 'offer-type', 'commercial-type'))){
                $fatal_errors++;
              }
            break;
            
            case 31: // Go next
            case 32:
              $last_message = '32|0';
              echo '#' . $last_message . "\n";
              unset($errors[$key]);
              continue; // Go to "case 25"
            break;
          
            case 73:
              $expect = str_replace('expected ', '', trim($error->message));
              $message = 'Ожидалось %s. строка %s, символ %s';
              $message = sprintf($message, $expect, $error->line, $error->column);
              $fatal_errors++;
            break;
          
            case 76:
              preg_match('/:\s([a-z\-]+)\sline\s(\d+)\sand\s([a-z\-]+)/i', $error->message, $out);
              $message = '%s %s или %s в строках %s или %s';
              $message = sprintf($message,
                      ImportFile::getErrorMessage(ImportFile::XMLERROR_OPEN_CLOSE),
                      $out[1],
                      $out[3],
                      $out[2],
                      $error->line                    
              );
              $fatal_errors++;
            break;
          }

          if(!empty($message)){
            echo "#" . $message ."\n";
            $xml_errors[] = $message;
            unset($errors[$key]);
          }
        }
        
        $xml_errors = array_unique($xml_errors);
        print_r($xml_errors);
        foreach($xml_errors as $message){
          ImportFile::importErrorLog($this->log, null, $message);
        }
        
        if(count($errors) == 0 && $fatal_errors == 0){
          $validation = true;
          echo '#' . $fatal_errors . "\n";
          echo '#' . (!empty($id) ? $id : $data['file_name']) . " Almost good: " . $excess_tags . " excess tags.\n";
        }
      }

      if(preg_match('/<\?xml.*?windows.*?\?>/i',$encodingTest)) {
        if(!empty($id)){
          $file->status = UserSourcesLink::STATUS_BANNED;
          $file->save();
        }
        echo '#' . (!empty($id) ? $id : $data['file_name']) . " banned. Reason: encoding.\n";
        ImportFile::importErrorLog(
          $this->log,
          null,
          ImportFile::getErrorMessage(ImportFile::ERROR_BAD_ENCODING)
        );

      }
      elseif(!$validation) {
        if(!empty($id)){
          $file->status = UserSourcesLink::STATUS_BANNED;
          $file->save();
          echo $dom->relaxNGValidate($validator);
        }
        if(count($xml_errors) == 0) {
          ImportFile::importErrorLog(
            $this->log,
            null,
            ImportFile::getErrorMessage(ImportFile::ERROR_INVALID_XML),
            true
          );
        }
          $err_txt = print_r($errors,1);
          fwrite(STDERR,$err_txt);
          echo '#' . (!empty($id) ? $id : $data['file_name']) . " banned. Reason: invalid file. " . count($errors) . " errors. Validator: " . $validator . "\n";
      }
      else {
        if(!empty($id)){
          $file->status = $this->not_paid ? UserSourcesLink::STATUS_NOT_PAID : UserSourcesLink::STATUS_ACTIVE;
          $file->save();
          echo "#" . $id;
        }
        echo '#' . (!empty($id) ? $id : $data['file_name']) ." active: Good file. Validator: " . $validator . "\n";
        $nodes = $dom->getElementsByTagName('offer');

        $this->log->lots = $nodes->length;
        $this->log->save();

        for ($i=0; $i<$nodes->length; $i++) {
          sfGearmanProxy::doBackground('import_lot', array(
            'lot'        => $dom->saveXML($nodes->item($i)),
            'user_id'    => $data['user_id'],
            'type'       => $type,
            'log'        => $this->log->id,
            'not_paid'   => $this->not_paid,
            'spec_active_till' => $this->spec_active_till,
          ));
        }
      }
    }
    else {
      ImportFile::importErrorLog($this->log, null, 'Допустим только формат XML.');
      echo __METHOD__ . " XML ONLY!\n";
    }

    echo '# ' . date("Y-m-d H:i:s") . "\n";
    if (file_exists($data['path'])) {
      //unlink($data['path']);
    }
    echo __METHOD__. " That's all!\n";

    return $this->completeJob();
  }
}
