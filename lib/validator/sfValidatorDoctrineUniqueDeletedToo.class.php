<?php

/**
 * 
 * @see sfValidatorDoctrineUnique
 */
class sfValidatorDoctrineUniqueDeletedToo extends sfValidatorDoctrineUnique
{
  /**
   * @see sfValidatorBase
   */
  protected function doClean($values)
  {
    $originalValues = $values;
    $table = Doctrine::getTable($this->getOption('model'));
    if (!is_array($this->getOption('column')))
    {
      $this->setOption('column', array($this->getOption('column')));
    }

    //if $values isn't an array, make it one
    if (!is_array($values))
    {
      //use first column for key
      $columns = $this->getOption('column');
      $values = array($columns[0] => $values);
    }

    $q = Doctrine_Query::create()
          ->from($this->getOption('model') . ' a');

    foreach ($this->getOption('column') as $column)
    {
      $colName = $table->getColumnName($column);
      if (!array_key_exists($column, $values))
      {
        // one of the column has be removed from the form
        return $originalValues;
      }

      $q->addWhere('a.' . $colName . ' = ?', $values[$column]);
    }

    $q->andWhere('a.deleted_at IS NULL OR a.deleted_at IS NOT NULL');    
    $object = $q->fetchOne();

    // if no object or if we're updating the object, it's ok
    if (!$object || $this->isUpdate($object, $values))
    {
      return $originalValues;
    }

    $error = new sfValidatorError($this, 'invalid', array('column' => implode(', ', $this->getOption('column'))));

    if ($this->getOption('throw_global_error'))
    {
      throw $error;
    }

    $columns = $this->getOption('column');

    throw new sfValidatorErrorSchema($this, array($columns[0] => $error));
  }
}