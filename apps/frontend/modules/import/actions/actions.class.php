<?php

/**
 * import actions.
 *
 * @package    domus
 * @subpackage import
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class importActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $this->redirect('@import?action=importFile');
  }

  public function executeImportFile(sfWebRequest $request)
  {
    $this->forward404Unless($this->getUser()->type == 'company');

    $this->formFields = Doctrine::getTable('FormField')
      ->createQuery('ff')
      ->leftJoin('ff.FormField f')
      ->where('ff.xml_name IS NOT NULL')
      ->andWhere('f.type = ?', 'apartament-sale')
      ->orderBy('f.position')
      ->execute();

    $this->paid_types = array();

    $query = Doctrine::getTable('ImportOrder')->createQuery('i')
            ->select('date_from, date_to, info')
            ->andWhere('i.date_from < NOW()')
            ->andWhere('i.date_to > NOW()')
            ->andWhere('i.user_id = ?')
            ->andWhere('i.status = ?')
            ->orderBy('date_to desc')
            ->execute(array($this->getUser()->id, 'active'), Doctrine::HYDRATE_ARRAY);
    foreach($query as $order) {
      $order['info'] = unserialize($order['info']);
      if (isset($order['info']['type'])){
        foreach($order['info']['type'] as $type) {
          $result = array(
            'from' => date('d.m.y', strtotime($order['date_from'])),
            'to'   => date('d.m.y', strtotime($order['date_to'])),
          );
          switch ($type) {
            case 'apartament-sale': $type_id = ImportFile::TYPE_APARTAMENT_SALE_XML; break;
            case 'apartament-rent': $type_id = ImportFile::TYPE_APARTAMENT_RENT_XML; break;
            case 'house-sale':      $type_id = ImportFile::TYPE_HOUSE_SALE_XML; break;
            case 'house-rent':      $type_id = ImportFile::TYPE_HOUSE_RENT_XML; break;
            case 'commercial-sale': $type_id = ImportFile::TYPE_COMMERCIAL_SALE_XML; break;
            case 'commercial-rent': $type_id = ImportFile::TYPE_COMMERCIAL_RENT_XML; break;
          }
          $result['name'] = str_replace(' (XML)', '', ImportFile::$types[$type_id]);
          $this->paid_types[$type_id] = $result;
        }
      }
    }
    sfConfig::set('my_paid_types', $this->paid_types);
    $this->form = new ImportForm();

    if ($request->isMethod('post')) {
      $this->forward404Unless($request->hasParameter('import'));
      $file = $request->getFiles('import');
      $data = $request->getParameter('import');
      $this->form->bind($data, $file);
      if ($this->form->isValid()) {
        if ($this->form->save()) {
          if($this->form->getValue('url') != false) {
              $this->getUser()->setFlash(
              'import',
              'Источник добавлен в очередь на импорт'
            );
          }
          else {
            $this->getUser()->setFlash(
              'import',
              'Файл загружен и добавлен в очередь на импорт'
            );
          }
        }
        else {
          $this->getUser()->setFlash('import', 'Неправильный формат файла');
        }
      }
    }

    $import_logs_query = Doctrine::getTable('ImportLog')->createQuery('il')
      ->where('il.user_id = ?', $this->getUser()->id)
      ->orderBy('il.created_at desc');
    $this->pager = new sfDoctrinePager('ImportLog', 5);
    $this->pager->setQuery($import_logs_query);
    $this->pager->setPage($request->getParameter('page'), 1);
    $this->pager->init();
  }

  public function executeDeleteSourceLink (sfWebRequest $request)
  {
    $this->forward404Unless($this->getUser()->type == 'company');
    $this->forward404Unless($request->hasParameter('id'));

    $source_link = Doctrine::getTable('UserSourcesLink')->find($request->getParameter('id'));
    if ($source_link) {
      if($source_link->user_id == $this->getUser()->id) {
        $source_link->delete();
        $this->getUser()->setFlash('source_link', 'Источник объявлений был удален.');
      }
      else {
        $this->getUser()->setFlash('source_link', 'Источник объявлений не был удален.');
      }
    }
    $this->redirect('@import?action=importFile');
  }

  public function executeRestoreSourceLink (sfWebRequest $request)
  {
    $this->forward404Unless($this->getUser()->type == 'company');
    $this->forward404Unless($request->hasParameter('id'));

    $source_link = Doctrine::getTable('UserSourcesLink')->find($request->getParameter('id'));
    if ($source_link) {
      if($source_link->user_id == $this->getUser()->id) {
        $source_link->status = 'restored';
        $source_link->save();
        $this->getUser()->setFlash('source_link', 'Источник объявлений задействован.');
      }
      else {
        $this->getUser()->setFlash('source_link', 'Источник объявлений не был задействован.');
      }
    }
    $this->redirect('@import?action=importFile');
  }

  /*
   * Export errors to csv file
   */
  public function executeExportErrorsToCsv(sfWebRequest $request)
  {
    $this->forward404Unless($this->getUser()->type == 'company');
    $this->forward404Unless($request->hasParameter('id'));
    $log_id = $request->getParameter('id');

    $conn = Doctrine_Manager::connection();

    // check user
    $is_user_log = $conn->prepare(
      'select user_id from import_log where id = ?'
    );
    $is_user_log->execute(array($log_id));
    $user = $is_user_log->fetch();
    $this->forward404Unless($user['user_id'] == $this->getUser()->id);

    $errors = $conn->prepare(
      'select e.internal_lot_id, e.message, l.user_id
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

  public function executeGetHelp(sfWebRequest $request)
  {
    $this->forward404Unless($this->getUser()->type == 'company');
    $this->forward404Unless($request->isXmlHttpRequest());
    $this->forward404Unless($request->hasParameter('type'));
    $type = $request->getParameter('type');
    switch ($type) {
      case ImportFile::TYPE_APARTAMENT_SALE_XML:
        $type       = 'apartament-sale';
        $this->type = $type . '-xml';
        break;
      case ImportFile::TYPE_APARTAMENT_RENT_XML:
        $type       = 'apartament-rent';
        $this->type = $type . '-xml';
        break;
      case ImportFile::TYPE_HOUSE_SALE_XML:
        $type       = 'house-sale';
        $this->type = $type . '-xml';
        break;
      case ImportFile::TYPE_HOUSE_RENT_XML:
        $type       = 'house-rent';
        $this->type = $type . '-xml';
        break;
      case ImportFile::TYPE_COMMERCIAL_SALE_XML:
        $type       = 'commercial-sale';
        $this->type = $type . '-xml';
        break;
      case ImportFile::TYPE_COMMERCIAL_RENT_XML:
        $type       = 'commercial-rent';
        $this->type = $type . '-xml';
        break;
      case ImportFile::TYPE_APARTAMENT_SALE_CSV:
        $type         = 'apartament-sale';
        $this->type   = $type . '-csv';
        $this->fields = array(
          2, 54, 1, 3, 4, 5, 6, 7, 8, 9,
          10, 11, 12, 13, 14, 15, 20
        );
        break;
      case ImportFile::TYPE_APARTAMENT_RENT_CSV:
        $type         = 'apartament-rent';
        $this->type   = $type . '-csv';
        $this->fields = array(
          16, 68, 55, 1, 3, 4, 17, 18, 19, 66,
          5, 6, 7, 8, 9, 10, 11, 15, 12, 21
        );
        break;
      case ImportFile::TYPE_HOUSE_SALE_CSV:
        $type         = 'house-sale';
        $this->type   = $type . '-csv';
        $this->fields = array(
          2, 64, 26, 27, 30, 31, 32, 33, 34, 5, 28, 29, 35, 4, 36, 37,
          38, 39, 40, 41, 42, 67, 43, 44, 22
        );
        break;
      case ImportFile::TYPE_HOUSE_RENT_CSV:
        $type         = 'house-rent';
        $this->type   = $type . '-csv';
        $this->fields = array(
          16, 26, 27, 5, 28, 61, 60, 56, 57, 58, 59,
          18, 19, 35, 4, 36, 37, 43, 44, 23
        );
        break;
      case ImportFile::TYPE_COMMERCIAL_SALE_CSV:
        $type         = 'commercial-sale';
        $this->type   = $type . '-csv';
        $this->fields = array(
          2, 3, 4, 5, 24, 45, 46, 47, 48, 49, 50, 51, 52
        );
        break;
      case ImportFile::TYPE_COMMERCIAL_RENT_CSV:
        $type         = 'commercial-rent';
        $this->type   = $type . '-csv';
        $this->fields = array(
          53, 69, 3, 4, 5, 25, 45, 46, 48, 49, 50, 52, 63
        );
        break;
      default:
        $type       = 'apartament-sale';
        $this->type = $type . '-xml';
    }

    $this->formFields = Doctrine::getTable('FormField')
      ->createQuery('ff')
      ->leftJoin('ff.FormField f')
      ->where('ff.xml_name IS NOT NULL')
      ->andWhere('f.type = ?', $type)
      ->orderBy('f.position')
      ->execute();

    $this->setLayout(false);
  }

  public function executePay(sfWebRequest $request)
  {
    $user = $this->getUser();

    if ($user->type == 'company') {
      $this->types = $this->getPricesAndTypes();
      $this->invoices = Doctrine::getTable('ImportOrder')
              ->createQuery('o')
              ->select('o.id, o.created_at')
              ->where('o.user_id = ?', $this->getUser()->id)
              ->orderBy('o.created_at desc')
              ->execute(array(), Doctrine::HYDRATE_ARRAY);

      $this->data_form = new ImportOrderDataForm();
      $this->last_params = array();

      if($request->isMethod('post')){
        $params =  $request->getPostParameters();
        $data   = $params['data'];
        if (!empty($data['type'])) {
          $types   = $data['type'];
          $this->last_params = array_merge($types, $this->last_params);
        }

        if (empty($types) || count($types) === 0) {
          $this->errors = 'Вы не выбрали ни одного типа файла для оплаты';
        }else {
          $total = 0;
          foreach($this->types as $type){
            if(in_array($type['type'], $types)){
              $total += $type['price'];
            }
          }
        }

        $this->data_form->bind($data);
        if ($this->data_form->isValid() && empty($this->errors)) {
          $order = new ImportOrder();
          $order->date_from     = implode('-', array_reverse(explode('.',$this->data_form->getValue('start')))) . " 00:00:00";
          $order->date_to       = date("Y-m-d H:i:s",  strtotime("+1 month", strtotime($order->date_from)));
          $order->created_at    = date("Y-m-d H:i:s");
          $order->user_id       = $user->id;
          $order->company_name  = $this->data_form->getValue('fullname');
          $order->info = serialize($data);
          $order->total = $total;
          if($order->save()) {
            foreach($this->types as $type){
              if(in_array($type['type'], $types)){
                $opt = new ImportOrderOptions;
                $opt->order_id = $order->id;
                $opt->type = $type['type'];
                $opt->save();
              }
            }

            $flash = '<a id="billsuccess" href="/import/invoice?id=' . $order->id . '" target="_blank">Счет №' . $order->id . '</a> успешно создан!';
            $flash .= '<script type="text/javascript">
              <!--// <![CDATA[
                $(function() {
                  $("html, body").animate({
                      scrollTop: ($("#billsuccess").offset().top - 65)
                  }, 1000);
                });
              // ]]> -->
            </script>';
            $user->setFlash('import', $flash);
          }
        }
      }
    }
  }

  public function executeInvoice(sfWebRequest $request)
  {
    $this->types = $this->getPricesAndTypes();
    $this->invoice = Doctrine::getTable('ImportOrder')->find($request->getParameter('id'));
    $this->formatted_id = '00' . ($this->invoice->id + 1000);
    $this->options = Doctrine::getTable('ImportOrderOptions')
            ->createQuery()
            ->andWhere('order_id = ?', $this->invoice->id)
            ->execute(array(), Doctrine::HYDRATE_ARRAY);
    $this->forward404If($this->getUser()->id != $this->invoice->user_id);
    $this->setLayout(false);
  }

  protected function getPricesAndTypes()
  {
    $app_config = sfYaml::load(sfConfig::get('sf_root_dir') . '/config/import_prices.yml');
    return array(
      'apartament-sale' => array(
        'type' => 'apartament-sale',
        'name' => 'Продажа квартир',
        'price' => $app_config['all']['apartament-sale'],
      ),
      'apartament-rent' => array(
        'type' => 'apartament-rent',
        'name' => 'Аренда квартир',
        'price' => $app_config['all']['apartament-rent'],
      ),
      'house-sale' => array(
        'type' => 'house-sale',
        'name' => 'Продажа домов',
        'price' => $app_config['all']['house-sale'],
      ),
      'house-rent' => array(
        'type' => 'house-rent',
        'name' => 'Аренда домов',
        'price' => $app_config['all']['house-rent'],
      ),
      'commercial-sale' => array(
        'type' => 'commercial-sale',
        'name' => 'Продажа коммерческой недвижимости',
        'price' => $app_config['all']['commercial-sale'],
      ),
      'commercial-rent' => array(
        'type' => 'commercial-rent',
        'name' => 'Аренда коммерческой недвижимости',
        'price' => $app_config['all']['commercial-rent'],
      ),
    );
  }
}
