<?php
function cached_component($module, $component, $vars, $prefix = null, $lifetime = 86400)
{
  $cache = new DomusCache();
  $key = sprintf('%s%s_%s', $prefix, $module, $component);
  if ($vars) {
    $key .= md5(serialize((array) $vars));
  }

  if ($cache->has($key) && $lifetime != -1) {
    echo $cache->get($key);
  }
  else {
    echo $data = get_component($module, $component, $vars ? (array) $vars : array());
    $cache->set($key, $data, $lifetime);
  }
}

function homepage_lots_info_list(Lot $lot)
{
  switch($lot->type){
    case 'apartament-sale':
      $field_id = 54;
      break;
    case 'apartament-rent':
      $field_id = 55;
      break;
    case 'house-sale':
      $field_id = 26;
      $string = 'Площадь:';
      $metrik = 'кв.м';
      break;
    case 'house-rent':
      $field_id = 26;
      $string = 'Площадь:';
      $metrik = 'кв.м';
      break;
    case 'commercial-sale':
      $field_id = 46;
      $string = 'Площадь:';
      $metrik = 'кв.м';
      break;
    case 'commercial-rent':
      $field_id = 46;
      $string = 'Площадь:';
      $metrik = 'кв.м';
      break;
    case 'cottage-sale':
      $result = '';
      $mkad_dist = $lot->getLotInfoField(92); 
      if(!empty($mkad_dist)) $result .= "<span>Расстояние от МКАД: $mkad_dist км</span>";
      if($area = from_to_output($lot->getLotInfoField(94), $lot->getLotInfoField(95), 'соток'))
        $result .= "<span>Площадь участков: $area</span>";
      if($area = from_to_output($lot->getLotInfoField(98), $lot->getLotInfoField(99), 'м<sup>2</sup>'))
        $result .= "<span>Площадь домов: $area</span>";
      if($area = from_to_output($lot->getLotInfoField(102), $lot->getLotInfoField(103), 'м<sup>2</sup>'))
        $result .= "<span>Площадь таунхаусов: $area </span>";
      return $result;
      break;
    default:
      $field_id = 0;
  }
  foreach ($lot->LotInfo as $lot_info) {
    if ($lot_info->field_id == $field_id){
      if (isset($string, $metrik)){
        $value = sprintf('%s %s %s', $string, $lot_info->value, $metrik);
      }
      else {
        $value = str_replace('квартира', '', $lot_info->value);
      }
      return "<span>" . $value . "</span>";
    }
  }
}

function link_to_post($post)
{
  if ($post instanceof Post) {
    $type = $post->post_type;
  }
  if ($post instanceof BlogPost) {
    $type = 'blog';
  }
  switch ($type) {
    case 'news':
      return link_to($post->title, '@news_show?id=' . $post->id);
    case 'article':
      return link_to($post->title, '@post_show?post_type=' . $type . '&id=' . $post->id);
    case 'analytics':
      return link_to($post->title, '@post_show?post_type=' . $type . '&id=' . $post->id);
    case 'events':
      return link_to($post->title, '@post_show?post_type=' . $type . '&id=' . $post->id);
    case 'author_article':
      return link_to($post->title, '@author_article_show?author_id=' . $post->author_id . '&id=' . $post->id);
    case 'expert_article':
      return link_to($post->title, '@expert_article_show?author_id=' . $post->author_id . '&id=' . $post->id);
    case 'qa':
      return link_to($post->title, '@qa_show?id=' . $post->id);
    case 'blog':
      return link_to($post->title, '@blog_post_show?blog_url=' . $post->Blog->url . '&id=' . $post->id);
  }
}

function format_h6_for_post($post)
{
  if ($post instanceOf Post || $post instanceOf BlogPost) {
    $h6 = '<h6>';
    $h6 .= format_date($post->created_at, 'd MMMM yyyy');

    if ($post instanceOf Post) {
      $type = $post->post_type;
    }
    if ($post instanceOf BlogPost) {
      $type = 'blog';
    }

    switch ($type) {
      case 'news':
        $h6 .= ', Новости';
        break;
      case 'events':
        $h6 .= ', События';
        break;
      case 'article':
        $h6 .= ', Статьи';
        break;
      case 'analytics':
        $h6 .= ', Аналитика';
        break;
      case 'qa':
        $h6 .= ', Вопрос&Ответ';
        break;
      case 'author_article':
        $h6 .= ', Авторская колонка, ' . $post->PostAuthor->name;
        break;
      case 'expert_article':
        $h6 .= ', Экспертное мнение, ' . $post->PostAuthor->name;
        break;
      case 'blog':
        $h6 .= ', Блог "' . $post->Blog->title . '", ' . $post->Blog->User->name;
        break;
    }

    return $h6;
  }
  return false;
}

function prepare_openx_zone($id)
{
  switch ($id) {
    case 0:
      return include_partial('banner/block2-inline-spec');
  }
  return false;
}

function format_seo_links($seo_links)
{
  sfContext::getInstance()->getConfiguration()->loadHelpers(array('Tag', 'Url'));

  $links = array(0 => array(), 1 => array(), 2 => array());
  $i = 0;
  shuffle($seo_links);

  foreach ($seo_links as $seo_link) {
    if($seo_link['url'] == 'root') $seo_link['url'] = null;
    $links[$i][] = link_to($seo_link['h1'], '@search_landing?type=' . Lot::getRoutingType($seo_link['type']) . '&slug=' . $seo_link['url']);

    if ($i < 2) {
      $i++;
    }
    elseif (2 == count($links[$i])) {
      break;
    }
    else {
      $i = 0;
    }
  }

  return sprintf('
    <div class="seo_links">
      <div class="seo-links-text">
        <div class="dd-text">
          <div class="cols3-dd">%s</div>' .
          '<div class="cols3-dd">%s</div>' .
          '<div class="cols3-dd cols3-dd-last">%s</div>
        </div>
      </div>
    </div>
  ', implode(' ', $links[0]), implode(' ', $links[1]), implode(' ', $links[2]));
}

function prepare_nodes_ul($nodes, $mode, $region_id = null, $node_id = null)
{
  $nb = (int) round(count($nodes) / 3);
  $i  = 0;
  $nodes_1 = array();
  $nodes_2 = array();
  $nodes_3 = array();

  if (null == $region_id) {
    $route = '@sitemap_region?';
  }
  elseif (null != $region_id && null == $node_id) {
    $route = '@sitemap_region_node?';
  }
  else {
    $route = '@sitemap_street?';
  }
  foreach ($nodes as $node) {
    if (null == $region_id) {
      $link = '<li>' .
        link_to(
          $node->name,
          $route
          . 'mode=' . $mode
          . '&region_id=' . $node->id
        )
        . '</li>';
    }
    elseif (null != $region_id && null == $node_id) {
      $link = '<li>' .
        link_to(
          $node->full_name,
          $route
          . 'mode=' . $mode
          . '&region_id=' . $region_id
          . '&node_id=' . $node->id
        )
        . '</li>';
    }
    else {
      $link = '<li>' .
        link_to(
          $node->full_name,
          $route
          . 'mode=' . $mode
          . '&region_id=' . $region_id
          . '&node_id=' . $node_id
          . '&street_id=' . $node->id
        )
        . '</li>';
    }

    if ($i >= 0 && $i <= $nb) {
      $nodes_1[] = $link;
    }

    if ($i > $nb && $i <= $nb * 2) {
      $nodes_2[] = $link;
    }

    if ($i > $nb * 2) {
      $nodes_3[] = $link;
    }
    $i++;
  }
  unset($nodes);

  $html = '';
  if (count($nodes_1) > 0) {
    $html .= '<ul class="col3">' . implode('', $nodes_1) . '</ul>';
  }
  if (count($nodes_2) > 0) {
    $html .= '<ul class="col3">' . implode('', $nodes_2) . '</ul>';
  }
  if (count($nodes_3) > 0) {
    $html .= '<ul class="col3">' . implode('', $nodes_3) . '</ul>';
  }

  return $html;
}

function lot_title(Lot $lot, $only_address = false, sfRequest $sf_request = null)
{ 
  $address1 = explode(', ', $lot->address1);
  for($i=0;$i<count($address1);$i++){
    if(preg_match('/(област(ь|и)|обл\.?$|\sкрай|республика|автономный округ)/iu', $address1[$i]) ) unset($address1[$i]);
  }
  $address1 = implode(', ', $address1);
  if(!is_null($sf_request) && $sf_request->hasParameter('landing')) {
    $title = $sf_request->getParameter('landing_lot_title_prefix');
    if(!empty($title)) {
      $title = preg_replace(array('#^\s+\.+\s+$#', '#{адрес}#u', '#{address}#u', '#\.+\s+$#'), '', trim($title));
      $title .= '. ' . preg_replace('/\,\s+$/', '', sprintf('%s, %s', $address1, $lot->address2));
      return $title;
    }
  }
  
  if($only_address) {
    return preg_replace('/\,\s+$/', '', sprintf('%s, %s', $address1, $lot->address2));
  }
  
  $title = MetaParse::getTitlePrepared($lot);

  if ('new_building-sale' == $lot->type) {
    $replace = 'в ЖК ';
    $zhk = $lot->getLotInfoField(91);
    if (mb_stristr($zhk, 'ЖК')) {
      $replace = 'в ';
    }
    if (!empty($zhk)) {
      $title = str_replace('в новостройке', $replace . $zhk, $title);
    }
  }
  
  if ('cottage-sale' == $lot->type) {
    $address1 = preg_split('#\s*,\s*#', $address1, -1, PREG_SPLIT_NO_EMPTY);
    //Если название поселка совпадает с последним элементом address1, то этот элемент не нужен
    if(mb_strpos($title, $address1[count($address1)-1])) unset( $address1[count($address1)-1] );
    $address1 = implode(', ', $address1);
    $title .= ',';
  }
  
  $address = preg_replace(array('/\,s*$/','#\s*,\s*,\s*#'), array('',', '), $address1 . (empty($lot->address2) ? '' : ', '.$lot->address2));
  return $title . ' ' . $address;
}

function lot_images($lot)
{
  $sizes = array(
    'big'    => array(800, 600),
    'medium' => array(320, 240),
    'thumb'  => array(84, 62)
  );

  $host = sfConfig::get('app_uploads_host');
  $result = array();
  $path = preg_replace('~^uploads/~', '', $lot->image_path);
  $images = $lot->images;
  $thumb_index = $lot->thumb;

  if (isset($images[$thumb_index]) && $thumb_index) {
    // swap indexes
    list($images[0], $images[$thumb_index]) = array($images[$thumb_index], $images[0]);
  }

  foreach ($images as $i => $image) {
    $item = array();
    foreach ($sizes as $name => $dims) {
      $item[$name] = sprintf('http://%s/%s/%dx%d/%s', $host, $path, $dims[0], $dims[1], $image);
    }
    $result[] = $item;
  }

  return $result;
}

function lot_image($lot, $width = 84, $height = 62, $geometry = null)
{
  $host = sfConfig::get('app_uploads_host');
  $shost = sfConfig::get('app_static_host');
  $path = preg_replace('~^uploads/~', '', $lot->image_path);
  $images = $lot->images;
  $index = $lot->thumb;

  if (empty($images)){
    return sprintf('//%s/images/no-thumb.png', $shost);
  }
  elseif (!isset($images[$index])) {
    $index = 0;
  }

  if (!$geometry) {
    $geometry = sprintf('%dx%d', $width, $height);
  }

  return sprintf('http://%s/%s/%s/%s', $host, $path, $geometry, $images[$index]);
}

function photo($item = null, $width = 150, $height = 150, $default = null)
{
  $host = sfConfig::get('app_uploads_host');
  $shost = sfConfig::get('app_static_host');

  if(!is_object($item)) return false;
  switch (get_class($item)) {
    case 'User':
    case 'myUser':
      $file = $item->photo;
      $default = sprintf('pict_%s.png', ($item &&'company' == $item->type) ? 'company' : 'user');
      break;

    case 'Post':
      $file = $item->title_photo;
      break;

    case 'PostAuthor':
      $file = $item->photo;
      break;
  }

  if (!empty($file)) {
    $path = preg_replace('~^uploads/~', '', $item->getPhotoPath(false));
    return sprintf('http://%s/%s/%dx%d/%s', $host, $path, $width, $height, $file);
  }
  elseif (!empty($default)) {
    return sprintf('//%s/images/%s', $shost, $default);
  }
}


function translit($str)
{
  return strtr($str, DomusSearchRoute::$translit_table);
}

function lot_search_url($lot)
{
  $params = array(
    'region_id'     => $lot->region_id,
    'location-type' => 'form',
    'regionnode'    => array(),
    'field'         => array(),
  );


  foreach (preg_split('/,\s+/', $lot->address1) as $i => $part) {
    if (0 === $i) {
      // skip first part
      continue;
    }
    $params['regionnode'][] = translit($part);
  }

  foreach (array(54, 55) as $field) {
    if ($value = $lot->getLotInfoField($field)) {
      if (preg_match('/^([5-9]|\d{2,})/', $value)) {
        $value = '5+-?и комнатная квартира';
      }
      $params['field'][$field] = array('or' => array(translit($value)));
    }
  }

  $url = DomusSearchRoute::buildHashFromParams($params);
  $url = preg_replace('/regionnode%5B\d*%5D/', 'rn', $url);

  $type = sfConfig::get('is_new_building')
    ? Toolkit::getGeoPseudoTypeForNB($lot->region_id)
    : $lot->type;

  return url_for('/' . Lot::getRoutingType($type) . $url);
}


function paginate_by_paragraph($text, $page, $onpage = 5)
{
  preg_match_all('#<.*?p.*?>.*?<.*?/.*?p.*?>#is', $text, $matches);
  return array(
    'data'    => implode("\n", array_slice($matches[0], ($page-1)*$onpage, $onpage)),
    'total'   => ceil(count($matches[0])/$onpage),
    'current' => $page,
    'next' => (($page + 1) <= ceil(count($matches[0])/$onpage) ? ($page + 1) : null),
    'prev' => (($page - 1) >= 1 ? ($page - 1) : null),
  );
}

function paginate_by_hr($text, $page)
{
  $data = preg_split( "#<hr.*?>#", $text );
  return array(
      'data'    => isset($data[$page-1]) ? $data[$page-1] : null,
      'total'   => ceil(count($data)),
      'current' => $page,
      'next' => (($page + 1) <= ceil(count($data)) ? ($page + 1) : null),
      'prev' => (($page - 1) >= 1 ? ($page - 1) : null)
  );
}

function link_to_banners($type, $route, $styles)
{
  $html = '<div class="clear">&nbsp;</div><div class="hypothec">';

  switch($type) {
    case 'apartament-sale':
      $link = 'Новостройки по такой же цене';
      break;
    case 'apartament-rent':
      $link = 'Еще снимаете?';
      break;
    case 'commercial-rent':
    case 'commercial-sale':
      $link = 'Заработали на новостройку?';
      break;
    default:
      $link = '';
  }

  if ('' != $link) {
    //If $route is a number, it is region id
    $route = preg_match('#^\d+$#', $route) ? Toolkit::getGeoHostByRegionId($route).'/all' : $route;
    $link = link_to($link, $route, $styles);
    return $html . $link . '</div>';
  }

  return $link;
}

function breadcrumbs($lot)
{
  //For Cottage and New Buildings only
  if($lot->type != 'new_building-sale' && $lot->type != 'cottage-sale') return '';

  $is_nb = $lot->type == 'new_building-sale' ? true : false;
  $names = $is_nb ? sfConfig::get('app_nb_names') : sfConfig::get('app_cottage_names');
  //Nothing to return if region name for current type doesn't exists
  if(empty($names[$lot->region_id])) return '';
  //Starts processing
  $links = array();
  $name = $names[$lot->region_id];
  $type_str = $is_nb ? 'Новостройки' : 'Коттеджные поселки';
  //Landing page for Region always exists
  $links[] = array(
      'link' => Toolkit::getGeoHostByRegionId($lot->region_id),
      'title' => $name,
      'weight' => 0
  );
  foreach ($lot->regionnode as $a => $node) {
    //Check type and sort by weight
    $name = $node->name;
    $weight = 3; //Default for metro and shosse
    if($node->is_metro) 
      $name = 'metro-' . $node->name;
    elseif ('р-н' == $node->socr //Normal
            || (in_array($lot->region_id, array(77,78)) && empty($node->socr)) //Moscow and Piter
    ) {
      if(!empty($node->socr)) { //Fix only for normal
        $node->name = 'в '.$node->getFullNamePrepositional();
        $node->socr = '';
        $name .= '-rajon';
      }
      $weight = 1;
    }
    if ('ш' == $node->socr) {
      $node->socr = '';
      $node->name = 'по '.WordInflector::get($node->name, WordInflector::TYPE_DATIVE).' шоссе';
      $name .= '-shosse';
    }
    $links[] = array(
      'link' => sprintf('%s/%s',
        Toolkit::getGeoHostByRegionId($lot->region_id),
        Toolkit::slugify($name)),
      'title' => $node->full_name,
      'weight' => $weight
    );
  }
  if(empty($links)) return '';

  usort($links, function($a, $b) { //Sort by weight
    if ($a['weight'] == $b['weight']) return 0;
    return ($a['weight'] < $b['weight']) ? -1 : 1;
  });
  array_walk($links, function(&$a, $b) use ($type_str) { //Remove weight info and create links
    $a = sprintf('<a href="%s">%s</a>', $a['link'], 
            ($b == 1 ? $type_str . ' ' : '') . $a['title']
    );
  });

  $links = $links[0] . ' &gt; ' . implode(' / ', array_slice($links, 1));
  return $links;
}

function get_full_description(Lot $lot) {
  $auto_description = $lot->auto_description;
  if (in_array($lot->type, array('apartament-sale', 'apartament-rent'))) {
    $params = array();
    $params['region_id']  = $lot->region_id;
    
    $nodes = array();
    $rnodes = $lot->getRegionnode(true);
    if (count($rnodes) > 0) {
      $nodes = $rnodes->getData();
    }
    
    if (!empty($nodes)) {
      foreach ($nodes as $node) {
        $params['nodes'][] = Regionnode::formatName($node->name, $node->socr);
      }
      $last_node = array_pop($nodes);
      $pattern = mb_stristr($lot->auto_description, $last_node->name, true, 'utf-8');
    }
    if (empty($pattern)) {
      foreach (array('с площадью', 'общей площадью', 'площадью', 'метраж квартиры') as $substring) {
        $pattern = mb_stristr($lot->auto_description, $substring, true, 'utf-8');
        if ($pattern) {
          $trash = array('общей', 'c', 'метраж');
          $pattern = trim(str_replace($trash, '', $pattern));
          break;
        }
      }
    }
    else {
      $pattern .= $last_node->name;
    }
    
    $params['field']      = $lot->getLotInfoFieldForLanding();
    $params['type']       = $lot->type;
    $sphinx = new DomusSphinxClient(array(
      'limit'         => 1,
      'maxmatches'    => 1,
      'timeout'       => 0,
    ));
    $result = $sphinx->getOneLandingPage($params);
    
    if (!empty($result['matches'])) {
      $landing_page = $result['matches'][0];
      $lpattern = $pattern;
      foreach ($nodes as $node) {
        $lpattern = trim(str_replace($node->name, '', $lpattern));
      }
      $lpattern = str_replace(array(',', '.'), '', $lpattern);
      $lpattern = trim(str_replace('метро', '', $lpattern));
      $lparams = unserialize($landing_page['attrs']['params']);
      
      $lpattern .= ' ' . str_replace('м.', 'метро', $lparams['regionnode'][0]);
      
      $replacement = link_to($lpattern, '@search_landing?type=' . Lot::getRoutingType($landing_page['attrs']['type']) . '&slug=' . $landing_page['attrs']['url']);
      $auto_description = str_replace($pattern, $replacement, $auto_description);
    }
  }
  
  return nl2br(trim($lot->description . "\n\n" . $auto_description));
}

function nb_button_with_jk(Lot $lot)
{
  switch($lot->type){
    case 'new_building-sale':
      $text = 'Подробная информация о новостройке';
      $replace = 'о ЖК ';
      $field = $lot->getLotInfoField(91);
      if (mb_stristr($field, 'ЖК')) {
        $replace = 'о ';
      }
      if (!empty($field)) {
        $text = str_replace('о новостройке', $replace . trim($field), $text);
      }
      break;
    case 'cottage-sale':
      $text = 'Подробная информация о посёлке ';
      $field = $lot->getLotInfoField(106);
      $field = preg_replace('#\s*п(о|а)с(е|ё)лок\s*#u', '', $field);
      $field = empty($field) ? $lot->address_info['city_region'] : $field;
      $text .= $field;
      break;
  }
  
  return nl2br(trim(strip_tags($text)));
}

function prepare_show_lot_url(Lot $lot)
{
  $host = Toolkit::getGeoHostByLotType($lot);
  $route_name  = 'show_lot';
  $route_name .= !empty($lot->slug) ? '_slug' : '';
  $route_name .= !empty($lot->slug) && $lot->type == 'new_building-sale' ? '_nb' : '';
  $route_name .= !empty($lot->slug) && $lot->type == 'cottage-sale' ? '_cottage' : '';
  return $host. url_for($route_name, $lot);
}

function from_to_output($from, $to, $tail = null)
{
  $result = array();
  foreach(array('from' => 'от', 'to' => 'до', 'tail' => '') as $var => $txt) {
    if($$var) {
      if(!(count($result) == 0 && $var == 'tail')) {
        if(floatval($$var)) $$var = round($$var, 2);
        $result[] = sprintf('%s %s', $txt, $$var);
      }
    }
  }
  return implode(' ', $result);
}

function lot_type_anchor($type, $count, $lot_types = null, $prefix = '')
{
  $types = sfConfig::get('app_lot_types');
  if( empty($types[$type]) ) return false;
  
  if($type == 'cottage-sale') {
    $lot_type = !empty($lot_types[107]['or']) ? $lot_types[107]['or'][0] : null;
    $out_types = array(
        'Дом/Коттедж' => 'коттеджей',
        'Таунхаусы и Дуплексы' => 'таунхаусов',
        'Участок' => 'участков',
        'Участок с подрядом' => 'участков'
    );
    if(!empty($lot_type))
      $anchor = $out_types[$lot_type];
    else {
      $out_types = array_unique($out_types);
      $anchor = implode(', ', array_slice($out_types, 0, count($out_types) - 1, true));
      $anchor .= ' и ' . array_pop($out_types);
    }
    
    $types[$type]['anchor'] = str_replace('{types}', $anchor, $types[$type]['anchor']);
    if(empty($prefix)) $prefix = ' Подмосковья';
  }
  
  return 'Найдено ' . $count . ' объявлений ' . $types[$type]['anchor'] . $prefix;
}

function preparePostUrlPatternPaginated($pattern, $page, $with_slug = false)
{
  $pattern = preg_replace(array('#&*page=\d+#','#\-part\d+$#'), '', $pattern);
  if($page > 1) $pattern .= $with_slug ? '-part%d' : '&page=%d';
  
  return sprintf($pattern, $page);
}
