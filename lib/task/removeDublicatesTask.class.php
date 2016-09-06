<?php
/**
 * Remove dublicated lots from db
 *
 * @package    domus
 * @subpackage task
 */
class removeDublicatesTask extends sfGarinTask
{
  protected
    $log_file = null,
    $file_stream = null,
    $config = null,
    $parser_ids = array(10297, 5, 10295, 3, 12, 6409, 2, 4, 10296);

  protected function configure()
  {
    $this->namespace = 'domus';
    $this->name = 'remove-dublicates';
    $this->briefDescription = 'Remove dublicated lots from db';
    $this->detailedDescription = '';

    $this->log_file = sfConfig::get('sf_root_dir').'/dublicates.log';

    $this->addOption('start_price', null, sfCommandOption::PARAMETER_OPTIONAL, 'start price', null);
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    if (!$this->file_stream = fopen($this->log_file, 'a')) {
      throw new Exception('Can\'t create log file: '.$this->log_file);
    }

    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);

    $calcs = Doctrine::getTable('Lot')
            ->createQuery('l')
            ->select('MIN(l.price) as min')
            ->addSelect('MAX(l.price) as max')
            ->addSelect('COUNT(l.id) as count')
            ->where('l.status = ?', 'active')
            ->addWhere('l.deleted_at IS NULL')
            ->addWhere('l.brief <> \'\'')
            ->addWhere('l.organization_contact_phone IS NOT NULL')
            ->fetchArray();

    $start_price  = !is_null($options['start_price']) ? $options['start_price'] : $calcs[0]['min'];
    $increment    = 5000;
    $end_price    = $start_price + $increment;
    $middle_price = (($calcs[0]['max'] + $calcs[0]['min'])/2);
    $count_lots   = $calcs[0]['count'];
    $min_lots     = 250;
    $max_lots     = 5000;
    $tries        = 0;
    $handled_lots = 0;

    while ($start_price < $calcs[0]['max']) {
      $tries++;
      $end_price = $start_price + $increment;
      $this->progress(sprintf('Calculating... Prices between %d and %d', $start_price, $end_price));

      $query = Doctrine::getTable('Lot')
              ->createQuery('l')
              ->select('l.id, l.type, l.region_id, l.address1, l.address2, l.price, l.brief, l.organization_contact_phone, l.user_id')
              ->where('l.price BETWEEN ? AND ?', array($start_price, $end_price))
              ->addWhere('l.status = ?', 'active')
              ->addWhere('l.deleted_at IS NULL')
              ->addWhere('l.brief <> \'\'')
              ->addWhere('l.organization_contact_phone IS NOT NULL')
              ->orderBy('l.created_at DESC');
      $count = $query->count();

      if ($count > 0) {
        if ($count > $max_lots) {
          if ($tries > 3) {
            $percent = 50;
            $tries = 0;
          } else {
            $percent = ceil(($max_lots/$count)*100);
            if ($start_price > $middle_price) $percent -= $percent/2;
            else $percent += $percent/2;
          }

          $increment = ceil($increment - (($increment / 100) * (100-$percent)));
          continue;
        } elseif ($count < $min_lots && ($count_lots - $handled_lots) > $min_lots) {
          if ($tries < 3) {
            $percent = ceil(($min_lots/$count)*100);
            if ($start_price > $middle_price) $percent += $percent/2;
            else $percent -= $percent/2;

            $increment = ceil($increment + (($increment / 100) * ($percent-100)));
            continue;
          }
        } elseif (($count_lots - $handled_lots) <= $min_lots && ($start_price + $increment) != $calcs[0]['max']) {
          $increment = $calcs[0]['max'] - $start_price;
          continue;
        }

        $handled_lots += $count;
        $lots_array = $query->fetchArray();
        $this->removeDublicates($lots_array);
      }

      $start_price += $increment+1;
      $tries = 0;
    }

    fclose($this->file_stream);
  }

  private function removeDublicates(&$lots_array)
  {
    $similar_ids = array();

    foreach ($lots_array as $i => $lot) {
      $this->progress(sprintf('Comparing lots. Found %d dublicates', count($similar_ids)));
      preg_match('/^(?:Площадь|Участок):(\d+\.*\d*)/isu', $lot['brief'], $matches);
      if (empty($matches[1])) {
        unset($lots_array[$i]);
        continue;
      }
      $area1 = floatval($matches[1]);
      $phones1 = explode(',', $lot['organization_contact_phone']);
      $address1_1 = preg_replace('/[^a-zа-я-\d]/iu',' ', $lot['address1']);
      $address1_1 = trim(preg_replace('/\s+/', ' ', $address1_1));
      $address1_2 = preg_replace('/[^a-zа-я-\d]/iu',' ', $lot['address2']);
      $address1_2 = trim(preg_replace('/\s+/', ' ', $address1_2));

      $first = true;
      foreach ($lots_array as $k => $data) {
        if ($lot['id'] == $data['id']) continue;
        if ($lot['type'] != $data['type'] || $lot['region_id'] != $data['region_id'] || $lot['price'] != $data['price'])
          continue;

        preg_match('/^(?:Площадь|Участок):(\d+\.*\d*)/isu', $data['brief'], $matches);
        if (empty($matches[1])) {
          unset($lots_array[$k]);
          continue;
        }
        $area2 = floatval($matches[1]);
        if ($area1 != $area2) continue;

        $phones2 = explode(',', $data['organization_contact_phone']);
        $sim_phone = false;
        if (isset($phones1, $phones2)) {
          foreach ($phones1 as $phone1) {
            $phone1 = preg_replace('/[^\d]/', '', $phone1);
            foreach ($phones2 as $phone2) {
              $phone2 = preg_replace('/[^\d]/', '', $phone2);
              if ($phone1 == $phone2) {
                $sim_phone = true;
                break 2;
              }
            }
          }
        }
        if ($sim_phone === false) continue;
        $this->progress();


        $address2_1 = preg_replace('/[^a-zа-я-\d]/iu',' ', $data['address1']);
        $address2_1 = trim(preg_replace('/\s+/', ' ', $address2_1));
        $address2_2 = preg_replace('/[^a-zа-я-\d]/iu',' ', $data['address2']);
        $address2_2 = trim(preg_replace('/\s+/', ' ', $address2_2));

        similar_text($address1_1, $address2_1, $sim_addr1);
        similar_text($address1_2, $address2_2, $sim_addr2);

        if ($sim_addr1 > 99 && $sim_addr2 > 99) {
          $similar_ids[] = $this->selectParsed($lot, $data);

          if ($first) {
            $write_line = $lot['id']." -   ".$lot['address1'].', '.$lot['address2'].' - '.$lot['price'].' - '.$lot['organization_contact_phone']."\r\n\t".
                          $data['id'].' - '.$data['address1'].', '.$data['address2'].' - '.$data['price'].' - '.$data['organization_contact_phone']."\r\n";
          } else {
            $write_line = "\t".$data['id'].' - '.$data['address1'].', '.$data['address2'].' - '.$data['price'].' - '.$data['organization_contact_phone']."\r\n";
          }

          fwrite($this->file_stream, $write_line) or die('Can\'t write to log file: '.$this->log_file);
          unset($lots_array[$k]);
          $first = false;
        }
      }

      unset($lots_array[$i]);
    }

    $similar_ids = array_unique($similar_ids);

    if (count($similar_ids)) {
      $deleted = Doctrine_query::create()
              ->update('lot')
              ->set('deleted_at', '?', date("Y-m-d H:i:s"))
              ->set('status', '?', 'inactive')
              ->whereIn('id', $similar_ids)
              ->execute();

      if ($deleted != count($similar_ids)) print "wtf!?\r\n";
    }
  }

  private function selectParsed($lot, $data) {
    if (in_array($lot['user_id'], $this->parser_ids) && !in_array($data['user_id'], $this->parser_ids))
      return $lot['id'];
    else
      return $data['id'];
  }

  /**
   * Write current progress
   * @param string $text = null
   * @return void
   */
  private function progress($text = null) {
    if ($text) {
      $text = str_pad($text, 50);
    }
    $this->writeProgress($text);
  }
}