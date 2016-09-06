<?php

class SeoTexts extends BaseSeoTexts
{
  public static function getSeoTextForRegion($type, $region_id)
  {
    $query = Doctrine::getTable('SeoTexts')->createQuery()
      ->select('text')
      ->where('region_id = ?', $region_id)
      ->andWhere('section = ?', Lot::$types[$type])
      ->andWhere('region_node_id IS NULL')
      ->andWhere('real_estate_type IS NULL');

    if ($text = $query->fetchOne()) {
      return $text->text;
    }
  }
}