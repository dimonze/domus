<?php

require_once dirname(__FILE__).'/../lib/qaGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/qaGeneratorHelper.class.php';

/**
 * qa actions.
 *
 * @package    domus
 * @subpackage qa
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12474 2008-10-31 10:41:27Z fabien $
 */
class qaActions extends autoQaActions
{
  protected function buildQuery() {
    return parent::buildQuery()->andWhere('post_type = ?', 'qa');
  }

  protected function executeBatchDelete(sfWebRequest $request)
  {
    $ids = $request->getParameter('ids');

    $count = Doctrine_Query::create()
      ->update('Post')
      ->set('deleted', 1)
      ->whereIn('id', $ids)
      ->execute();

    if ($count >= count($ids))
    {
      $this->getUser()->setFlash('notice', 'The selected items have been deleted successfully.');
    }
    else
    {
      $this->getUser()->setFlash('error', 'A problem occurs when deleting the selected items.');
    }

    $this->redirect('@qa');
  }

}
