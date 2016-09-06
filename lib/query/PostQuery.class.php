<?php

class PostQuery extends Doctrine_Query
{
  const NEW_BUILDINGS_THEME_ID = 13;
  const COTTAGE_THEME_ID = 14;

  public function separateThemes()
  {
    $this
      ->leftJoin($this->getRootAlias() . '.PostTheme t')
      ->andWhere(self::getWhereExpression('t.theme_id'));
    return $this;
  }

  public static function getWhereExpression($field)
  {
    $result = '';
    $comparator = sfConfig::get('is_new_building') ? '=' : '!=';
    $result = sprintf('%s %s %s', $field, $comparator, self::NEW_BUILDINGS_THEME_ID);
    
    $comparator = sfConfig::get('is_cottage') ? '=' : '!=';
    $result .= ' AND '.sprintf('%s %s %s', $field, $comparator, self::COTTAGE_THEME_ID);
    
    return $result;
  }
  
  public function filterNewBuilding() {
    $this->leftJoin($this->getRootAlias() . '.PostTheme t');
    
    if(!sfConfig::get('is_new_building')) {
      $this->andWhere( sprintf('%s %s %s', 't.theme_id', '!=', self::NEW_BUILDINGS_THEME_ID) );
    }
    if(sfConfig::get('is_new_building')) {
      $this->andWhere( sprintf('%s %s %s', 't.theme_id', '=', self::NEW_BUILDINGS_THEME_ID) );
    }
    
    return $this;
  }
}