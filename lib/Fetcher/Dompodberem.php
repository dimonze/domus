<?php

/**
 * Class for fetching http://dompodberem.ru/ lots
 *
 * @author Grechko Aleksey
 */
class Fetcher_Dompodberem extends BaseFetcher
{

  protected function importLot($url)
  {
    try {
      $html = $this->fetch($url, array(
        'strip_comments'        => true,
        'strip_html'            => true,
        'strip_html_options'    => array('script'),
        'only_body'             => false,
        'use_proxy'             => $this->limit > 10,
      ));
    }
    catch (Exception $e) {
      printf('%s%s', $e->getMessage(), PHP_EOL);
      return false;
    }

    $this->progress();
    $data = $this->lot_options;
    $data['parsed_at'] = date('Y-m-d H:i:s');
    $data['organization_link'] = $url;
    $data['currency'] = 'RUR';
    $data['exchange'] = 1;
    $data['photos'] = array();
    if (!isset($data['params'])) {
      $data['params'] = array();
    }

    $this->parser->load($html);
    $container = $this->parser->getElementById('dle-content');

    if (!is_object($container)) {
      ParseLogger::writeError($url, 'Some HTML shit happened...');
      return false;
    }

    if ($fragment = $container->find('div.objectCard', 0)->find('ul', 0)) {
      foreach ($fragment->find('li') as $item) {
        if ($item->children(0)) {
          preg_match('/<[^>]+>([^<]+)<[^>]+>(.+)$/', $item->children(0)->innertext, $matches);
          if (!empty($matches[1]) && !empty($matches[2])) {
            $param_key = strip_tags($matches[1]);
            $param_val = strip_tags($matches[2]);
            list($param_val) = explode(',', $param_val);
            if (in_array($param_key, array('Расстояние от МКАД', 'Шоссе', 'Район'))) {
              $data['title'][$param_key] = trim($param_val);
            }
            else {
              $data['params'][$param_key] = trim($param_val);
            }
          }
        }
      }
    }
    if (empty($data['title'])) {
      ParseLogger::writeError($url, ParseLogger::EMPTY_ADDRESS);
      return false;
    }
    if (!empty($data['title']['Расстояние от МКАД'])) {
      $data['params']['Расстояние от МКАД'] = $data['title']['Расстояние от МКАД'];
    }


    if ($fragment = $container->find('div.withDots', 0)) {
      if ($item = $fragment->find('h1', 0)) {
        preg_match('/(["«].+["»](?:\s*\(.+\))*)/', $item->innertext, $matches);
        if (!empty($matches[1])) {
          $data['params']['Название посёлка'] = $matches[1];
        }
      }
    }

    if ($container->find('div.vtorich', 0)) {
      $data['params']['Вторичный рынок'] = 'да';
    }

    if ($fragment = $container->find('div.sliderkit-panel')) {
      foreach ($fragment as $item) {
        if ($item->find('img', 0)) {
          $data['photos'][] = 'http://dompodberem.ru'.$item->find('img', 0)->src;
        }
      }
    }

    if ($fragment = $container->find('div.objectDescription', 0)) {
      $data['description'] = $fragment->plaintext;
    }

    if ($fragment = $container->find('script')) {
      foreach ($fragment as $item) {
        preg_match('/"lng"\:"*([\d\.]+)"*,"lat"\:"*([\d\.]+)"*/', $item->innertext, $matches);
        if (!empty($matches[1]) && !empty($matches[2])) {
          $data['longitude'] = $matches[2];
          $data['latitude']  = $matches[1];
          break;
        }
      }
    }


    ParseTools::preg_clear_cache();
    if (is_object($fragment)) $fragment->clear();
    $container->clear();
    $this->parser->clear();
    unset($item, $fragment, $container, $html);

    $this->progress();

    $data = $this->parseLotData($data);

    return $data;
  }


  /**
   * Fix lot additional params
   * @param array $params
   * @return array $params
   */
  protected function parseLotParams(array $params)
  {
    $checked = array();
    foreach ($params as $key => &$value) {
      $this->progress();

      if (empty($value) || in_array($key, array(
          'Площадь поселка',
        ))) {
        continue;
      }

      switch ($key) {
        case 'Площадь участка, сот.':
          $keys = array('Мин. площадь участка', 'Макс. площадь участка');
          break;

        case 'Цена участков, руб.':
          $keys = array('Мин. цена участка', 'Макс. цена участка');
          break;

        case 'Площадь таунхаусов, м2':
          $keys = array('Мин. площадь таунхауса', 'Макс. площадь таунхауса');
          break;

        case 'Цена таунхаусов, руб.':
          $keys = array('Мин. цена таунхауса', 'Макс. цена таунхауса');
          break;

        case 'Площадь домов, м2':
          $keys = array('Мин. площадь дома', 'Макс. площадь дома');
          break;

        case 'Цена домов, руб.':
          $keys = array('Мин. цена дома', 'Макс. цена дома');
          break;
      }

      if (isset($keys)) {
        $parts = preg_split('/-|до/', $value, 2);
        if (count($parts) == 1) {
          $checked[$keys[0]] = trim(str_replace(',', '.', preg_replace('/[^\d.,]/', '', $parts[0])), '.');
          if (mb_strpos($keys[0], 'цена') !== false) {
            $checked[$keys[0]] = 'RUR'.$checked[$keys[0]];
          }
        }
        elseif (count($parts) == 2) {
          $checked[$keys[0]] = trim(str_replace(',', '.', preg_replace('/[^\d.,]/', '', $parts[0])), '.');
          $checked[$keys[1]] = trim(str_replace(',', '.', preg_replace('/[^\d.,]/', '', $parts[1])), '.');
          if (mb_strpos($keys[0], 'цена') !== false) {
            $checked[$keys[0]] = 'RUR'.$checked[$keys[0]];
            $checked[$keys[1]] = 'RUR'.$checked[$keys[1]];
          }
        }
        unset($keys);
        continue;
      }


      if (is_string($value)) {
        $value = preg_replace('/^\s+|\s+$/', '', $value);
      }

      $checked[$key] = $value;
    }

    return $checked;
  }


  /**
   * Parse address row
   * @param string $value
   * @return array $address
   */
  protected function parseLotAddress($value)
  {
    $data = array(
      'address1' => '',
      'address2' => '',
    );

    $data['address1'] = ParseTools::getRegionName($this->lot_options['region_id']);

    if (!empty($value['Район'])) {
      $data['address1'] .= sprintf(', %s район', $value['Район']);
    }
    if (!empty($value['Шоссе'])) {
      $data['address1'] = sprintf(', %s шоссе', $value['Шоссе']);
    }

    if (!empty($value['Расстояние от МКАД'])) {
      $data['address2'] = $value['Расстояние от МКАД'];
    }

    return $data;
  }


  /**
   * Extract lot and page links
   * @param string $html
   * @return void|false
   */
  protected function extractLinks($html)
  {
    $this->parser->load($html);
    $container = $this->parser->getElementById('dle-content');
    if (!$container) return false;

    // first find lot links
    foreach ($container->find('div.ym-grid') as $item) {
      $this->progress();
      if ($a = $item->find('h5', 0)->find('a', 0)) {
        $link = 'http://dompodberem.ru'.$a->href;
        if (!$this->appendLotLink($link)) {
          return false;
        }
      }
      $item->clear();
    }

    // ... after extract page locations
    if ($paginator = $this->parser->find('div.pager', 0)) {
      foreach ($paginator->find('a') as $item) {
        $this->progress();
        if (ctype_digit($item->innertext) && $item->innertext > 1 && !empty($item->href) && $item->href != '#') {
          $link = 'http://dompodberem.ru'.str_replace('&amp;', '&', $item->href);
          if (!isset($this->pages[$item->innertext])) {
            $this->pages[$item->innertext] = $link;
          }
        }

        $item->clear();
      }
    }

    $this->parser->clear();
    unset($a, $item, $paginator, $container, $html);

    return true;
  }


  protected function translateParamValue($value, $field_id = null)
  {
    return $value;
  }
}