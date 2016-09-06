<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Theme extends BaseTheme
{
  public static function getThemesArray()
  {
    $themes = Doctrine::getTable('Theme')->createQuery()
      ->select('id, title')
      ->andWhere(PostQuery::getWhereExpression('id'))
      ->fetchArray();
    $theme_array = array();
    foreach ($themes as $theme) {
      $theme_array[$theme['id']] = $theme['title'];
    }
    return $theme_array;
  }

  public static function getThemesWithActiveBlogPosts() {
    $themes = Doctrine::getTable('Theme')->createQuery('t')
      ->select('t.id, t.title, bp.id')
      ->andWhere(PostQuery::getWhereExpression('t.id'))
      ->leftJoin('t.BlogPost bp')
      ->andWhere('bp.status = ?','publish')
      ->having('bp.id IS NOT NULL')
      ->execute();
    $theme_array = array();
    foreach ($themes as $theme) {
      $theme_array[$theme->id] = $theme->title;
    }
    return $theme_array;
  }
  
  public static function getThemes() {
    return Doctrine::getTable('Theme')->createQuery('t')->where(PostQuery::getWhereExpression('t.id'))->execute();
  }

  public function hasActivePosts($type = null) {
    if(!$type) return false;
    $q = Doctrine_Query::create()
      ->select('p.id')
      ->from('Post p')
      ->leftJoin('p.Themes t')
      ->where('p.post_type = ?', $type)
      ->andWhere('p.status = ?', 'publish')
      ->andWhere('t.id = ?', $this->id)
      ->andWhere(PostQuery::getWhereExpression('t.id'))
      ->count();
    return $q ? true : false;
  }
}