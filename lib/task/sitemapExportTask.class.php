<?php

class sitemapTask extends sfBaseTask
{
  private
    $has_results       = array(),
    $sphinx            = null,
    $region_id         = null,
    $exclude_region_id = null,
    $cid               = null,
    $commercial_types  = array(),
    $patterns          = array(),
    $regions           = array(),
    $districts         = array(),
    $cities            = array();

  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      new sfCommandOption('app', 'frontend', sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
      new sfCommandOption('region_id', null, sfCommandOption::PARAMETER_REQUIRED, 'Region.id', null),
      new sfCommandOption('exclude_region_id', null, sfCommandOption::PARAMETER_REQUIRED, 'exclude Region.id', null),
    ));

    $this->namespace        = 'sitemap';
    $this->name             = 'export';
    $this->briefDescription = '';
    $this->detailedDescription = '';
  }

  protected function execute($arguments = array(), $options = array())
  {
    $this->createConfiguration($options['app'], 'dev');
    $databaseManager = new sfDatabaseManager($this->configuration);

    $this->region_id = $options['region_id'];
    $this->exclude_region_id = $options['exclude_region_id'];

    $this->init(Doctrine::getTable('SitemapSeoData')->getConnection());
    $this->initSphinx();

    foreach ($this->patterns as $pattern) {
      $this->cid = $pattern['id'];

      if ($pattern['subsection']) {
        $ctypes = array($pattern['subsection']);
      }
      elseif (false !== strpos($pattern['link'], '[вид недвижимости]')) {
        $ctypes = $this->commercial_types;
      }
      else {
        $ctypes = array(0 => null);
      }

      foreach ($ctypes as $commercial_type) {
        $method = 'print' . sfInflector::camelize($pattern['level'] . '_pattern');
        $this->$method($pattern['link'], $pattern['section'], $commercial_type);
      }
    }
  }

  protected function init($conn)
  {
    $stmt = $conn->prepare('select value from form_field where id = ?');
    $stmt->execute(array(45));
    $this->commercial_types = explode("\n", $stmt->fetch(Doctrine::FETCH_COLUMN));
    $stmt->closeCursor();

    $stmt = $conn->prepare('
      select id, link, level, section, subsection from sitemap_seo_data
      where section <> ? and section is not null
      order by level, section
    ');
    $stmt->execute(array(''));
    $this->patterns = $stmt->fetchAll(Doctrine::FETCH_ASSOC);
    $stmt->closeCursor();

    if ($this->region_id) {
      $stmt = $conn->prepare('select id, name from region where id = ?');
      $stmt->execute(array($this->region_id));
    }
    elseif ($this->exclude_region_id) {
      $stmt = $conn->prepare('select id, name from region where id <> ?');
      $stmt->execute(array($this->exclude_region_id));
    }
    else {
      $stmt = $conn->prepare('select id, name from region');
      $stmt->execute();
    }
    while ($row = $stmt->fetch(Doctrine::FETCH_ASSOC)) {
      $this->regions[$row['id']] = $row['name'];
    }
    $stmt->closeCursor();


    if ($this->region_id) {
      $stmt = $conn->prepare('
        select region_id, name, socr from regionnode
        where region_id = ? and has_children = ? and list = ?
      ');
      $stmt->execute(array($this->region_id, 1, 1));
    }
    elseif ($this->exclude_region_id) {
      $stmt = $conn->prepare('
        select region_id, name, socr from regionnode
        where region_id <> ? and has_children = ? and list = ?
      ');
      $stmt->execute(array($this->exclude_region_id, 1, 1));
    }
    else {
      $stmt = $conn->prepare('
        select region_id, name, socr from regionnode
        where has_children = ? and list = ?
      ');
      $stmt->execute(array(1, 1));
    }
    while ($row = $stmt->fetch(Doctrine::FETCH_ASSOC)) {
      $this->districts[] = array(
        'region_id' => $row['region_id'],
        'name'      => Regionnode::formatName($row['name'], $row['socr'])
      );
    }
    $stmt->closeCursor();


    if ($this->region_id) {
      $stmt = $conn->prepare('
        select region_id, name, socr from regionnode
        where region_id = ? and list = ? and socr = ?
      ');
      $stmt->execute(array($this->region_id, 1, 'г'));
    }
    elseif ($this->exclude_region_id) {
      $stmt = $conn->prepare('
        select region_id, name, socr from regionnode
        where region_id <> ? and list = ? and socr = ?
      ');
      $stmt->execute(array($this->exclude_region_id, 1, 'г'));
    }
    else {
      $stmt = $conn->prepare('
        select region_id, name, socr from regionnode
        where list = ? and socr = ?
      ');
      $stmt->execute(array(1, 'г'));
    }
    while ($row = $stmt->fetch(Doctrine::FETCH_ASSOC)) {
      $this->cities[] = array(
        'region_id' => $row['region_id'],
        'name'      => Regionnode::formatName($row['name'], $row['socr'])
      );
    }
    $stmt->closeCursor();
  }

  protected function initSphinx()
  {
    $this->sphinx = new DomusSphinxClient(array(
      'select'        => 'id, rating',
      'maxmatches'    => 1,
      'timeout'       => 0,
    ));
  }


  protected function printRegionPattern($text, $type, $commercial_type = null)
  {
    foreach ($this->regions as $region_id => $region) {
      if (!$this->hasResults($type, $region_id, array('f45' => $commercial_type))) {
        continue;
      }
      $this->format(
        $this->url($type, $region_id, array('f45' => $commercial_type)),
        $this->replaceText($text, $region, $commercial_type)
      );
    }
  }

  protected function printDistrictPattern($text, $type, $commercial_type = null)
  {
    foreach ($this->districts as $district) {
      $params = array('f45' => $commercial_type, 'rn' => $district['name']);
      if (!$this->hasResults($type, $district['region_id'], $params)) {
        continue;
      }
      $this->format(
        $this->url($type, $district['region_id'], $params),
        $this->replaceText($text, null, $commercial_type, $district['name'])
      );
    }
  }

  protected function printCityPattern($text, $type, $commercial_type = null)
  {
    foreach ($this->cities as $city) {
      $params = array('f45' => $commercial_type, 'rn' => $city['name']);
      if (!$this->hasResults($type, $city['region_id'], $params)) {
        continue;
      }
      $this->format(
        $this->url($type, $city['region_id'], $params),
        $this->replaceText($text, null, $commercial_type, null, $city['name'])
      );
    }
  }

  protected function printVillagePattern($text, $type, $commercial_type = null)
  {
    $conn = Doctrine::getTable('Regionnode')->getConnection();
    if ($this->region_id) {
      $stmt = $conn->prepare('
        select region_id, name, socr from regionnode where region_id = ? and socr in (?, ?)
      ');
      $stmt->execute(array($this->region_id, 'д', 'п'));
    }
    elseif ($this->exclude_region_id) {
      $stmt = $conn->prepare('
        select region_id, name, socr from regionnode where region_id <> ? and socr in (?, ?)
      ');
      $stmt->execute(array($this->exclude_region_id, 'д', 'п'));
    }
    else {
      $stmt = $conn->prepare('
        select region_id, name, socr from regionnode where socr in (?, ?)
      ');
      $stmt->execute(array('д', 'п'));
    }

    while ($village = $stmt->fetch(Doctrine::FETCH_ASSOC)) {
      $name = Regionnode::formatName($village['name'], $village['socr']);
      $params = array('f45' => $commercial_type, 'rn' => $name);
      if (!$this->hasResults($type, $village['region_id'], $params)) {
        continue;
      }
      $this->format(
        $this->url($type, $village['region_id'], $params),
        $this->replaceText($text, null, $commercial_type, null, null, $name)
      );
    }

    $stmt->closeCursor();
  }

  protected function printStreetPattern($text, $type, $commercial_type = null)
  {
    $params = array();
    $q = '
      select s.name street_name, s.socr street_socr, r.name node_name, r.socr node_socr, r.region_id
      from street s
      left join regionnode r on r.id = s.regionnode_id
    ';
    if ($this->region_id) {
      $q .= ' where region_id = ?';
      $params[] = $this->region_id;
    }
    elseif ($this->exclude_region_id) {
      $q .= ' where region_id <> ?';
      $params[] = $this->exclude_region_id;
    }
    $stmt = Doctrine::getTable('Street')->getConnection()->prepare($q);
    $stmt->execute($params);

    while ($row = $stmt->fetch(Doctrine::FETCH_ASSOC)) {
      $street_name = Regionnode::formatName($row['street_name'], $row['street_socr']);
      $node_name = Regionnode::formatName($row['node_name'], $row['node_socr']);

      $params = array(
        'f45' => $commercial_type,
        'rn'  => in_array($row['region_id'], array(77, 78)) ? null : $node_name,
        'q'   => $street_name,
        'l'   => 'form',
      );
      if (!$this->hasResults($type, $row['region_id'], $params)) {
        continue;
      }

      $this->format(
        $this->url($type, $row['region_id'], $params),
        $this->replaceText($text, null, $commercial_type, null, 'в ' . $node_name, null, $street_name)
      );
    }

    $stmt->closeCursor();
  }



  protected function replaceText($text, $region = null, $type = null, $district = null, $city = null, $village = null, $street = null)
  {
    if ($region) {
      $text = preg_replace('/\[регион.*\]/iSU', $region, $text);
    }
    if ($type) {
      $text = preg_replace('/\[вид недвижимости]/iS', $type, $text);
    }
    if ($district) {
      $text = preg_replace('/\[район.*\]/iSU', $district, $text);
    }
    if ($city) {
      $text = preg_replace('/\[город.*\]/iSU', $city, $text);
    }
    if ($village) {
      $text = preg_replace('/\[деревн.* или посел.*\]/iSU', $village, $text);
    }
    if ($street) {
      $text = preg_replace('/\[улиц.*\]/iSU', $street, $text);
    }

    if (strpos($text, '[')) {
      throw new Exception($text);
    }

    return $text;
  }

  protected function url($type, $region_id, array $params = array())
  {
    $url = array(sprintf('http://www.mesto.ru/%s/%d/search', $type, $region_id));

    if (!empty($params['rn'])) {
      $params['l'] = 'form';
    }
    $params['cid'] = $this->cid;

    foreach ($params as $name => $value) {
      if (null === $value) {
        continue;
      }
      if (in_array($name, array('f45', 'rn'))) {
        $value = strtr($value, DomusSearchRoute::$translit_table);
      }
      elseif ('q' == $name) {
        $value = urlencode($value);
      }

      $url[] = sprintf('%s/%s', $name, $value);
    }

    return implode('/', $url);
  }

   protected function format($url, $text)
   {
     printf("%s\t%s\n", $url, $text);
  }

  protected function hasResults($type, $region_id, array $params = array(), $check_skip = true)
  {
    if ($check_skip && $this->shouldSkip($type, $region_id, $params)) {
      return false;
    }

    $search = array(
      'type'      => $type,
      'region_id' => $region_id,
      'field'     => array(),
    );

    if (!empty($params['rn'])) {
      $search['regionnode'] = array(addcslashes($params['rn'], '\/'));
    }
    if (!empty($params['q'])) {
      $search['q'] = addcslashes($params['q'], '\/');
    }
    if (!empty($params['f45'])) {
      $search['field'][45] = addcslashes($params['f45'], '\/');
    }

    $result = $this->performQuery($search);
    return $result['total_found'] > 0;
   }

   protected function shouldSkip($type, $region_id, array $params = array())
   {
     $keys = array();

     $keys[sprintf('%s:%s', $type, $region_id)] = array($type, $region_id, array(), false);

     if (!empty($params['rn'])) {
       $keys[sprintf('%s:%s:%s', $type, $region_id, $params['rn'])] = array(
         $type, $region_id, array('rn' => $params['rn']), false
       );
     }
     if (!empty($params['f45'])) {
       $keys[sprintf('%s:%s:%s', $type, $region_id, $params['f45'])] = array(
         $type, $region_id, array('f45' => $params['f45']), false
       );
     }

     foreach ($keys as $key => $params) {
       if (!isset($this->has_results[$key])) {
         $this->has_results[$key] = call_user_func_array(array($this, 'hasResults'), $params);
       }
       if (false === $this->has_results[$key]) {
         return true;
       }
     }

     return false;
   }


   protected function performQuery(array $params)
   {
      try {
        return $this->sphinx->search($params);
      }
      catch (Exception $e) {
        file_put_contents('php://stderr', sprintf("ERROR: %s\n", $e->getMessage()));

        $this->sphinx->Close();
        $this->initSphinx();
        sleep(1);
        return $this->performQuery($params);
      }
   }
}
