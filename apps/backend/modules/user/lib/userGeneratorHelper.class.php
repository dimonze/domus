<?php

/**
 * user module helper.
 *
 * @package    domus
 * @subpackage user
 * @author     Garin Studio
 * @version    SVN: $Id: helper.php 12474 2008-10-31 10:41:27Z fabien $
 */
class userGeneratorHelper extends BaseUserGeneratorHelper
{
  public function linkToEdit($object, $params)
  {
    if (!is_null($object->deleted_at)) {
      return null;
    }
    return parent::linkToEdit($object, $params);
  }

  public function linkToDelete($object, $params)
  {
    if (!is_null($object->deleted_at)) {
      return null;
    }
    return parent::linkToDelete($object, $params);
  }

  public function linkToRestore($object, $params)
  {
    if (is_null($object->deleted_at)) {
      return null;
    }

    return '<li class="sf_admin_action_edit">' .
             link_to('Восстановить', $this->getUrlForAction('restore'), $object) .
           '</li>';
  }
}
