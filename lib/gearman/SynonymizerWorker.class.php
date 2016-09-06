<?php

class SynonymizerWorker extends sfGearmanWorker
{
  public
    $name = 'synonymizer',
    $methods = array('synonymize_description');

  protected function configure()
  {
    $this->_configuration->loadHelpers('Word');
  }

  public function doSynonymizeDescription(GearmanJob $job)
  {
    $this->startJob();
    $lot = Doctrine::getTable('Lot')->find($job->workload());

    if (!$lot) {
      return $this->completeJob($job, 'not found');
    }

    $formats = array(
      'apartament-sale' => array(
        'fmt' => '<54> <001> <1> <3> <4> <5>. <6>. <7>, <8>, <9>. <10>, <11>, <12>, <13>. <14>. <15>. <20>',
        'beg' => $this->select('Продается', 'Есть на продажу', 'Предлагается'),
      ),
      'apartament-rent' => array(
        'fmt' => '<55> <001> <1> <3> <4> <5>. <6>. <7>, <8>, <9>. <10>, <11>, <12>. <15>. <21>',
        'beg' => $this->select('Сдается', 'Аренда', 'Снять в аренду'),
      ),
      'house-sale' => array(
        'fmt' => '<64>. <26>, <27>. <30>, <31>, <32>, <33>, <34>. <28>, <29>, <35>, <4>. <36>, <37>. <38>, <39>, <40>, <41>. <42>. <67>, <43>. <44>. <22>',
        'beg' => ''
      ),
      'house-rent' => array(
        'fmt' => '<26>, <27>, <5>. <28>. <61>. <60>, <56>, <57>, <58>, <59>. <18>, <19>. <35>, <4>. <36>, <37>. <43>. <44>. ',
        'beg' => $this->select('Сдаю дом', 'Дом в аренду', 'Аренда дома', 'Сдам дом в аренду', 'Предлагается дом в аренду', 'Дом в лизинг'),
      ),
      'new_building-sale' => array(
        'fmt' => "<002>, <76>. <6>, <4>, <74>. <075>.<003>",
      )
    );

    $lot_info = $lot->lot_info_array_no_groups;

    if (!isset($formats[$lot->type])) {
      return $this->complete($job, $lot->id, null);
    }

    $data = $formats[$lot->type];
    $text = trim(sprintf('%s %s', $data['beg'], $data['fmt']));

    foreach (preg_split('/\D+/', $data['fmt'], null, PREG_SPLIT_NO_EMPTY) as $field_id) {
      foreach (array(str_replace(array('-', '_'), '', $lot->type), '') as $suffix) {
        $method = 'field' . $field_id . $suffix;
        if (is_callable(array($this, $method))) {
          break;
        }
        else {
          $method = false;
        }
      }

      if($method && substr($field_id, 0, 1) === '0') {
        $str = $this->$method($lot);
      }
      elseif (empty($lot_info[$field_id]) || !$method) {
        $str = '';
      }
      else {
        $str = $this->$method($lot_info[$field_id]['value'], $lot_info[$field_id]['help']);
      }

      $text = str_replace("<$field_id>", $str, $text);
    }

    $text = preg_replace('/(([.,!?] )+)/', '\\2', $text);
    $text = preg_replace('/\s+([.,!?])/', '\\1', $text);
    $text = trim($text);

    //Постобработка только для домов
    if($lot->type == 'house-sale' || $lot->type == 'house-rent') {
      $text = $this->fixAutoDescription($text);
    }

    $lot_id = $lot->id;
    $lot->free(true);
    return $this->complete($job, $lot_id, $text);
  }

  protected function complete($job, $id, $text)
  {
    $conn = Doctrine::getTable('Lot')->getConnection();

    $stmt = $conn->prepare('UPDATE `lot` SET `auto_description` = ? where id = ?');
    $stmt->execute(array($text, $id));
    $stmt->closeCursor();

    return $this->completeJob($job, $id . ':' . $text);
  }


  protected function select()
  {
    $items = func_get_args();
    if (1 == count($items)) {
      if (is_numeric($items[0])) {
        return mt_rand(0, $items[0]);
      }
      elseif(is_string($items[0])) {
        return $items[0];
      }
      elseif (is_array($items[0])) {
        $items = $items[0];
      }
      else {
        throw new Exception('Unknown argument');
      }
    }

    return $items[mt_rand(0, count($items) - 1)];
  }

  protected function inflect($word, $case)
  {
    return WordInflector::get($word, $case);
  }

  protected function num2text($num)
  {
    return Toolkit::num2str($num, false, true);
  }

  protected function num2pretext($num)
  {
    if (1 === $num) {
      return 'одно';
    }
    else {
      return $this->inflect($this->num2text($num), WordInflector::TYPE_GENITIVE);
    }
  }

  protected function sqm($num, $text = null)
  {
    $num = (int) $num;

    switch ($this->select(0, 1, 2)) {
      case 0:
        $value = sprintf('%d кв. метр%s', $num, ending($num, '', 'а', 'ов'));
        break;
      case 1:
        $value = sprintf('%d квадратн%s метр%s', $num, ending($num, 'ый', 'ых'), ending($num, '', 'а', 'ов'));
        break;
      case 2:
        $value = sprintf('%d метр%s', $num, ending($num, '', 'а', 'ов'));
        break;
    }

    if ($text) {
      return $text . ' ' . $value;
    }
    else {
      return $value;
    }
  }

  protected function hec($num, $text = null)
  {
    $num = (int) $num;

    switch ($this->select(1)) {
      case 0:
        $value = sprintf('%d сот%s', $num, ending($num, 'ка', 'ки', 'ок'));
        break;
      case 1:
        $value = sprintf('%d сот.', $num);
        break;
    }

    if ($text) {
      return $text . ' ' . $value;
    }
    else {
      return $value;
    }
  }

  protected function gender($value, $gender)
  {
    switch ($gender) {
      case 2:
        break;

      case 3:
        $value = str_replace('ый', 'ое', $value);
        break;
    }

    return $value;
  }


  protected function field001($lot)
  {
    $metros = array();
    foreach($lot->getRegionnode(true) as $node) {
      if ($node->is_metro) {
        $metros[] = $node->name;
      }
    }
    return count($metros) ? sprintf('метро %s', implode(', ', $metros)) : '';
  }

  protected function field002NewBuildingSale($lot)
  {
    switch ($lot->region_id) {
      case 77:
        return $this->select('Москва', 'В городе');

      case 50:
        return $this->select('Московская область', 'За МКАДом', 'Недалеко от Москвы');
    }
  }

  protected function field003NewBuildingSale($lot)
  {
    $values = array();
    foreach ($lot->Flats as $flat) {
      $values[] = sprintf('%s %s. %s, %s. %s %s',
        $this->ucfirst_utf8($this->field54($flat->rooms)),
        $this->field1($flat->common_space),
        $this->field7($flat->living_space),
        $this->field8($flat->kitchen_space),
        $flat->has_balcony ? $this->field15('балкон') . '.' : '',
        $flat->has_loggia ? $this->field15('лоджия') . '.' : ''
      );
    }

    if ($values) {
       array_unshift($values, 'Квартиры в доме:');
       return implode("\n", array_filter($values));
    }
  }


  protected function field54($value)
  {
    switch ($value) {
      case 'комната':
        return $this->select('комната', 'отдельная комната', 'комната в квартире');
      case 'квартира со свободной планировкой':
      case 'своб. планировка':
        return $this->select('квартира со свободной планировкой', 'квартира с произвольной планировкой');

      default:
        $formats = array('%d-комнатная квартира', 'квартира с %d комнат%s', '%sкомнатная квартира');
        $num = (int) mb_substr($value, 0, 2);
        switch ($this->select(2)) {
          case 0:
            return sprintf($formats[0], $num);
          case 1:
            return sprintf($formats[1], $num, ending($num, 'ой', 'ами'));
          case 2:
            return sprintf($formats[2], $this->num2pretext($num));
        }
    }
  }

  protected function field55($value)
  {
    return $this->field54($value);
  }

  protected function field1($value)
  {
    $text = $this->select('площадью', 'общей площадью', 'с площадью', 'метраж квартиры');
    return $this->sqm($value, $text);
  }

  protected function field3($value)
  {
    return sprintf('на %d этаже', $value);
  }

  protected function field4($value)
  {
    $formats = array(
      '%s-этажный дом',
      '%d этаж' . ending($value, '', 'а', 'ей'),
      'в %d этаж'  . ending($value, '', 'а', 'ей')
    );

    $rand = $this->select(2);
    if(!$rand) {
      $value = $this->num2pretext($value);
    }

    return sprintf($formats[$rand], $value);
  }

  protected function field4ApartamentSale($value)
  {
    $formats = array('%d этажного дома', '%sэтажного дома');
    switch ($this->select(1)) {
      case 0:
        return sprintf($formats[0], $value);
      case 1:
        return sprintf($formats[1], $this->num2pretext($value));
    }
  }

  protected function field4ApartamentRent($value)
  {
    return $this->field4ApartamentSale($value);
  }

  protected function field5($value)
  {
    return sprintf(
      $this->select('%d года постройки', 'построенного в %d году', 'возведенного в %d году'),
      $value
    );
  }

  protected function field5HouseRent($value)
  {
    $value = $this->field5($value);
    $value = preg_replace(array('#построенного#u', '#возведенного#u'), array('построен', 'возведен'), $value);
    return $value;
  }

  protected function field6($value)
  {
    switch ($value) {
      case 'Кирпичный':
        return $this->select('Дом кирпичный', 'Дом из кирпича');

      case 'Дерево':
        return $this->select('Дом деревянный', 'Дом из дерева');

      default:
        $formats = array('Дом %s', 'Здание %s');
        $value = mb_strtolower($value);
        switch ($this->select(1)) {
          case 0:
            return sprintf($formats[0], $value);
          case 1:
            return sprintf($formats[1], $this->gender($value, 3));
        }
    }
  }

  protected function field7($value)
  {
    $text = $this->select(
      'Жилая площадь квартиры', 'Жил. площадь квартиры', 'Жилая площадь', 'Жил. площадь'
    );
    return $this->sqm($value, $text);
  }

  protected function field8($value)
  {
    return $this->sqm($value, $this->select('площадь кухни', 'площадь кухни составляет'));
  }

  protected function field9($value)
  {
    return sprintf('высота потолков %.2f метр%s', $value, ending((int) $value, '', 'а', 'ов'));
  }

  protected function field10($value)
  {
    switch ($value) {
      case 'изолированные':
        $text = $this->select('изолированные', 'изолированы', 'отдельные', 'несмежные');
        break;

      case 'смежные':
        $text = $this->select('смежные', 'проходные', 'неизолированные');
        break;

      case 'смежно-изолированная':
        $text = $this->select('смежно-изолированные', 'смежно-раздельные');
        break;
    }

    if (!empty($text)) {
      return sprintf('Комнаты %s', $text);
    }
  }

  protected function field11($value)
  {
    switch ($value) {
      case 'совмещенный':
        return $this->select(
          'санузел совмещенный', 'с/у совмещенный', 'туалет и ванная совмещены',
          'туалет совмещен с ванной'
        );

      case 'раздельный':
        return $this->select(
          'санузел раздельный', 'с/у раздельный', 'туалет и ванная отдельно',
          'туалет отдельно от ванной', 'отдельные туалет и ванная'
        );

      default:
        $formats = array(
          0 => 'в квартире %d с/у',
          1 => 'в квартире несколько (%d) с/у',
          2 => 'в квартире несколько (%d) санузлов',
          3 => 'в квартире %d сануз%s',
          4 => 'в квартире несколько (%s) санузлов',
          5 => 'в квартире %s сануз%s',
          6 => 'в квартире %s с/у',
          7 => 'в квартире несколько (%s) с/у',
        );

        $num = (int) mb_substr($value, 0, 1);
        switch ($i = $this->select(7)) {
          case 0:
          case 1:
          case 2:
            return sprintf($formats[$i], $num);

          case 3:
            return sprintf($formats[$i], $num, ending($num, 'ел', 'ла', 'лов'));

          case 4:
          case 5:
          case 6:
          case 7:
            return sprintf($formats[$i], $this->num2text($num), ending($num, 'ел', 'ла', 'лов'));
        }
    }
  }

  protected function field12($value)
  {
    switch ($value) {
      case 'деревянные':
        $text = $this->select('деревянные', 'из дерева', 'с деревянной рамой');
        break;

      case 'деревянные стеклопакет':
        $text = $this->select('деревянный стеклопакет', 'стеклопакет из дерева');
        break;

      case 'пластиковые стеклопакет':
        $text = $this->select('пластиковые', 'пластиковый стеклопакет', 'ПВХ', 'ПВХ стеклопакет');
        break;

      case 'алюминиевые стеклопакет':
        $text = $this->select('алюминиевые', 'алюминиевый стеклопакет', 'стеклопакет из алюминия');
        break;

      case 'дерево-алюм. стеклопакет':
        $text = $this->select(
          'дерево-алюминиевые', 'алюминиево-деревянные', 'из дерева и алюминия',
          'дерево-алюминиевый стеклопакет', 'алюминиево-деревянный стеклопакет'
        );
        break;
    }

    if (!empty($text)) {
      return sprintf('окна %s', $text);
    }
  }

  protected function field13($value)
  {
    switch ($value) {
      case 'дерево':
        $text = $this->select('из дерева', 'деревянные');
        break;

      case 'железобетон':
        $text = $this->select('из железобетона', 'железобетонные', 'из ж/б', 'ж/б');
        break;

      case 'монолитные':
        $text = $this->select('монолитные', 'монолитного типа');
        break;

      case 'смешанные':
        $text = $this->select('смешанные', 'смешанного типа');
        break;
    }

    if (!empty($text)) {
      return sprintf('перекрытия %s', $text);
    }
  }

  protected function field14($value)
  {
    switch ($value) {
      case 'евроремонт':
        $text = $this->select('сделан евроремонт', 'сделан европейский ремонт');
        break;

      case 'в отличном состоянии':
        $text = $this->select(
          'сделан хороший ремонт', 'сделан отличный ремонт', 'сделан качественный ремонт'
        );
        break;

      case 'после косметического ремонта':
        $text = $this->select(
          'сделан косметический ремонт', 'сделан поверхностный ремонт', 'сделана отделка',
          'выполнен косметический ремонт', 'выполнен поверхностный ремонт', 'выполнена отделка'
        );
        break;

      case 'требует косметического ремонта':
        $text = $this->select(
          'нужен косметический ремонт', 'нужен поверхностный ремонт',
          'требуется косметический ремонт', 'требуется поверхностный ремонт',
          'требуется отделка', 'нужна отделка', 'требует косметического ремонта'
        );
        break;

      case 'требует кап ремонта':
        $text = $this->select(
          'не сделан ремонт', 'нет ремонта', 'нужен капитальный ремонт',
          'требуется капитальный ремонт', 'необходимо сделать ремонт', 'требуется ремонт'
        );
        break;
    }

    if (!empty($text)) {
      return sprintf('В квартире %s', $text);
    }
  }

  protected function field15($value)
  {
    return 'нет' == $value ? '' : sprintf('Есть %s', $value);
  }

  protected function field20($value)
  {
    $value = explode(', ', $value);
    $groups = array(
      'В квартире'   => array(
        'телефон'    => array('проведен телефон', 'установлен телефон'),
        'интернет'   => array('проведен интернет', 'подключен интернет'),
        'меблировка' => array('есть мебель', 'имеется мебель', 'меблировка'),
      ),
      'В подъезде' => array(
        'домофон'       => array('установлен домофон', 'есть домофон', 'на двери домофон'),
        'кодовый замок' => array('установлен кодовый замок', 'есть кодовый замок', 'на двери кодовый замок'),
        'лифт'          => array('есть лифт', 'имеется лифт', 'лифт в наличии'),
        'мусоропровод'  => array('есть мусоропровод', 'имеется мусоропровод', 'мусоропровод в наличии'),
        'консьерж'      => array(
          'есть консьерж', 'сидит консьерж', 'дежурит консьерж', 'работает консьерж',
          'есть вахтер', 'сидит вахтер', 'дежурит вахтер', 'работает вахтер'
        ),
      ),
      'Территория' => array(
        'огороженная территория' => array(
          'огорожена', 'с ограждением', 'с забором', 'за забором', 'за ограждением'
        ),
        'охрана'                 => array('охраняется', 'с охраной', 'под охраной'),
      ),
      'На территории' => array(
        'подземная автостоянка' => array(
          'подземная автостоянка', 'подземная стоянка', 'подземный гараж'
        ),
      ),
    );

    $text = '';
    foreach ($groups as $start => $fields) {
      $sentence = array();
      foreach ($fields as $field_name => $field_texts) {
        if (in_array($field_name, $value)) {
          $sentence[] = call_user_func_array(array($this, 'select'), $field_texts);
        }
      }
      if (count($sentence)) {
        $text .= sprintf('%s %s. ', $start, implode(', ', $sentence));
      }
    }

    return $text;
  }

  protected function field21($value)
  {
    return $this->field20($value);
  }


  protected function field64($value)
  {
    if(empty($value)) {
      return '';
    }

    $ending =  $gending = $variation = '';
    $begs = array('Продается%s %s', 'Выставлен%s на продажу %s', 'Продаю%s %s', 'Есть в продаже%s %s', 'Продам%s %s');

    $lrand = $this->select(1);
    $rand = $this->select(4);
    $beg = $begs[$rand];

    switch ($value){
      case 'дача':
        $variations = array('дач%s', '%sдачный участок');
        //Пляшем, если "дача"
        if($lrand == 0) {
          $ending = 'а'; //По умолачанию "дача"

          //Проверка начала предложения
          switch ($rand) {
            case 1:
              $gending = 'а'; //ВыставленА
              break;

            case 2:
            case 4:
              $ending = 'у'; //Прода(ю|м) дачУ
              break;
          }
        }

        $variation = sprintf($variations[$lrand], $ending);
        break;

      case 'коттедж/дом':
        $variation = $this->select('коттедж', 'благоустроенный дом');
        break;

      case '1/2 дома':
        $variations = array('1/2 дома', 'полдома');
        //ВыставленА на продажу 1/2 дома
        if($lrand == 0 && $rand == 1) {
          $gending = 'а';
        }
        //ВыставленО на продажу полдома
        if($rand == 1 && $lrand == 1) {
          $gending = 'о';
        }

        $variation = $variations[$lrand];
        break;

      case '1/3 дома':
        $ending = 'а';
        $variations = array('1/3 дом%s', 'одн%s треть дома');
        //ВыставленА на продажу 1/3 дома
        if($rand == 1){
          $gending = 'а';
        }
        //Продаю/продам на продажу однУ треть дома
        if($lrand == 1 && in_array($rand, array(2,4))) {
          $ending = 'у';
        }

        $variation = sprintf($variations[$lrand], $ending);
        break;

      case '2/3 дома':
        $variations = array('2/3 дома', 'две трети дома');
        //ВыставленЫ на продажу 2/3 дома
        if($rand == 1){
          $gending = 'ы';
        }

        $variation = $variations[$lrand];
        break;

      case '1/4 дома':
        $ending = 'а';
        $variations = array('1/4 дом%s', 'одн%s четверть дома');
        //ВыставленА на продажу 1/4 дома
        if($rand == 1){
          $gending = 'а';
        }
        //Продаю/продам на продажу однУ треть дома
        if($lrand == 1 && in_array($rand, array(2,4))) {
          $ending = 'у';
        }

        $variation = sprintf($variations[$lrand], $ending);
        break;

      case 'таунхаус':
        $variation = $this->select('таунхаус', 'одноквартирный дом');
        break;

      case 'особняк':
        $variation = $this->select('особняк', 'комфортабельный дом');
        break;

      case 'участок':
        $variation = $this->select('участок', 'земельный участок');
        break;
    }

    return sprintf($beg, $gending, $variation);
  }

  protected function field26($value)
  {
    $index = $this->select(5);
    $options = array(
      'Площадью', null, 'Общей площадью', 'С площадью', 'Занимаемая площадь'
    );

    return $this->sqm($value, $options[$index]);
  }

  protected function field27($value)
  {
    $index = $this->select(3);
    $options = array('площадь участка', 'участок в', null, 'участок площадью', 'площадь участка земли', 'занимаемая площадь');

    return $this->hec($value, $options[$index]);
  }

  protected function field35($value)
  {
    $n = intval($value);
    $plural_one = $n%10==1&&$n%100!=11?'комната':($n%10>=2&&$n%10<=4&&($n%100<10||$n%100>=20)?'комнаты':'комнат');

    return sprintf($this->select(
      '%d ' . $plural_one, 'с %d комнатами', '%d-комнатный'
    ), $value);
  }

  protected function field30($value)
  {
    $result = '';

    switch ($value){
      case 'централизованное':
        $result = $this->select('отопление централизованное', 'централизованное отопление предоставлено', 'имеется централизованное отопление', 'есть централизованное отопление');
        break;

      case 'газовое':
        $result = $this->select('отопление газовое', 'газовое отопление', 'предоставлено газовое отопление', 'есть газовое отопление');
        break;

      case 'электрическое':
        $result = $this->select('электрическое отопление', 'отопление электрическое', 'с электрическим отоплением', 'имеется электрическое отопление');
        break;

      case 'печь или камин':
        $result = $this->select('печное отопление', 'отопление печью', 'с печным отоплением');
        break;

      case 'да':
      case 'есть':
        $result = $this->select('с отоплением', 'есть отопление', 'проведено тепло', 'проведено отопление', 'имеется отопление');
        break;

      default:
        $result = $this->select('отопления нет', 'отопление отсутствует', 'отопление не проведено');
    }

    return $this->ucfirst_utf8($result);
  }

  protected function field31($value)
  {
    switch ($value){
      case 'магистральный':
        return $this->select('газ магистральный', 'с магистральным газом', 'предоставлен магистральный газ');
        break;

      case 'балоны':
        return $this->select('газ в баллонах', 'газ из баллонов');
        break;

      case 'по границе':
        return $this->select('газ по границе', 'имеется газ по границе', 'есть газ по границе');
        break;

      case 'перспектива':
        return $this->select('газ в перспективе', 'газ в проекте');
        break;

      case 'да':
        return $this->select('газ', 'есть газ', 'проведен газ', 'с газом', 'имеется газ');
        break;

      default:
        return $this->select('газа нет', 'газ отсутствует', 'без газа');
    }
  }

  protected function field32($value)
  {
    switch ($value){
      case 'да':
      case 'есть':
        return $this->select('с электричеством', 'есть электричество', 'проведено электричество', 'имеется электричество');
        break;

      case 'перспектива':
        return $this->select('электричество в перспективе', 'электричество в проекте');
        break;

      default:
        return $this->select('электричества нет', 'электричество не проведено', 'электричество отсутствует', 'без электричества');
    }
  }

  protected function field33($value)
  {
    switch ($value){
      case 'центральный':
        return $this->select('водопровод центральный', 'с центральным водопроводом', 'имеется центральный водопровод', 'есть центральный водопровод');
        break;

      case 'скважина':
        return $this->select('скважина', 'имеется скважина', 'есть скважина');
        break;

      case 'колодец':
        return $this->select('колодец', 'имеется колодец', 'есть колодец');
        break;

      case 'перспектива':
        return $this->select('водопровод в перспективе', 'водопровод в проекте');
        break;

      case 'по границе':
        return $this->select('водопровод по границе', 'есть водопровод по границе', 'имеется водопровод по границе');
        break;

      case 'да':
      case 'есть':
        return $this->select('водопровод', 'водопровод есть', 'имеется водопровод', 'присутствует водопровод');
        break;

      default:
        return $this->select('водопровода нет', 'без водопровода', 'отсутствует водопровод');
    }
  }

  protected function field34($value)
  {
    switch ($value){
      case 'центральная':
        return $this->select('канализация центральная', 'с центральной канализацией', 'имеется центральная канализация');
        break;

      case 'очистные сооружения':
        return $this->select('очистные сооружения', 'имеются очистные сооружения', 'есть очистные сооружения');
        break;

      case 'аэротенк':
        return $this->select('аэротенк', 'есть аэротенк', 'имеется аэротенк', 'с аэротенком');
        break;

      case 'септик':
        return $this->select('септик', 'с септиком', 'есть септик', 'имеется септик');
        break;

      case 'да':
      case 'есть':
        return $this->select('канализация', 'канализация есть', 'присутствует канализация', 'имеется канализация');
        break;

      default:
        return $this->select('канализации нет', 'канализация отсутствует', 'без канализации');
    }
  }

  protected function field28($value)
  {
    switch ($value){
      case 'Кирпичный':
        return $this->select('Дом кирпичный', 'Дом из кирпича');
        break;

      case 'Блочный':
        return $this->select('Дом блочный', 'Дом из блоков', 'Моноблочный дом');
        break;

      case 'Панельный':
        return $this->select('Дом панельный', 'Панельный дом');
        break;

      case 'Дерево':
        return $this->select('Дом деревянный', 'Дом из дерева');
        break;

      case 'Панельно-блочный':
        return $this->select('Дом панельно-блочный', 'Дом из панелей и блоков');
        break;

      case 'Монолитно-кирпичный':
        return $this->select('Дом монолитно-кирпичный', 'Кирпично-монолитный дом');
        break;

      default:
        return '';
    }
  }

  protected function field29($value)
  {
    switch ($value){
      case 'дом недостроен':
        return $this->select('дом недостроен', 'недостроенный дом', 'требует достройки');
        break;

      case 'требует капитального ремонта':
        return $this->select('требует капитального ремонта', 'нужен капитальный ремонт', 'требуется капитальный ремонт');
        break;

      case 'требует косметического ремонта':
        return $this->select('требуется косметический ремонт', 'нужен косметический ремонт', 'нужен поверхностный ремонт', 'требуется поверхностный ремонт');
        break;

      case 'после косметического ремонта':
        return $this->select('после косметического ремонта', 'выполнен косметический ремонт', 'сделан косметический ремонт', 'не требует косметического ремонта');
        break;

      case 'в отличном состоянии':
        return $this->select('в отличном состоянии', 'в хорошем состоянии', 'в идеальном состоянии');
        break;

      default:
        return '';
    }
  }

  protected function field36($value)
  {
    $options = array('Удаленность от города %s км', 'В %d км от города', 'Находится на расстоянии %d км от города');
    $index = $this->select(count($options) - 1);

    return sprintf($options[$index], $value);
  }

  protected function field37($value)
  {
    switch ($value){
      case 'город':
        return $this->select('в городе', 'находится в городе', 'располагается в городе');
        break;

      case 'коттеджный поселок':
        return $this->select('в коттеджном поселке', 'находится в коттеджном поселке', 'располагается в коттеджном поселке');
        break;

      case 'поселок':
        return $this->select('в поселке', 'располагается в поселке', 'находится в поселке');
        break;

      case 'село':
        return $this->select('в селе', 'находится в селе', 'располагается в селе');
        break;

      case 'деревня':
        return $this->select('в деревне', 'находится в деревне', 'располагается в деревне');
        break;

      case 'садовое товарищество':
        return $this->select('садовое товарищество', 'садоводческое товарищество', 'садоводческое некоммерческое товарищество');
        break;

      default:
        return '';
    }
  }

  protected function field38($value)
  {
    $result = '';

    switch ($value){
      case 'блочный':
        $result = $this->select('блочный фундамент', 'блочный фундамент', 'фундамент из блоков');
        break;

      case 'ленточно-блочный':
        $result = $this->select('фундамент ленточно-блочный', 'фундамент ленточный монолитный');
        break;

      case 'ленточно-монолитный':
        $result = $this->select('ленточно-монолитный фундамент', 'с ленточно-монолитным фундаментом');
        break;

      case 'ленточный':
        $result = $this->select('фундамент ленточный', 'с ленточным фундаментом');
        break;

      case 'ленточный монолитный пояс':
        $result = $this->select('фундамент - ленточный монолитный пояс', 'из ленточно-монолитного пояса');
        break;

      case 'монолит':
        $result = $this->select('фундамент – монолит', 'из монолита');
        break;

      case 'сборно-монолитный':
        $result = $this->select('сборно-монолитный фундамент', 'из сборно-монолитного фундамента');
        break;

      case 'свайный':
        $result = $this->select('фундамент свайный', 'фундамент из свай', 'сделан из свай');
        break;

      case 'свайный-ростверковый':
        $result = $this->select('свайный-ростверковый фундамент', 'cвайный фундамент с ростверком');
        break;
    }

    return $this->ucfirst_utf8($result);
  }

  private function ucfirst_utf8($word) {
    return mb_strtoupper(mb_substr($word, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr(mb_convert_case($word, MB_CASE_LOWER, 'UTF-8'), 1, mb_strlen($word), 'UTF-8');
  }

  protected function field39($value)
  {
    $result = '';

    switch ($value){
      case 'блоки':
        $result = $this->select('стены из блоков', 'блочные стены');
        break;

      case 'бревно + кирпич':
        $result = $this->select('стены - бревно + кирпич', 'стены из бревен и кирпича');
        break;

      case 'брус':
        $result = $this->select('стены из бруса', 'брусочные стены', 'стены сделаны из бруса');
        break;

      case 'брус+вагонка':
        $result = $this->select('стены - брус + вагонка', 'стены сделаны из бруса и вагонки');
        break;

      case 'деревянные':
        $result = $this->select('стены деревянные', 'стены из дерева', 'стены сделаны из дерева');
        break;

      case 'каменные':
        $result = $this->select('стены каменные', 'каменные стены', 'стены из камня', 'стены сделаны из камня');
        break;

      case 'кирпич + брус':
        $result = $this->select('стены - кирпич + брус', 'стены из бруса и кирпича', 'стены сделаны из бруса и кирпича');
        break;

      case 'кирпич или пенополистерольный блок':
        $result = $this->select('стены - кирпич или пенополистерольный блок', 'стены из кирпича или пенополистерольного блока');
        break;

      case 'кирпичные':
        $result = $this->select('кирпичные стены', 'стены из кирпича', 'стены сделаны из кирпича');
        break;

      case 'клееный брус':
        $result = $this->select('стены - клеенный брус', 'стены из клееного бруса', 'стены сделаны из клееного бруса');
        break;

      case 'оцилиндрованное бревно':
        $result = $this->select('стены - оцилиндрованное бревно', 'сделаны из оцилиндрованного бревна');
        break;

      case 'пенобетон':
        $result = $this->select('стены – пенобетон', 'стены сделаны из пенобетона', 'стены из пеноблока');
        break;

      case 'пеноблоки':
        $result = $this->select('стены – пеноблоки', 'стены из пеноблока', 'сделаны из пеноблока');
        break;

      case 'пеноблоки и кирпич':
        $result = $this->select('стены - пеноблоки и кирпич', 'стены из пеноблока и кирпича', 'стены сделаны из пеноблока и кирпича');
        break;

      case 'профилированный брус':
        $result = $this->select('стены - профилированный брус', 'стены из профилированного бруса', 'стены сделаны из профилированного бруса');
        break;

      case 'сендвич':
        $result = $this->select('стены – сендвич', 'стены из сендвич-панелей', 'стены сделаны из сендвич-панелей');
        break;
    }

    return $result;
  }

  protected function field40($value)
  {
    $result = '';

    switch ($value){
      case 'Гранит':
        $result = $this->select('фасад из гранита', 'гранитный фасад', 'фасад выполнен из гранита');
        break;
      case 'Дерево':
        $result = $this->select('фасад из дерева', 'деревянный фасад', 'фасад выполнен из дерева');
        break;
      case 'Индивидуальная':
        $result = $this->select('фасад индивидуальный', 'фасад отдельный', 'фасад личный');
        break;
      case 'Кирпич':
        $result = $this->select('фасад из кирпича', 'кирпичный фасад', 'фасад выполнен из кирпича');
        break;
      case 'Натуральный камень':
        $result = $this->select('фасад из натурального камня', 'фасад выполнен из натурального камня');
        break;
      case 'Облицовочный камень':
        $result = $this->select('фасад - облицовочный камень', 'фасад из облицовочного камня', 'фасад выполнен из облицовочного камня');
        break;
      case 'Облицовочный кирпич':
        $result = $this->select('фасад - облицовочный кирпич', 'фасад из облицовочного кирпича', 'фасад выполнен из облицовочного кирпича');
        break;
      case 'Плитка':
        $result = $this->select('фасад – плитка', 'плиточный фасад', 'фасад выполнен из плитки');
        break;
      case 'Сайдинг':
        $result = $this->select('фасад – сайдинг', 'фасад из сайдинга', 'фасад выполнен из сайдинга');
        break;
      case 'Стекло и бетон':
        $result = $this->select('фасад - стекло и бетон', 'фасад из стекла и бетона', 'фасад выполнен из стекла и бетона');
        break;
      case 'Фахверк':
        $result = $this->select('фасад – фахверк', 'фахверковая конструкция');
        break;
      case 'Штукатурка и покраска':
        $result = $this->select('фасад - штукатурка и покраска', 'фасад оштукатурен и покрашен');
        break;
    }

    return $result;
  }

  protected function field41($value)
  {
    $result = '';

    switch ($value){
      case 'дерево':
        $result = $this->select('перекрытия деревянные', 'перекрытия из дерева', 'перекрытия сделаны из дерева');
        break;
      case 'железобетон':
        $result = $this->select('перекрытия – железобетон', 'перекрытия из ж/б', 'ж/б перекрытия', 'перекрытия из железобетона');
        break;
      case 'монолит':
        $result = $this->select('перекрытия – монолит', 'монолитные перекрытия');
        break;
      case 'смешанные':
        $result = $this->select('перекрытия смешанные', 'перекрытия смешанного типа');
        break;
    }

    return $result;
  }

  protected function field42($value)
  {
    $result = '';

    switch ($value){
      case 'возможна установка мебели':
        $result = $this->select('возможна установка мебели', 'допустима установка мебели');
        break;
      case 'полностью меблирован':
        $result = $this->select('полностью меблирован', 'не требует мебели', 'мебель не требуется');
        break;
      case 'современная мебель':
        $result = $this->select('современная мебель', 'модная мебель', 'стильная мебель');
        break;
      case 'частично меблирован':
        $result = $this->select('частично меблирован', 'не до конца меблирован');
        break;
    }

    return $this->ucfirst_utf8($result);
  }

  protected function field67($value)
  {
    $result = '';

    switch ($value){
      case 'индивидуальное жилищное строительство (ИЖС)':
      $result = $this->select('земли под индивидуальное жилищное строительство (ИЖС)', 'земли для индивидуального жилищного строительства', 'земли для ИЖС');
      break;
    case 'садоводство (ДНП, ДНТ)':
      $result = $this->select('земли под садоводство (ДНП, ДПТ)', 'земли для садоводства', 'земли для разведения сада');
      break;
    case 'личное подсобное хозяйство (ЛПХ)':
      $result = $this->select('назначение земель - личное подсобное хозяйство (ЛПХ)', 'земли под личное подсобное хозяйство');
      break;
    case 'фермерское хозяйство':
      $result = $this->select('назначение земель - фермерское хозяйство', 'земли для фермерского хозяйства');
      break;
    case 'другое':
      $result = 'назначение земли не определено';
      break;
    }

    return $this->ucfirst_utf8($result);
  }

  protected function field43($value)
  {
    $result = '';

    switch ($value){
      case 'Береговая линия':
        $result = $this->select('на береговой линии', 'находится на линии берега');
        break;
      case 'Газон':
        $result = $this->select('ландшафт участка – газон', 'представлен в виде газона');
        break;
      case 'Ландшафтный дизайн':
        $result = $this->select('ландшафтный дизайн', 'имеется ландшафтный дизайн', 'выполнен ландшафтный дизайн');
        break;
      case 'Лес по границе участка':
        $result = $this->select('лес по границе участка', 'участок ограничен лесом');
        break;
      case 'Озеленение на территории и по границам поселка':
        $result = $this->select('озеленение на территории и по границам поселка', 'выполнено озеленение на территории и по границам поселка');
        break;
      case 'Опушка леса':
        $result = $this->select('на опушке леса', 'находится на опушке леса', 'располагается на опушке леса');
        break;
      case 'Парковый ландшафт':
        $result = $this->select('парковый ландшафт', 'выполнен парковый ландшафт');
        break;
      case 'Поле':
        $result = $this->select('ландшафт участка – поле', 'располагается в поле', 'находится в поле');
        break;
      case 'Сад':
        $result = $this->select('ландшафт участка – сад');
        break;
      case 'Смешанный лес':
        $result = $this->select('ландшафт участка - смешанный лес', 'лес из смешанных деревьев');
        break;
      case 'Сосновый лес':
        $result = $this->select('ландшафт участка - сосновый лес', 'лес из сосен');
        break;
      case 'Хвойный лес':
        $result = $this->select('ландшафт участка - хвойный лес', 'лес из хвойных деревьев');
        break;
    }

    return $result;
  }

  protected function field44($value)
  {
    $result = '';
    $value = explode(', ', $value);

    $opts = array(
      'автомобильный навес' => array('автомобильный навес', 'навес для машины', 'есть навес для авто', 'имеется навес для авто'),
      'баня' => array('баня', 'имеется баня', 'с баней', 'есть баня'),
      'барбекю' => array('барбекю', 'место для барбекю', 'есть место под барбекю'),
      'бассейн' => array('бассейн', 'бассейн на территории', 'есть бассейн на территории'),
      'беседка' => array('беседка', 'с беседкой', 'имеется беседка', 'есть беседка'),
      'бильярд' => array('бильярд', 'с местом для бильярда'),
      'гараж' => array('гараж', 'имеется гараж', 'с гаражом', 'есть гараж'),
      'гостевой домик' => array('гостевой домик', 'имеется гостевой домик', 'с гостевым домиком', 'есть гостевой домик'),
      'дом охраны' => array('дом охраны', 'с домом охраны', 'имеется дом для охраны', 'есть дом охраны'),
      'дом прислуги' => array('дом прислуги', 'есть дом для прислуги', 'имеется дом для прислуги', 'с домом для прислуги'),
      'сауна' => array('сауна', 'с сауной', 'имеется сауна', 'есть сауна'),
      'теннисный корт' => array('теннисный корт', 'с теннисным кортом', 'имеется теннисный корт', 'есть теннисный корт'),
      'теплица' => array('теплица', 'есть теплица', 'имеется теплица', 'с теплицей'),
      'хозблок' => array('хозблок', 'есть хозблок', 'имеется хозблок', 'с хозблоком')
    );

    foreach ($value as $opt) {
      $result .= $this->ucfirst_utf8($this->select($opts[$opt])) . '. ';
    }
    $result = strlen($result) ? substr($result, 0, -2) : '';

    return $result;
  }

  protected function field22($value)
  {
    $result = '';
    $value = explode(', ', $value);

    $opts = array(
      'телефон' => array('телефон', 'проведен телефон', 'установлен телефон', 'есть подключенный телефон'),
      'лес' => array('лес', 'рядом лес', 'лес близко'),
      'интернет' => array('интернет', 'есть интернет', 'проведен интернет', 'подключен интернет'),
      'водоем' => array('водоем', 'озеро', 'пруд'),
      'охрана' => array('охрана', 'охраняемая территория', 'есть охрана', 'охраняется', 'под охраной'),
      'пмж' => array('пмж')
    );

    foreach ($value as $opt) {
      $result .= $this->ucfirst_utf8($this->select($opts[$opt])) . '. ';
    }
    return $result;
  }

  protected function field60($value)
  {
    return $this->field30($value);
  }

  protected function field61($value)
  {
    $result = '';

    switch ($value){
      case 'евроремонт':
        $result = $this->select('с евроремонтом', 'имеется евроремонт', 'сделан европейский ремонт');
        break;
      case 'в отличном состоянии':
        $result = $this->select('в отличном состоянии', 'в хорошем состоянии', 'в идеальном состоянии');
        break;
      case 'после косметического ремонта':
        $result = $this->select('после косметического ремонта', 'выполнен косметический ремонт', 'сделан поверхностный ремонт');
        break;
    }

    return $this->ucfirst_utf8($result);
  }

  protected function field56($value)
  {
    return $this->field31($value);
  }

  protected function field57($value)
  {
    return $this->field32($value);
  }

  protected function field58($value)
  {
    return $this->field33($value);
  }

  protected function field59($value)
  {
    return $this->field34($value);
  }

  protected function field18($value)
  {
    $result = '';
    switch ($value) {
      case 'да':
      case 'Да':
      case 'есть':
        $result = $this->select('с мебелью', 'имеется мебель/меблировка', 'есть мебель');
        break;

      default:
        $result = $this->select('без мебели', 'без меблировки', 'мебели нет');
        break;
    }

    return $this->ucfirst_utf8($result);
  }

  protected function field19($value)
  {
    $result = '';
    switch ($value) {
      case 'да':
      case 'есть':
        $result = $this->select('с бытовой техникой', 'имеется бытовая техника', 'есть бытовая техника');
        break;

      default:
        $result = $this->select('без бытовой техники', 'нет бытовой и кухонной техники', 'отсутствует бытовая техника');
        break;
    }

    return $result;
  }

  protected function field23($value)
  {
    return $this->field22($value);
  }

  protected function field74($value)
  {
    switch ($value){
      case 'проект':
        return $this->select('дом в проекте', 'квартира в проекте', 'квартира в проекте дома', 'проект дома', 'по проекту');

      case 'площадка':
        return $this->select('площадка дома', 'размечена площадка', 'есть площадка');

      case 'котлован':
        return $this->select('котлован дома', 'заложен котлован');

      case 'строится':
        return $this->select('дом строится', 'строящийся дом', 'в строящемся доме');

      case 'отделка':
        return $this->select('дом с отделкой', 'отделка в доме', 'произведена отделка', 'с отделкой в доме');

      case 'построен':
        return $this->select('дом построен', 'построенный дом', 'в готовом доме');

      case 'сдан':
        return $this->select('дом сдан', 'новый дом', 'дом, готовый к заселению');
    }
  }

  protected function field075(Lot $lot)
  {
    $near_metro = false;
    foreach($lot->getRegionnode(true) as $node) {
      if ($near_metro = $node->is_metro) {
        break;
      }
    }

    if ($near_metro) {
      return $this->select('Недалеко от метро', 'В шаговой доступности от метро', 'Рядом с метро', 'Метро рядом');
    }
    else {
      return $this->select('Недалеко от ж.д. станции', 'В шаговой доступности от ж.д. станции', 'Рядом с ж.д. станцией', 'Ж.д. станция рядом');
    }
  }

  protected function field76($value)
  {
    return implode(', ', array_map(array($this, 'field54'), explode(', ', $value)));
  }



  private function fixAutoDescription($text) {
    $replacment_map = array(
      '#\.+#s' => '.'
      ,'#,+#s' => ','
      ,'#кв\.#us' => '=1='
      ,'#сот\.#us' => '=2='
      ,'#\.\s+([А-Яа-я])#ues' => "'. '.mb_strtoupper('$1', 'utf-8')"
      ,'#([А-Яа-я])\s+([А-Яа-я])#ues' => "(mb_strtoupper('$2', 'utf-8') == '$2' ? '$1, '.mb_strtolower('$2', 'utf-8') : '$1 $2')"
      ,'#=1=#us' => 'кв.'
      ,'#=2=#us' => 'сот.'
    );

    return preg_replace(array_keys($replacment_map), array_values($replacment_map), $text);
  }
}
