<?php

abstract class MetaParse
{
  protected static $placeholders = array(
    'default'         => array('{тип}', '{регион}'),
    'lot'             => array('{тип}', '{регион}', '{адрес1}', '{адрес2}', '{стоимость}', '{типе}', '{отправитель}'),
    'user'            => array('{имя}', '{компания}', '{телефон}', '{id}', '{регион}'),
    'news'            => array('{заголовок}', '{раздел}', '{тема}', '{регион}'),
    'posts'           => array('{заголовок}', '{раздел}', '{тема}', '{регион}'),
    'expert_article'  => array('{заголовок}', '{раздел}', '{тема}', '{автор}', '{регион}'),
    'author_article'  => array('{заголовок}', '{раздел}', '{тема}', '{автор}', '{регион}'),
    'qa'              => array('{заголовок}', '{тема}', '{автор}'),
    'blogs'           => array('{заголовок}', '{блог}', '{тема}'),
    'homepage'        => array('{регион}', '{регионе}')
  ),
  $_word_combinations = false;

  public static function setMetas(sfActions $action)
  {    
    //Landing page metas recover
    if(array_key_exists('landing', $action->getResponse()->getMetas())) {
      $action->getResponse()->addMeta('landing',null,true);
      return true;
    }
    
    if ('search' == $action->getModuleName() && in_array($action->getActionName(), array('index', 'get'))) {
      return self::setMetasSearch($action);
    }
    if ('news' == $action->getModuleName() && in_array($action->getActionName(), array('index', 'theme'))) {
      return self::setMetasNews($action);
    }
    if ('posts' == $action->getModuleName() && in_array($action->getActionName(), array('index', 'theme'))) {
      return self::setMetasPosts($action);
    }
    if ('expert_article' == $action->getModuleName() && in_array($action->getActionName(), array('index', 'theme', 'showauthor'))) {
      return self::setMetasAuthors($action, 'expert_article');
    }
    if ('author_article' == $action->getModuleName() && in_array($action->getActionName(), array('index', 'theme', 'showauthor'))) {
      return self::setMetasAuthors($action, 'author_article');
    }
    if ('qa' == $action->getModuleName() && in_array($action->getActionName(), array('index', 'theme', 'show', 'add'))) {
      return self::setMetasQA($action, 'qa');
    }
    if ('questionnaire' == $action->getModuleName() && in_array($action->getActionName(), array('index'))) {
      return self::setMetasQuestionnaire($action);
    }
    if ('blogs' == $action->getModuleName() 
        && in_array($action->getActionName(), array('index', 'theme', 'show', 'add', 'editpost', 'showpost', 'my'))) {
      return self::setMetasBlogs($action);
    }
    if ('page' == $action->getModuleName() && 'homepage' == $action->getActionName()) {
      return self::setMetasHomepage($action);
    }
    require sfContext::getInstance()->getConfigCache()->checkConfig('config/meta.yml');

    $response = $action->getResponse();
    $meta = sfConfig::get(sprintf('meta_%s_%s', $action->getModuleName(), $action->getActionName()));
    
    if (!empty($meta)) {
      if ('author_article' != $action->getModuleName()
          && 'expert_article' != $action->getModuleName()) {
        $parser = 'parse' . sfInflector::camelize($action->getModuleName());
        if (is_callable("MetaParse::$parser")) {
          $meta = self::$parser($meta, $action);
        }
      }
      else {
        $parser = 'parse' . sfInflector::camelize('authors');
        if (is_callable("MetaParse::$parser")) {
          $meta = self::$parser($meta, $action);
        }
      }
      $meta = self::parseDefault($meta, $action);

      foreach ($meta as $name => $content) {
        !empty($content) && $response->addMeta($name, $content);
      }
    }
    else {
      $meta = $response->getMetas();
      if (!empty($meta['name']) && empty($meta['title'])) {
        $response->addMeta('title', $meta['name']);
      }
    }
  }

  protected static function setMetasSearch(sfActions $action)
  {    
    $params = $action->getRequest()->getParameterHolder();
    $response = $action->getResponse();
    
    if(preg_match('#sale$#', Lot::getRealType($params->get('type')))){
      $type = array('продажа', 'купить');
    } else {
      $type = array('аренда', 'снять');
    }
    
    $metas = array(
        'title' => self::mb_ucfirst(vsprintf('%s {объект_g} {regionnodes}, %s {объект_a} {regionnodes}.', $type))
        ,'description' => sprintf('Место поиска недвижимости {регион} — каталог {объект_g} {regionnodes}, %s {объект_a}.', $type[1])
        ,'keywords' => self::mb_ucfirst(vsprintf('%s {объект_g} {regionnodes}, %s {объект_a} {regionnodes}', $type))
        ,'h1' => self::mb_ucfirst(sprintf('%s {объект_g} {regionnodes}', $type[0]))
    );
    
    $regionnodes = $commercial_type = null;
    
    /**
     * {регион}
     */
    $region = trim(str_replace(
      DomusSearchRoute::$_str2replace,
      DomusSearchRoute::$_str_replacements,
      sfContext::getInstance()->getUser()->current_region->name
    ));
    
    /**
     * {regionnodes}
     */
    $regionnodes = array();
    $replacments = array(
        '/^м. /' => 'у метро '
        ,'/^п. /' => ''
        ,'/^д. /' => ''
        ,'/^(.+)\s*мрн.$/' => 'в микрорайоне $1'
        ,'/^район /' => 'в районе '
        ,'/^г. /' => 'в городе '
        ,'/^пгт /' => 'в пгт '
        ,'/^(.+)\s*р\-н/eu' => "'в '.WordInflector::get('$1', WordInflector::TYPE_PREPOSITIONAL).' районе'"
        ,'/^ш.\s*(.+)/eu' => "'по '.WordInflector::get('$1', WordInflector::TYPE_DATIVE).' шоссе'"
    );
    
    foreach ($params->get('regionnode', array()) as $regionnode) {
      $regionnode = preg_replace(array_keys($replacments), array_values($replacments), $regionnode);
      $regionnodes[] = $regionnode;
    }
    
    $regionnodes = implode(', ', $regionnodes);
    if(empty($regionnodes)) {
      $regionnodes = 'в ' . WordInflector::get($region, WordInflector::TYPE_PREPOSITIONAL);
    }
    
    /**
     * {объект}
     */
    $object = array();
    $glue = '/';
    $object_types = self::getObjectTypes($params->get('type'));
    $object['obj_s'] = array($object_types['default'][0]);
    //Типы коммерческой недвижимости
    $fields = $params->get('field', array());
    if(!empty($fields[45]['orlike'])){
      $object['obj_s'] = $fields[45]['orlike'];
      $glue = ', ';
    }
    if(!empty($fields[54]['or'])){
      $object['obj_s'] = $fields[54]['or'];
      $glue = ', ';
    }
    if(!empty($fields[55]['or'])){
      $object['obj_s'] = $fields[55]['or'];
      $glue = ', ';
    }
    if(!empty($fields[64])){
      $object['obj_s'] = array($fields[64]);
      $glue = ', ';
    }
    if(!empty($fields[76]['or'])){
      $object['obj_s'] = $fields[76]['or'];
      $glue = ', ';
    }
    if(!empty($fields[107]['or'])){
      $object['obj_s'] = $fields[107]['or'];
      $glue = ', ';
    }
    
    //Формы
    foreach ($object['obj_s'] as $k => $v) {
      if (count($object['obj_s']) > 1 && stristr(Lot::getRealType($params->get('type')), 'apartament')) {      
        $object['obj_a'][] = array_key_exists($v, $object_types) ? preg_replace('# квартир#', '', $object_types[$v][1]) : $object_types['default'][1];
        $object['obj_g'][] = array_key_exists($v, $object_types) ? preg_replace('# квартир#', '', $object_types[$v][0]) : $object_types['default'][0];
      }
      else {
        $object['obj_a'][] = array_key_exists($v, $object_types) ? $object_types[$v][1] : $object_types['default'][1];
        $object['obj_g'][] = array_key_exists($v, $object_types) ? $object_types[$v][0] : $object_types['default'][0]; 
      }
    }

    if(count($object['obj_s']) > 1 && stristr(Lot::getRealType($params->get('type')), 'new_building')) {
      $object['obj_a'] = array($object_types['default'][1]);
      $object['obj_g'] = array($object_types['default'][0]);
    }
    
    /**
     * {обработка}
     */
    $tail = array(
      's' => '',
      'a' => '',
      'g' => '',
    );
    if (stristr(Lot::getRealType($params->get('type')), 'apartament') && count($object['obj_a']) > 1) {
      if (!in_array('квартир со свободной планировкой', $object['obj_g'])) {
        $tail = array(
          's' => ' квартир',
          'a' => ' квартир',
          'g' => ' квартир'
        );
      }
    }

    foreach ($metas as $k => $v) {
      $metas[$k] = preg_replace(
        array(
          '#{объект_a}#',
          '#{объект_g}#',
          '#{объект_s}#',
          '#{regionnodes}#',
          '#{регион}#'
        ),
        array(
          implode($glue, $object['obj_a']).$tail['a'],
          implode($glue, $object['obj_g']).$tail['g'],
          implode($glue, $object['obj_s']).$tail['s'],
          $regionnodes,
          $region
        ),
        $v);
    }
    $response->addMeta('title',       strip_tags($metas['title']));
    $response->addMeta('description', strip_tags($metas['description']));
    $response->addMeta('keywords',    strip_tags($metas['keywords']));
    $response->addMeta('name',        $metas['h1']);
    $response->addMeta('h1',          $metas['h1']);
  }

  public static function getCommercialTypeSearchName($value, $type = null)
  {
    if (null != $value) {
      $commercial_type = WordInflector::get($value, WordInflector::TYPE_GENITIVE);
    }
    else {
      $commercial_type = null;
    }
    if ($value != $commercial_type) {
      $commercial_type = mb_strtolower($commercial_type);
    }

    return $commercial_type;
  }

  protected static function getTypeName($type, $name = 'name')
  {
    $types = sfConfig::get('app_lot_types', array());
    return $types[$type][$name];
  }

  /** Parsers * */
  public static function getPlaceholders($type = 'default')
  {
    $merge = isset(self::$placeholders[$type]) ? self::$placeholders[$type] : array();
    if ($type != 'news'){
      return array_unique(array_merge(self::$placeholders['default'], $merge));
    }
    else {
      return array_unique($merge);
    }
  }

  protected static function parseDefault(array $data, sfActions $action)
  {
    $search = self::$placeholders['default'];
    $replace = array();


    $current_type = $action->getRequestParameter('type', $action->getRequestParameter('current_type'));
    if('rating' == $action->getActionName()) $current_type = null;

    if ($current_type) {
      $replace[] = self::getTypeName($current_type);
    }
    else {
      $search[0] = 'r\{тип\}(\s+\S\s+)?';
      $replace[] = '';
    }

    $current_region = $action->getUser()->current_region;
    $replace[] = (string) $current_region;

    foreach ($data as &$row) {
      foreach ($search as $i => $s) {
        if (substr($s, 0, 1) == 'r') {
          $row = preg_replace('/' . substr($s, 1) . '/', $replace[$i], $row);
        }
        else {
          $row = str_replace($s, $replace[$i], $row);
        }
      }
    }

    return $data;
  }

  protected static function parseLot(array $data, sfActions $action)
  {
    $lot = $action->lot;
    if ($lot) {
      //Компания или без посредников?
      $company = '';
      if($lot->getUser()->getType() == 'owner'){
        $company = ' - без посредников';
      }else{
        $company = $lot->getUser()->getCompanyName();
        $company = ' - ' . (empty($company) ? $lot->getUser()->getName() : $company);
      }
      
      $search = self::$placeholders['lot'];
      $replace = array(
        self::getTitlePrepared($lot),
        (string) $lot->Region,
        $lot->address1,
        $lot->address2,
        preg_replace('/&nbsp;/', ' ', $lot->getPriceFormated()),
        self::getTitlePrepared($lot, true),
        $company
      );
      //Special #16734 rules
      if('cottage-sale' == $lot->type){
        $data['title'] = 'Коттеджный поселок {тип} {адрес1}, купить коттедж в коттеджном поселке {тип}, купить дом в поселке {тип} без посредников, цены и отзывы';
        $data['description'] = $data['keywords'] = $data['h1'] = $data['title'];
        $data['h1'] = 'Коттеджный поселок {тип} {адрес1}';
        $replace[0] = $lot->getPrepearedLotInfoField(106);
      }
             
      foreach ($data as &$row) {
        $row = str_replace($search, $replace, $row);
      }
    }

    if (!empty($lot) && 'new_building-sale' == $lot->type) {
      $replace = 'в ЖК ';
      $zhk = $lot->getLotInfoField(91);
      if (mb_stristr($zhk, 'ЖК')) {
        $replace = 'в ';
      }
      if (!empty($zhk)) {
        $data['title'] = str_replace('в новостройке', $replace . $zhk, $data['title']);
      }
    }
    return $data;
  }
  
  public static function getTitlePrepared(Lot $lot, $genetive = false)
  {
    $title = '';
    if (!$lot) return $title;
    
    $case = !$genetive ? WordInflector::TYPE_ACCUSATIVE : WordInflector::TYPE_GENITIVE;
    //Покупка/аренда
    if(preg_match('#sale$#', $lot->type)){
      $title .= !$genetive ? 'Купить ' : ' покупке ';
    }else{
      $title .= !$genetive ? 'Снять ' : 'б аренде ';
    }
    
    switch ($lot->type) {
      case 'apartament-sale':
        $field = $lot->getLotInfoField(54);
      case 'apartament-rent':
        if(!isset($field)){
          $field = $lot->getLotInfoField(55);
        }
        
        if(preg_match('#(квартира|комната)#u', $field, $matches)){
          $title .= WordInflector::get($matches[0], $case);
        }
        break;
      
      case 'house-sale':
        $field = $lot->getLotInfoField(64);
        //Если есть размер (1/2 и т.п.), или предложный падеж, но не "дача",
        // то можно не изменять тип
        if(preg_match('#\d/\d#', $field) || ($case == WordInflector::TYPE_ACCUSATIVE && $field != 'дача')){
          $title .= $field;
          break;
        }

        $field = preg_split('#/#',$field); //Вдруг составной тип
        foreach ($field as $k => $type){
          $field[$k] = WordInflector::get($type, $case);
        }
        $title .= implode(',',$field);
        break;
      case 'house-rent':
        $title .= $genetive ? 'дома' : 'дом'; //Только 1 вариант
        break;
      
      case 'commercial-sale':
      case 'commercial-rent':
        $field = $lot->getLotInfoField(45);
        $field = preg_split('#\s*,\s*#',$field,-1,PREG_SPLIT_NO_EMPTY);
        
        foreach ($field as $k => $v) {//Вычисляем аббревиатуры
          if(!preg_match('#^[A-ZА-Я]+$#u', $v)) $field[$k] = mb_strtolower ($v);
        }

        if($case != WordInflector::TYPE_ACCUSATIVE){ //Винительный не нужно склонять
          foreach ($field as $k => $v) {
            $field[$k] = WordInflector::get($v, $case);
          }
        }
        $title .= implode(',',$field);
        break;
        
      case 'new_building-sale':
        $title .= WordInflector::get('квартира', $case) . ' в новостройке';
        break;
      
      case 'cottage-sale':
        $cname = $lot->getPrepearedLotInfoField(106);
        $cname = empty($cname) ? (empty($lot->address_info['city_region']) ? '' : $lot->address_info['city_region'] ) : $cname;
        $title = 'Коттеджный поселок'.(empty($cname) ? '' : ' '.$cname);
        break;
    }
    
    return $title;
  }

  protected static function parseUser(array $data, sfActions $action)
  {
    $user = $action->user;
    if (!$user) {
      $user = sfContext::getInstance()->getUser();
    }

    if ('rating' == $action->getActionName()) {
      $search = self::$placeholders['user'];
      $replace = array(
        '',
        '',
        '',
        '',
        $user->current_region->name
      );
      $type = $action->getRequest()->getParameter('type');
      $tmp = $data;
      $data = array();
      foreach ($tmp[$type] as $name => $val) {
        $data[$name] = str_replace($search, $replace, $val);
      }
    } elseif ($user) {
      $search = self::$placeholders['user'];
      $replace = array(
        $user->name,
        $user->company_name,
        $user->phone,
        $user->id,
        ''
      );

      foreach ($data as &$row) {
        $row = str_replace($search, $replace, $row);
      }
    }

    return $data;
  }

  protected static function parseNews(array $data, sfActions $action)
  {
    $user = sfContext::getInstance()->getUser();
    $news = $action->news;
    if ($news){
      $search = self::$placeholders['news'];
      $replace = array(
        $news->title_seo ? $news->title_seo : $news->title,
        $news->section,
        '',
        $user->current_region->name
      );

      foreach ($data as &$row) {
        $row = str_replace($search, $replace, $row);
      }
    }

    return $data;
  }

  protected static function parsePosts(array $data, sfActions $action)
  {
    $user = sfContext::getInstance()->getUser();
    $post = $action->post;
    if ($post){
      $search = self::$placeholders['posts'];
      $replace = array(
        $post->title_seo ? $post->title_seo : $post->title,
        Post::$types[$post->post_type],
        '',
        $user->current_region->name
      );

      foreach ($data as &$row) {
        $row = str_replace($search, $replace, $row);
      }
    }

    return $data;
  }

  protected static function parseAuthors(array $data, sfActions $action)
  {
    $user = sfContext::getInstance()->getUser();
    $post = $action->article;
    if ($post){
      $search = self::$placeholders[$post->post_type];
      $replace = array(
        $post->title_seo ? $post->title_seo : $post->title,
        Post::$types[$post->post_type],
        '',
        $post->PostAuthor->name,
        $user->current_region->name
      );

      foreach ($data as &$row) {
        $row = str_replace($search, $replace, $row);
      }
    }

    return $data;
  }

  protected static function setMetasQuestionnaire(sfActions $action) {
    $response = $action->getResponse();
    require sfContext::getInstance()->getConfigCache()->checkConfig('config/meta.yml');
    $data = sfConfig::get(sprintf('meta_%s_%s', $action->getModuleName(), $action->getActionName()));

    foreach ($data as $key => &$row) $response->addMeta($key, $row);
  }

  protected static function setMetasBlogs(sfActions $action) {
    $response = $action->getResponse();
    require sfContext::getInstance()->getConfigCache()->checkConfig('config/meta.yml');
    $params = $action->getRequest()->getParameterHolder();

    if (null != $params->get('theme')) {
      $theme = str_replace(
          array_values(DomusSearchRoute::$translit_table),
          array_keys(DomusSearchRoute::$translit_table),
          $params->get('theme')
      );
    } else $theme = '';
    $blog = '';
    $post = '';

    switch($action->getActionName()) {
      case 'show':
        $q = Doctrine::getTable('Blog')->findOneByUrl($params->get('blog_url'));
        $blog = $q->title;
        break;
      case 'editpost':
        $q = Doctrine::getTable('BlogPost')->findOneById($params->get('id'));
        $post = $q->title;
        break;
      case 'showpost':
        $q = Doctrine::getTable('BlogPost')->findOneById($params->get('id'));
        $post = $q->title;
        $blog = $q->Blog->title;
        break;
    }
    $data = sfConfig::get(sprintf('meta_%s_%s', $action->getModuleName(), $action->getActionName()));

    $search = self::$placeholders[$action->getModuleName()];
    $replace = array($post, $blog, $theme );
    foreach ($data as $key => &$row){
      $row = str_replace($search, $replace, $row);
      $response->addMeta($key, $row);
    }

  }

  protected static function setMetasNews(sfActions $action)
  {
    $params = $action->getRequest()->getParameterHolder();
    $response = $action->getResponse();
    $user = sfContext::getInstance()->getUser();
    require sfContext::getInstance()->getConfigCache()->checkConfig('config/meta.yml');

    $data = sfConfig::get(sprintf('meta_%s_%s', $action->getModuleName(), $action->getActionName()));
    $theme = '';
    if ('news' == $action->getModuleName()){
      switch ($params->get('news_section')) {
        case 'news-market':
          $section = News::$sections['news-market'];
          break;
        case 'news-portal':
          $section = News::$sections['news-portal'];
          break;
        case 'news-companies':
          $section = News::$sections['news-companies'];
          break;
        default:
          $section = '';
      }
    }
    if (null != $params->get('theme')) {
      $theme = str_replace(
        array_values(DomusSearchRoute::$translit_table),
        array_keys(DomusSearchRoute::$translit_table),
        $params->get('theme')
      );
    }
    $search = self::$placeholders[$action->getModuleName()];
    $replace = array('', $section, $theme, $user->current_region->name);
    foreach ($data as $key => &$row){
      $row = str_replace($search, $replace, $row);
      $response->addMeta($key, $row);
    }
  }

  protected static function setMetasPosts (sfAction $action)
  {
    $params = $action->getRequest()->getParameterHolder();
    if (!$params->has('post_type')){
      return false;
    }
    $response = $action->getResponse();
    $user = sfContext::getInstance()->getUser();
    require sfContext::getInstance()->getConfigCache()->checkConfig('config/meta.yml');
    $post_type = $params->get('post_type');
    $data = sfConfig::get(sprintf('meta_%s_%s', $action->getModuleName(), $action->getActionName()));
    $theme = '';

    if (null != $params->get('theme')) {
      $theme = str_replace(
        array_values(DomusSearchRoute::$translit_table),
        array_keys(DomusSearchRoute::$translit_table),
        $params->get('theme')
      );
    }
    $search = self::$placeholders['posts'];
    $replace = array('', Post::$types[$post_type], $theme, $user->current_region->name);
    foreach ($data as $key => &$row){
      $row = str_replace($search, $replace, $row);
      $response->addMeta($key, $row);
    }
  }

  protected static function setMetasAuthors (sfAction $action, $author_type = null)
  {
    if (null == $author_type){
      return false;
    }
    $params = $action->getRequest()->getParameterHolder();
    $response = $action->getResponse();
    $user = sfContext::getInstance()->getUser();
    require sfContext::getInstance()->getConfigCache()->checkConfig('config/meta.yml');
    $data = sfConfig::get(sprintf('meta_%s_%s', $action->getModuleName(), $action->getActionName()));
    $theme = '';

    if (null != $params->get('theme')) {
      $theme = str_replace(
        array_values(DomusSearchRoute::$translit_table),
        array_keys(DomusSearchRoute::$translit_table),
        $params->get('theme')
      );
    }
    if (null != $params->get('author_id')) {
      $author = Doctrine_Query::create()
        ->select('name')
        ->from('PostAuthor p')
        ->where('p.id = ?', $params->get('author_id'))
        ->fetchOne();
    }
    $search = self::$placeholders[$author_type];
    $replace = array(
      '',
      Post::$types[$author_type],
      $theme,
      (isset($author)) ? $author->name : '',
      $user->current_region->name
    );

    foreach ($data as $key => &$row){
      $row = str_replace($search, $replace, $row);
      $response->addMeta($key, $row);
    }
  }

  protected static function setMetasQA(sfAction $action, $author_type = null) {

    if (null == $author_type) {
      return false;
    }

    $params = $action->getRequest()->getParameterHolder();
    $response = $action->getResponse();
    $user = sfContext::getInstance()->getUser();
    require sfContext::getInstance()->getConfigCache()->checkConfig('config/meta.yml');
    $data = sfConfig::get(sprintf('meta_%s_%s', $action->getModuleName(), $action->getActionName()));
    $theme = '';

    if (null != $params->get('theme')) {
      $theme = str_replace(
          array_values(DomusSearchRoute::$translit_table),
          array_keys(DomusSearchRoute::$translit_table),
          $params->get('theme')
      );
    }

    if ('show' == $action->getActionName()) {
      $qa = Doctrine::getTable('Post')->findOneById($params->get('id'));
      $author = $qa->User->name ? $qa->User->name : $qa->author_name;
    }

    $search = self::$placeholders[$author_type];
    $replace = array(
      isset($qa) ? (isset($qa->title_seo)) ? $qa->title_seo : $qa->title : '',
      $theme,
      (isset($author)) ? $author : ''
    );

    foreach ($data as $key => &$row) {
      $row = str_replace($search, $replace, $row);
      $response->addMeta($key, $row);
    }
  }

  protected static function setMetasHomepage (sfAction $action)
  {
    $params = $action->getRequest()->getParameterHolder();
    $response = $action->getResponse();
    $user = sfContext::getInstance()->getUser();
    require sfContext::getInstance()->getConfigCache()->checkConfig('config/meta.yml');
    $data = sfConfig::get(sprintf('meta_%s_%s', $action->getModuleName(), $action->getActionName()));
    
    $region_name = self::prepareRegionNameForMetas($user->current_region->name);
    
    $search = self::$placeholders['homepage'];
    $replace = array(
      trim(vsprintf('%s %s', $region_name)),
      trim(sprintf('%s %s', WordInflector::get($region_name[0], WordInflector::TYPE_PREPOSITIONAL), WordInflector::get($region_name[1], WordInflector::TYPE_PREPOSITIONAL)))
    );
    
    $host = $action->getRequest()->getHost();
    if (preg_match('#^(novostroyki)\.#', $host)) {
      $search[] = 'недвижимости';
      $replace[] = 'новостроек';
      
      //#16354 Special 2 regions
      $replace[0] .= ' и Подмосковье';
      $replace[1] .= ' и Подмосковье';
      //#16835
      $search[] = 'и аренде городской и загородной';
      $replace[] = '';
      
      $search[] = 'и аренде квартир, домов, офисов';
      $replace[] = 'квартир в новостройках';
      
      $data['keywords'] = 'Недвижимость, недвижимость {регион}, новостройки {регион}, квартира в новостройке {регион}';
    }
    
    //Cottages
    if (preg_match('#^(cottage)\.#', $host)) {
      $data = array(
          'title' => 'Место поиска коттеджных поселков {регион} — каталог коттеджных поселков Подмосковья, дома, коттеджи, таунхаусы, участки в {регионе}',
          'description' => 'Место поиска коттеджных поселков {регион} — каталог коттеджных поселков Подмосковья, коттеджи, таунхаусы, участки в {регионе}',
          'keywords' => 'Каталог коттеджных поселков Подмосковья, коттеджи, таунхаусы, участки в {регионе}, купить коттедж {регион}',
          'name' => 'Каталог коттеджных поселков Подмосковья',
          'h1' => 'Коттеджные поселки Подмосковья'
      );
    }
    
    foreach ($data as $key => &$row){
      $row = str_replace($search, $replace, $row);
      $response->addMeta($key, $row);
    }
  }

  protected static function getMetasForRn($params)
  {
    $response = sfContext::getInstance()->getResponse();
    $cid    = $params->get('cid');
    $type   = $params->get('type');
    $street = $params->get('q');

    $data = Doctrine::getTable('SitemapSeoData')->find($cid);

    if (!$data->count())
      return false;

    $region = Doctrine::getTable('Region')->find($params->get('region_id'));
    if (!$region) {
      return false;
    }

    $regionnode = $params->get('regionnode');

    switch ($data->level) {
      case 'city':
      case 'district':
      case 'village':
        $replace[] = implode(', ', $regionnode);
        break;
      case 'street':
        $replace[]  = $street;
        break;
      case 'region':
        $replace[] = $region->name;
        break;

    }

    switch ($data->level) {
      case 'city':
        $pattern[] = '/\[город([А-я])*\]/';
        break;
      case 'street':
        $pattern[] = '/\[улиц([А-я])*\]/';
        $pattern[] = '/\[город([А-я])*\]/';
        if (empty($regionnode)) {
          $replace[]  = 'в ' . $region->full_name_prepositional;
        }else {
          $replace[]  = 'в ' . implode(', ', $regionnode);
        }
        break;
      case 'village':
        $pattern[] = '/\[деревне или поселке\]/';
        break;
      case 'region':
        $pattern[] = '/\[регион([А-я])*\]/';
        break;
      case 'district':
        $pattern[] = '/\[район([А-я])*\]/';
        break;
    }

    $h1     = preg_replace($pattern, $replace, $data->h1);
    $title  = preg_replace($pattern, $replace, $data->title);

    if ($type == 'commercial-sale' || $type == 'commercial-rent') {
      $fields = $params->get('field');

      if (isset($fields[45]['orlike']) && 1 == count($fields[45]['orlike'])) {
        $choice = $fields[45]['orlike'][0];
      }
      else {
        $choice = '';
      }

      $h1 = str_replace('[вид недвижимости]', $choice, $h1);
      $title = str_replace('[вид недвижимости]', $choice, $title);
    }

    if ($params->get('type') == $data->section) {
      $response->addMeta('title', $title);
      $response->addMeta('name', $h1);
      return true;
    }

    return false;
  }

  public static function mb_ucfirst($word) {
    return mb_strtoupper(mb_substr($word, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr(mb_convert_case($word, MB_CASE_LOWER, 'UTF-8'), 1, mb_strlen($word), 'UTF-8');
  }
  
  public static function prepareRegionNameForMetas($region_name)
  {
    $region = Regionnode::unformatName($region_name);
    switch ($region[1]) {
      case 'обл':
      case 'обл.':
        $region[1] = 'область';
        break;
      
      case 'г':
      case 'г.':
      default:
        $region[1] = '';
    }
    return $region;
  }
  
  public static function generateLandingMeta($landing_params)
  {
    if(preg_match('#sale$#', Lot::getRealType($landing_params['type']))){
      $type = array('продажа', 'купить');
    } else {
      $type = array('аренда', 'снять');
    }
    
    $metas = array(
        'title' => self::mb_ucfirst(vsprintf('%s {объект_g} {regionnodes}, %s {объект_a} {regionnodes}.', $type))
        ,'description' => sprintf('Место поиска недвижимости {регион} — каталог {объект_g} {regionnodes}, %s {объект_a}.', $type[1])
        ,'keywords' => self::mb_ucfirst(vsprintf('%s {объект_g} {regionnodes}, %s {объект_a} {regionnodes}', $type))
        ,'h1' => self::mb_ucfirst(sprintf('%s {объект_g} {regionnodes}', $type[0]))
    );
    
    $regionnodes = $commercial_type = null;
    
    /**
     * {регион}
     */

    $region = trim(str_replace(
      DomusSearchRoute::$_str2replace,
      DomusSearchRoute::$_str_replacements,
      $landing_params['region_name']
    ));
    
    /**
     * {regionnodes}
     */
    $regionnodes = array();
    $replacments = array(
        '/^м. /' => 'у метро '
        ,'/^п. /' => ''
        ,'/^д. /' => ''
        ,'/^(.+)\s*мрн.$/' => 'в микрорайоне $1'
        ,'/^район /' => 'в районе '
        ,'/^г. /' => 'в городе '
        ,'/^пгт /' => 'в пгт '
        ,'/^(.+)\s*р\-н/eu' => "'в '.WordInflector::get('$1', WordInflector::TYPE_PREPOSITIONAL).' районе'"
        ,'/^ш.\s*(.+)/eu' => "'по '.WordInflector::get('$1', WordInflector::TYPE_DATIVE).' шоссе'"
    );
    
    if (!empty($landing_params['params']['regionnode'])) {
      foreach ($landing_params['params']['regionnode'] as $regionnode) {
        $regionnode = preg_replace(array_keys($replacments), array_values($replacments), $regionnode);
        $regionnodes[] = $regionnode;
      }
      $regionnodes = implode(', ', $regionnodes);
    }
    
    if(empty($regionnodes)) {
      $regionnodes = 'в ' . WordInflector::get($region, WordInflector::TYPE_PREPOSITIONAL);
    }
    
    /**
     * {объект}
     */
    $object = array();
    $glue = '/';

    $object_types = self::getObjectTypes($landing_params['type']);
    $object['obj_s'] = array($object_types['default'][0]);
    //Типы недвижимости
    if (!empty($landing_params['params']['field'])) {
      $fields = $landing_params['params']['field'];
      if(!empty($fields[45]['orlike'])){
        $object['obj_s'] = $fields[45]['orlike'];
        $glue = ', ';
      }
      if(!empty($fields[54]['or'])){
        $object['obj_s'] = $fields[54]['or'];
        $glue = ', ';
      }
      if(!empty($fields[55]['or'])){
        $object['obj_s'] = $fields[55]['or'];
        $glue = ', ';
      }
      if(!empty($fields[64])){
        $object['obj_s'] = array($fields[64]);
        $glue = ', ';
      }
      if(!empty($fields[76]['or'])){
        $object['obj_s'] = $fields[76]['or'];
        $glue = ', ';
      }
      if(!empty($fields[107]['or'])){
        $object['obj_s'] = $fields[107]['or'];
        $glue = ', ';
      }
    }
    //Формы
    foreach ($object['obj_s'] as $k => $v) {
      $object['obj_a'][] = array_key_exists($v, $object_types) ? $object_types[$v][1] : $object_types['default'][1];
      $object['obj_g'][] = array_key_exists($v, $object_types) ? $object_types[$v][0] : $object_types['default'][0];
    }
    
    /**
     * {обработка}
     */
    foreach ($metas as $k => $v) {
      $metas[$k] = preg_replace(
              array( '#{объект_a}#', '#{объект_g}#', '#{объект_s}#', '#{regionnodes}#', '#{регион}#' ), 
              array( implode($glue, $object['obj_a']), implode($glue, $object['obj_g']), implode($glue, $object['obj_s']), $regionnodes, $region ), $v);
    }
    
    return $metas;
  }

  public static function getObjectTypes($type = null)
  {
    $object_types = array(
      'apartament' => array(
        'комната' => array(
          'комнат', 'комнату'
        ),
        '1 комнатная квартира' => array(
          'однокомнатных квартир',
          'однокомнатную квартиру'
        ),
        '2-х комнатная квартира' => array(
          'двухкомнатных квартир',
          'двухкомнатную квартиру'
        ),
        '3-х комнатная квартира' => array(
          'трехкомнатных квартир',
          'трехкомнатную квартиру'
        ),
        '4-х комнатная квартира' => array(
          'четырехкомнатных квартир',
          'четырехкомнатную квартиру'
        ),
        '5+-?х комнатная квартира' => array(
          'пятикомнатных квартир',
          'пятикомнатную квартиру'
        ),
        'квартира со свободной планировкой' => array(
          'квартир со свободной планировкой',
          'квартиру со свободной планировкой'
        ),
        'default' => array(
          'квартир и комнат', 'квартиру или комнату'
        )
      ),
      'house' => array(
        'дача' => array(
          'дач', 'дачу'
        ),
        'коттедж/дом' => array(
          'коттеджей и домов', 'коттедж или дом'
        ),
        'часть дома' => array(
          'части дома', 'часть дома'
        ),
        'особняк' => array(
          'особняков', 'особняк'
        ),
        'таунхаус' => array(
          'таунхаусов', 'таунхаус'
        ),
        'участок' => array(
          'участков', 'участок'
        ),
        'default' => array(
          'домов и участков', 'дом или участок'
        )
      ),
      'commercial' => array(
        'Автосервис' => array(
          'автосервисов', 'автосервис'
        ),
        'АЗС' => array(
          'АЗС', 'АЗС'
        ),
        'Банковское помещение' => array(
          'банковских помещений', 'банковское помещение'
        ),
        'Бизнес-парк' => array(
          'бизнес-парков', 'бизнес-парк'
        ),
        'Бизнес-центр' => array(
          'бизнес-центров', 'бизнес-центр'
        ),
        'Гостиница / мотель' => array(
          'гостиниц и мотелей', 'гостиницу или мотель'
        ),
        'Грузовой терминал' => array(
          'грузовых терминалов', 'грузовой терминал'
        ),
        'Дом отдыха / пансионат' => array(
          'домов отдыха и пансионатов',
          'дом отдыха или пансионат'
        ),
        'Завод / фабрика' => array(
          'заводов и фабрик', 'завод или фабрику'
        ),
        'Земля' => array(
          'земли', 'землю'
        ),
        'Логистический центр' => array(
          'логистических центров', 'логистический центр'
        ),
        'Магазин' => array(
          'магазинов', 'магазин'
        ),
        'Объект здравоохранения' => array(
          'объектов здравоохранения', 'объект здравоохранения'
        ),
        'Объекты бытовых услуг' => array(
          'объектов бытовых услуг', 'объект бытовых услуг'
        ),
        'Отд. стоящее здание' => array(
          'отдельно стоящих зданий', 'отдельно стоящее здание'
        ),
        'Офис' => array(
          'офисов', 'офис'
        ),
        'Производ. площади' => array(
          'производственных площадей', 'производственные площади'
        ),
        'Развлекательный' => array(
          'помещений развлекательного назначения',
          'помещение развлекательного назначения'
        ),
        'Ресторан / кафе' => array(
          'ресторанов и кафе', 'ресторан или кафе'
        ),
        'Розничная сеть' => array(
          'розничных сетей', 'розничную сеть'
        ),
        'Свободного назначения' => array(
          'помещений свободного назначения',
          'помещение свободного назначения'
        ),
        'Склад' => array(
          'складов', 'склад'
        ),
        'Спорт. назначения' => array(
          'помещений спортивного назначения',
          'помещение спортивного назначения',
        ),
        'Торговые площади' => array(
          'торговых площадей', 'торговую площадь'
        ),
        'Другое' => array(
          'помещений неопределённого назначения',
          'помещение неопределённого назначения'
        ),
        'default' => array(
          'коммерческой недвижимости', 'коммерческую недвижимость'
        )
      ),
      'new_building' => array(
        '1' => array(
          'однокомнатных квартир в новостройках',
          'однокомнатную квартиру в новостройке'
        ),
        '2' => array(
          'двухкомнатных квартир в новостройках',
          'двухкомнатную квартиру в новостройке'
        ),
        '3' => array(
          'трехкомнатных квартир в новостройках',
          'трехкомнатную квартиру в новостройке'
        ),
        '4' => array(
          'четырехкомнатных квартир в новостройках',
          'четырехкомнатную квартиру в новостройке'
        ),
        '5' => array(
          'пяти- и более комнатных квартир в новостройках',
          'пяти- и более комнатную квартиру в новостройке'
        ),
        'своб. планировка' => array(
          'квартир со свободной планировкой в новостройках',
          'квартиру со свободной планировкой в новостройке'
        ),
        'default' => array(
          'квартир в новостройках', 'квартиру в новостройке'
        )
      ),
      'cottage' => array(
        'Дом/Коттедж' => array(
          'коттеджных поселков', 'коттеджный поселок'
        ),
        'Таунхаусы и Дуплексы' => array(
          'таунхаусов и дуплексов', 'таунхаус и дуплекс'
        ),
        'Участок' => array(
          'участков', 'участок'
        ),
        'Участок с подрядом' => array(
          'участков с подрядом', 'участок с подрядом'
        ),
        'default' => array(
          'коттеджных поселков', 'коттеджный поселок'
        )
      )
    );
    
    $type = str_replace(array('-sale', '-rent'), '', Lot::getRealType($type));
    return array_key_exists($type, $object_types) ? $object_types[$type] : false;
  }
}
