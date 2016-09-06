<?php

/**
 * form components.
 *
 * @package    domus
 * @subpackage form
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class formComponents extends sfComponents
{
  protected static $preloaded = array();

  public function executePreloadFields()
  {
    $results = Doctrine::getTable('FormField')->createQuery()
                  ->andWhereIn('id', $this->id)
                  ->execute();

    foreach ($results as $result)
    {
      self::$preloaded[$result->id] = $result;
    }
    return sfView::NONE;
  }

  public function executeRenderField()
  {
    $id = $this->id;
    if (!isset(self::$preloaded[$id]))
    {
      $this->id = array($id);
      $this->executePreloadFields();
    }

    $field = self::$preloaded[$id];
    if ($field) {

      $attributes = array();
      if ($this->class)
      {
        $attributes['class'] = $this->class;
      }

      $field_info = array();
      if ('select' == $field->getType() && $this->empty)
      {
        $field_info['empty'] = $this->empty;
      }

      $field->choice_strategy = 'empty';
      echo $field->getWidget($field_info)->render(sprintf($this->name, $id), null, $attributes);
    }

    return sfView::NONE;
  }
}
