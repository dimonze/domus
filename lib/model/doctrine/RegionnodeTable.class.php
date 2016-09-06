<?php

class RegionnodeTable extends Doctrine_Table
{
  public function createQuery($alias = '') {
    return parent::createQuery($alias)->orderBy('has_children, name');
  }
}