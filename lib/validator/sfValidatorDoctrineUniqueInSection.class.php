<?php

/**
 * 
 * @see sfValidatorDoctrineUnique
 */
class sfValidatorDoctrineUniqueInSection extends sfValidatorDoctrineUnique
{
  /**
   * Configures the current validator.
   *
   * Available options:
   *
   *  * model:              The model class (required)
   *  * section_field:      The "section" for records
   *  * column:             The unique column name in Doctrine field name format (required)
   *                        If the uniquess is for several columns, you can pass an array of field names
   *  * primary_key:        The primary key column name in Doctrine field name format (optional, will be introspected if not provided)
   *                        You can also pass an array if the table has several primary keys
   *  * connection:         The Doctrine connection to use (null by default)
   *  * throw_global_error: Whether to throw a global error (false by default) or an error tied to the first field related to the column option array
   *
   * @see sfValidatorBase
   */
  protected function configure($options = array(), $messages = array())
  {
    parent::configure($options, $messages);
    
    $this->addRequiredOption('section_field');
    
    $invalid = !empty($messages['invalid']) ? $messages['invalid'] : $this->getMessage('invalid');
    $this->setMessage('invalid', $invalid);
  }
  /**
   * @see sfValidatorBase
   */
  protected function doClean($values)
  {
    $originalValues = $values;
    $table = Doctrine_Core::getTable($this->getOption('model'));
    if (!is_array($this->getOption('column')))
    {
      $this->setOption('column', array($this->getOption('column')));
    }
    
    //Remove section from 'column'
    $column = $this->getOption('column');
    $section = $this->getOption('section_field');
    $section_index = array_search($section, $column);
    if($section_index != false){
      unset( $column[$section_index] );
      $this->setOption('column', $column);
    }
                
    //if $values isn't an array, make it one
    if (!is_array($values))
    {
      //use first column for key
      $columns = $this->getOption('column');
      $values = array($columns[0] => $values);
    }
    
    $q = Doctrine_Core::getTable($this->getOption('model'))->createQuery('a');
    foreach ($this->getOption('column') as $column)
    {
      $colName = $table->getColumnName($column);
      if (!array_key_exists($column, $values))
      {
        // one of the column has be removed from the form
        return $originalValues;
      }

      $q->addWhere('a.' . $colName . ' = ?', $values[$column]);
      
      $sectionName = $table->getColumnName($section);
      $q->addWhere('a.' . $sectionName . ' = ?', $values[$section]);
    }
        
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