<?php

require_once dirname(__FILE__).'/../lib/import_logsGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/import_logsGeneratorHelper.class.php';

/**
 * import_logs actions.
 *
 * @package    domus
 * @subpackage import_logs
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12474 2008-10-31 10:41:27Z fabien $
 */
class import_logsActions extends autoImport_logsActions
{
  public function executeGetErrors(sfWebRequest $request)
  {
    $this->forward404Unless($request->hasParameter('id'));
    $log_id = $request->getParameter('id');
    $conn = Doctrine_Manager::connection();
    $errors = $conn->prepare(
      'select e.internal_lot_id, e.message
       from import_errors_log e
       left join import_log l on e.log_id = l.id
       where l.id = ?
       order by e.internal_lot_id');
    $errors->execute(array($log_id));

    if ($errors->rowCount() > 0) {

      $this->setLayout(false);
      $this->errors = $errors;
      $this->getResponse()->clearHttpHeaders();
      $this->getResponse()->setHttpHeader(
        'Content-Type',
        'application/vnd.ms-excel;'
      );
      $this->getResponse()->setHttpHeader(
        'Content-Disposition',
        'attachment; filename=mesto.ru_import_errors_' . $log_id . '.csv'
      );
    }
    else {
      $this->forward404();
    }
  }
}
