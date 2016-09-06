<?php

class ParseYandexWorker extends sfGearmanWorker
{
  public
    $name = 'parse_yandex',
    $methods = array('parse_yandex_lot');

  protected function configure()
  {
    sfContext::createInstance($this->_configuration);
  }

  public function doParseYandexLot(GearmanJob $job)
  {
    $this->startJob();
    $params = unserialize($job->workload());
    $parser = new Parser_Yandex($params['type'], $params['region_id']);

    if ($data = $parser->parseItem($params['item'])) {
      if (!$parser->isEnoughData($data)) {
        $error = 'not enough data';
      }
      elseif (!$parser->isUnique($data)) {
        $error = 'duplicate';
      }

      if (empty($error)) {
        $id = $parser->import($data);
      }
    }

    if (!empty($id)) {
      $this->completeJob($job, sprintf('%d:%s', $id, $data['url']));
    }
    elseif (isset($error) && isset($data['url'])) {
      $this->completeJob($job, sprintf('%s:%s', $error, $data['url']));
    }
    elseif (isset($data['url']) && empty($error)){
      $this->completeJob($job, sprintf('%s:%s', 'import', $data['url']));
    }
    else {
      $this->completeJob($job);
    }
  }
}