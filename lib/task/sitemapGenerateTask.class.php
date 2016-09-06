<?php

class sitemapGenerateTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      new sfCommandOption('app', 'frontend', sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
    ));

    $this->namespace        = 'sitemap';
    $this->name             = 'generate';
    $this->briefDescription = '';
    $this->detailedDescription = '';
  }

  protected function execute($arguments = array(), $options = array())
  {
    ini_set('memory_limit', '4096M');
    
    $configuration = $this->createConfiguration($options['app'], 'dev');
    $databaseManager = new sfDatabaseManager($configuration);
    $conn = Doctrine::getTable('Lot')->getConnection();

    $files = array();
    $path = sfConfig::get('sf_web_dir');
    $tmp_path = sfConfig::get('sf_root_dir') . '/cache';
    $routes = sfYaml::load(sfConfig::get('sf_root_dir') . '/apps/frontend/config/routing.yml');

    foreach (glob($tmp_path . '/sitemap*') as $file) {
      unlink($file);
    }

    $regions = $conn->prepare('SELECT id FROM region');
    $regions->execute(array());
    while ($region_id = $regions->fetch(Doctrine::FETCH_COLUMN)) {
      $region = Toolkit::getRegionHostById($region_id);

      //Lots
      $stmt = $conn->prepare('
        SELECT `id`, `type`, `region_id`, `slug`, `created_at`, `updated_at`
        FROM `lot`
        WHERE `status` = ? AND `region_id` = ?
        ORDER BY `type` ASC, `created_at` DESC
      ');
      $stmt->execute(array('active', $region_id));
      $this->writeData($stmt, $tmp_path, $files, $region, $routes, false);

      //News portal
      $stmt = $conn->prepare('
        SELECT p.`id`, p.`post_type`, p.`author_id`, p.`created_at`, p.`updated_at`, pr.`region_id`
        FROM `post` AS p INNER JOIN `post_region` AS pr ON p.`id` = pr.`post_id`
        WHERE p.`status` = ? AND pr.`region_id` = ?
        ORDER BY p.`post_type` ASC, p.`created_at` DESC
      ');
      $stmt->execute(array('publish', $region_id));
      $this->writeData($stmt, $tmp_path, $files, $region, $routes);

      $region_files = array_filter($files, function($file) use($region) {
        return 0 === strpos($file, 'sitemap_' . $region);
      });

      if (1 === count($region_files)) {
        $file = array_pop($region_files);
        $new_file = str_replace('_1.xml', '.xml', $file);

        unset($files[array_search($file, $files)]);
        $files[] = $new_file;

        rename($tmp_path . '/' . $file, $tmp_path . '/' . $new_file);
      }
      elseif ($region_files) {
        $this->writeIndex($tmp_path, $region, $region_files);
        $files[] = sprintf('sitemap_%s.xml', $region);
      }
    }

    // moving files
    foreach (glob($path . '/sitemap*') as $file) {
      unlink($file);
    }
    foreach ($files as $file) {
      rename($tmp_path . '/' . $file, $path . '/' . $file);
    }
  }

  private function writeData($data, $tmp_path, &$files, $host, $routes, $close_block = true)
  {
    if(!$data->rowCount()) {
      return false;
    }
    $current_index = 1;

    while ($row = $data->fetch(Doctrine::FETCH_ASSOC)) {
      if (empty($fh) || !is_resource($fh)) {
        $fh = $this->open($tmp_path, $files, $row['region_id'], $current_index);
        $data_written = 0;
      }

      $data_written += fwrite($fh, $this->format($row, $host, $routes));

      if ($data_written > 8.5 * 1024 * 1024) {
        $current_index++;
        $this->close($fh);
      }
    }

    if ($close_block) {
      $this->close($fh);
    }
    else {
      fclose($fh);
    }

    return true;
  }

  private function format(array $row, $host, array $routes)
  {
    $last_mod = strtotime($row['updated_at'] ? $row['updated_at'] : $row['created_at']);
    if(empty($row['post_type'])){ //Lot
      $route = 'show_lot' . (empty( $row['slug']) ? '' : '_slug');
    }
    else{ //Post
      $row['page'] = 1;
      $route = $row['post_type'] . '_show';
      if(!array_key_exists($route, $routes)) {
        $route = 'post_show';
      }
    }

    return sprintf('
<url>
	<loc>http://%s.mesto.ru%s</loc>
	<lastmod>%s</lastmod>
	<changefreq>weekly</changefreq>
	<priority>0.7</priority>
</url>',
      $host, $this->formatRoute($routes[$route]['url'], $row), date('c', $last_mod)
    );
  }

  private function formatRoute($route, array $data){
    $placeholders = array();
    if (preg_match_all('#:([\w_-]+)#', $route, $placeholders)){
      foreach ($placeholders[1] as $v) {
        if (array_key_exists($v, $data)){
          $route = str_replace(array(':'.$v, '//'), array($data[$v], '/'), $route);
        }
      }
    }

    return $route;
  }

  private function open($path, array &$files, $region_id, $index = 1)
  {
    $name = sprintf('sitemap_%s_%d.xml', Toolkit::getRegionHostById($region_id), $index);
    $fh = fopen($path . '/' . $name, 'a+');

    if (!in_array($name, $files)) {
      $files[] = $name;
      fwrite($fh,
'<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
  http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">'
      );
    }

    return $fh;
  }

  private function close($fh)
  {
    if (is_resource($fh)) {
      fwrite($fh, "\n</urlset>");
      fclose($fh);
    }
  }

  private function writeIndex($path, $host, array $files)
  {
    $fh = fopen(sprintf('%s/sitemap_%s.xml', $path, $host), 'w');

    fwrite($fh,
'<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
  http://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd">'
      );

    foreach ($files as $file) {
      fwrite($fh, sprintf('
<sitemap>
  <loc>http://%s.mesto.ru/%s</loc>
  <lastmod>%s</lastmod>
</sitemap>',
        $host, $file, date('c'))
      );
    }

    fwrite($fh, "\n</sitemapindex>");
    fclose($fh);
  }
}