<?php
class News extends Post
{
  public function getRegionsArray()
  {
    $regions = array();
    foreach ($this->Region as $region){
      $regions[] = $region->id;
    }
    return $regions;
  }
  /**
   * set regions id's in post_region
   * @param array $value
   * @return null
   */
  public function setRegionsFromArray($value)
  {
    $new_regions = $value;
    $this->unlink('Region');

    if (!$new_regions) {
      return;
    }
    $regions = Doctrine::getTable('Region')->createQuery()->andWhereIn('id', $new_regions)->execute();
    foreach ($regions as $region) {
      $this->link('Region', $region->id);
      unset($new_regions[array_search($region->id, $new_regions)]);
    }
  }
}
?>