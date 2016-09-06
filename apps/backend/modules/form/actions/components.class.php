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
  public function executeWidgets()
  {
    $this->widgets = Doctrine::getTable('FormField')->createQuery()->execute();
  }
}
