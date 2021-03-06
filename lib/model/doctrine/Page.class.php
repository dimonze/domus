<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Page extends BasePage
{
  public function setUrl($value) {
    $value = preg_replace('/(^(\/|\\\\)+)|((\/|\\\\)+$)/', '', $value);

    if (strlen($value) == 0)
    {
      throw new sfException('Trying to set empty url');
    }

    return $this->_set('url', $value);
  }

  public function getIsFirst() {
    return $this->getTable()->createQuery()
      ->andWhere('position < ?', $this->position)
      ->count() == 0;
  }

  public function getIsLast() {
    return $this->getTable()->createQuery()
      ->andWhere('position > ?', $this->position)
      ->count() == 0;
  }

  public function getPrevious() {
    return $this->getTable()->createQuery()
      ->andWhere('position < ?', $this->position)
      ->orderBy('position desc')
      ->fetchOne();
  }

  public function getNext() {
    return $this->getTable()->createQuery()
      ->andWhere('position > ?', $this->position)
      ->orderBy('position asc')
      ->fetchOne();
  }

  public function moveUp() {
    if ($this->is_first) {
      return true;
    }

    $prev_page = $this->Previous;
    list($prev_page->position, $this->position) = array($this->position, $prev_page->position);

    $prev_page->save();
    $this->save();

    $this->getTable()->checkAndNormalize();
  }

  public function moveDown() {
    if ($this->is_last) {
      return true;
    }

    $next_page = $this->Next;
    list($next_page->position, $this->position) = array($this->position, $next_page->position);

    $next_page->save();
    $this->save();

    $this->getTable()->checkAndNormalize();
  }

  public function save(Doctrine_Connection $conn = null) {
    if ($this->position <= 0) {
      $query = Doctrine::getTable('Page')->createQuery()
        ->orderBy('position desc')
        ->limit(1);
      if ($this->id) {
        $query->andWhere('id != ?', $this->id);
      }
      $last_page = $query->fetchOne(null, Doctrine::HYDRATE_ARRAY);
      $this->position = $last_page ? $last_page['position'] +1 : 1;
    }
    
    parent::save($conn);
  }

}