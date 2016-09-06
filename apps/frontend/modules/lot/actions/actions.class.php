<?php

/**
 * lot actions.
 *
 * @package    domus
 * @subpackage lot
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class lotActions extends sfActions
{
  public function postExecute() {
    MetaParse::setMetas($this);
  }

  public function executeAdd(sfWebRequest $request)
  {
    $type_info = sfConfig::get('app_lot_types');
    $types = array_keys($type_info);
    $show_contacts = false;
    if ($post = $request->getParameter('dynamicform')) {
      $request->setParameter('type', $post['type']);
    }

    if (in_array($this->getUser()->type, array('company', 'source'))
        || $this->getUser()->hasCredential('moder-access')) {
      $show_contacts = true;
    }

    $form = new DynamicForm(
      $request->getParameter('type'),
      $show_contacts
    );

    if ($request->isMethod('post')) {
      $form->bind($request->getParameter('dynamicform', array()));
      if ($form->isValid())
      {
        if ($request->getParameter('validate')) {
          return $this->renderText(json_encode(array('valid' => true)));
        }
        else {
          $id = $form->save();
          $user = $this->getUser();
          $user->active_count++;
          $user->save();

          $this->getUser()->setFlash(
            'lot_success',
            sprintf('Ваше объявление добавлено. В поиске появится в течение 6 часов #share-%d#', $id)
          );
          $this->redirect('lot/my');
        }
      }
      elseif ($request->getParameter('validate')) {
        return $this->renderText(json_encode($form->getErrorsArray()));
      }
    }

    $this->form = $form;
  }

  public function executeEdit(sfWebRequest $request)
  {
    $type_info = sfConfig::get('app_lot_types');
    $types = array_keys($type_info);
    $show_contacts = false;
    $id = $request->getParameter('id');
    $params = $request->getParameter('dynamicform');
    if (!empty($params['id'])) {
      $id = $params['id'];
    }
    $lot = Doctrine::getTable('Lot')
      ->getRestrictedToUser(
        $id,
        Doctrine::getTable('Lot')->createQuery()
          ->leftJoin('Lot.LotInfo f')
          ->leftJoin('f.FormField')
      );

    $this->forward404Unless($lot);
    if ($lot->user_id != $this->getUser()->id) {
      $this->forward404Unless($this->getUser()->hasCredential('moder-actions'));
    }

    if (in_array($this->getUser()->type, array('company', 'source'))
        || $this->getUser()->hasCredential('moder-access')) {
      $show_contacts = true;
    }
    $form = new DynamicForm(
      $lot->type,
      $show_contacts
    );

    if ($request->isMethod('post')) {
      $form->bind($request->getParameter('dynamicform'));
      if ($form->isValid())
      {
        if ($request->getParameter('validate')) {
          return $this->renderText(json_encode(array('valid' => true)));
        }
        else {
          $id = $form->save();

          $this->getUser()->save();
          $this->getUser()->setFlash(
            'lot_success',
            sprintf('Объявление обновлено. #share-%d#', $id)
          );
          $this->redirect($request->getParameter('referrer') ? $request->getParameter('referrer') : 'lot/my');
        }
      }
      elseif ($request->getParameter('validate')) {
        return $this->renderText(json_encode($form->getErrorsArray()));
      }
    }
    else {
      $form->bindFromObject($lot);
    }

    $this->form = $form;
  }

  public function executeFavourite(sfWebRequest $request) {
    $user = $this->getUser();

    if ($request->hasParameter('id')) {
      $lot = Doctrine::getTable('Lot')->createQuery()
        ->andWhere('id = ?', $request->getParameter('id'))->fetchOne();
      if ($lot) {
        $user->toggleLotFavourite($lot);
      }

      if ($request->isXmlHttpRequest()) {
        return $this->renderText('ok');
      }
      else {
        $this->redirect($request->getReferer());
      }
    }

    $filterForm = new MyLotsFilterForm();
    if ($request->hasParameter('filter')) {
      $filter = array();
      $filterForm->bind($request->getParameter('filter'));

      if ($filterForm->isValid()) {
        foreach ($filterForm->getValues() as $name => $value) {
          if ($value != '') {
            $filter[$name] = $value;
          }
        }
        $user->setAttribute('lot-favourite-filter', $filter);
      }
    }
    else {
      $filterForm->bind($user->getAttribute('lot-favourite-filter', array()));
    }

    $query = Doctrine::getTable('Lot')->createQuery()
        ->innerJoin('Lot.Favourite f with f.user_id = ?', $user->id);
    foreach ($user->getAttribute('lot-favourite-filter', array()) as $name => $value) {
      $query->andWhere("$name = ?", $value);
    }

    $this->pager = new sfDoctrinePager('Lot', sfConfig::get('app_lot_my_max_per_page'));
    $this->pager->setQuery($query);
    $this->pager->setPage($request->getParameter('page', 1));
    $this->pager->init();

    $this->filterForm = $filterForm;
  }

  public function executeNotify(sfWebRequest $request) {
    $this->forward404Unless($request->hasParameter('id'));

    $form = new NotificationForm();
    $data = array(
      'model' => 'Lot',
      'field' => 'price',
      'pk'    => $request->getParameter('id'),
      'email' => $this->getUser()->email
    );
    $form->setDefaults($data);

    if ($request->isMethod('post'))
    {
      $data['email'] = $request->getParameter('email', $data['email']);
      $data['period'] = $request->getParameter('period');
      $form->bind($data);

      if ($form->isValid()) {
        $form->save();
        $msg = 'Вы успешно подписались на обновления';
        if ($request->isXmlHttpRequest()) {
          return $this->renderText(json_encode(array('valid' => true, 'message' => $msg)));
        }
        else {
          $this->getUser()->setFlash('subscribed_success', $msg);
          $this->redirect('@homepage');
        }
      }
      else {
        return $this->renderText(json_encode($form->getErrorsArray()));
      }
    }

    $this->form = $form;
  }

  public function executeClaim(sfWebRequest $request) {
    $this->forward404Unless($request->isXmlHttpRequest());
    $this->forward404Unless($request->hasParameter('id'));
    $user = $this->getUser();
    $form = new ClaimForm();
    $data = array(
      'lot_id' => $request->getParameter('id')
    );
    $form->setDefaults($data);

    if ($request->isMethod('post'))
    {
      $data = $request->getPostParameter('claim');
      if($user->isAuthenticated()) $data['user_id'] = $this->getUser()->id;

      $form->bind($data);
      if ($form->isValid()) {

        $form->save();
        $msg = 'Вы успешно пожаловались на объявление';
        if ($request->isXmlHttpRequest()) {
          return $this->renderText(json_encode(array('valid' => true, 'callback' => array('name' => 'claim_off'))));
        }
        else {
          $this->getUser()->setFlash('subscribed_success', $msg);
          $this->redirect('@homepage');
        }
      }
      else {
        return $this->renderText(json_encode($form->getErrorsArray()));
      }
    }

    $this->form = $form;
  }

  public function executeMy(sfWebRequest $request) {
    $user = $this->getUser();
    $filterForm = new MyLotsFilterForm();

    if ($request->hasParameter('filter')) {
      $filter = array();
      $filterForm->bind($request->getParameter('filter'));

      if ($filterForm->isValid()) {
        foreach ($filterForm->getValues() as $name => $value) {
          if ($value != '') {
            $filter[$name] = $value;
          }
        }
        $user->setAttribute('lot-my-filter', $filter);
      }
    }
    else {
      $filterForm->bind($user->getAttribute('lot-my-filter', array()));
    }

    $query = Doctrine::getTable('Lot')->createQuery()->andWhere('user_id = ?', $user->id)->orderBy('id DESC');
    foreach ($user->getAttribute('lot-my-filter', array()) as $name => $value) {
      $query->andWhere("$name = ?", $value);
    }

    $this->pager = new sfDoctrinePager('Lot', sfConfig::get('app_lot_my_max_per_page'));
    $this->pager->setQuery($query);
    $this->pager->setPage($request->getParameter('page', 1));
    $this->pager->init();

    $this->filterForm = $filterForm;
  }


  public function executeShow(sfWebRequest $request) {

    try {
      $this->lot = $this->getRoute()->getObject();
      if ('show_lot' == $this->getContext()->getRouting()->getCurrentRouteName() && $this->lot->slug) {
        $this->redirect($this->generateUrl('show_lot_slug', $this->lot), 301);
      }
    }
    catch (sfError404Exception $e) {
      if ($slug = $request->getParameter('slug')) {
        preg_match('/.*?-(\d+)$/',$slug, $matches);

        if (isset($matches[1])) {
          $this->lot = Doctrine::getTable('Lot')->find($matches[1]);
          if ($this->lot && $this->lot->slug) {
            $this->redirect($this->generateUrl('show_lot_slug', $this->lot));
          }
        }
      }
    }
    
    $this->forward404Unless($this->lot);

    sfConfig::set('banner', 'lot');

    //New slug #9815
    if (!$request->hasParameter('type')){
      $request->setParameter('type', $this->lot->type);
    }

    $request->setParameter('current_type', $this->lot->type);
    if (!$this->getUser()->getFlash('region_changed')) {
      $this->getUser()->current_region = $this->lot->Region;
      $this->getResponse()->setCookie('current_region', $this->lot->Region->id);
    }

    //no index trash info, use only lot links and sitemap
    if (strtotime($this->lot->created_at) > strtotime('2011-03-01 00:00:00')) {
      sfConfig::set('lot_noindex', true);
    }
  }

  public function executeSpecial(sfWebRequest $request) {
    $this->lot = Doctrine::getTable('Lot')->find($request->getParameter('id'));
    $this->forward404Unless($this->lot);
    sfConfig::set('homepage', true);
    sfConfig::set('all_banners', true);
    sfConfig::set('no_top_spec_banners', true);
  }


  public function executeSetactive(sfWebRequest $request) {
    $lot = Doctrine::getTable('Lot')->getRestrictedToUser($request->getParameter('id'));
    $this->forward404Unless($lot && ($lot->status == 'inactive' || $this->getUser()->hasCredential('moder-actions')));

    if ($lot->user_id != $this->getUser()->id) {
      $this->forward404Unless($this->getUser()->hasCredential('moder-actions'));
    }
    $lot->moderator_message = NULL;
    $lot->activate()->save();
    $lot->User->setRegion($lot->region_id);

    $period = sfConfig::get('app_lot_periods');
    $type = explode('-', $lot->type);
    $periods = array_keys($period[$type[1]]);
    $this->getUser()->setFlash('lot_success', sprintf('Объявление активировано еще на %d дней.', $periods[0]));

    if ($request->isXmlHttpRequest()){
      return $this->renderText(json_encode(array('save' => true)));
    }
    else {
      $redirect_to = $request->getReferer();
      if (strpos($redirect_to, '/') !== 0 && !strpos($redirect_to, sfConfig::get('app_site') . '/')) {
        $redirect_to = 'lot/my';
      }
      $this->redirect($redirect_to);
    }
  }

  public function executeSetinactive(sfWebRequest $request) {
    $lot = Doctrine::getTable('Lot')->getRestrictedToUser($request->getParameter('id'));
    $this->forward404Unless($lot && $lot->status == 'active');

    $lot->deactivate()->save();
    $lot->User->deleteRegion($lot->region_id);

    $this->getUser()->setFlash('lot_success', 'Объявление деактивировано.');
    if ($request->isXmlHttpRequest()){
      return $this->renderText(json_encode(array('save' => true)));
    }
    else {
      $this->redirect($request->getReferer());
    }
  }

  public function executeRestore(sfWebRequest $request) {
    $lot = Doctrine::getTable('Lot')->getRestrictedToUser($request->getParameter('id'));
    $this->forward404Unless($lot && $this->getUser()->hasCredential('moder-delete'));
    if ($lot->user_id != $this->getUser()->id) {
      $this->forward404Unless($this->getUser()->hasCredential('moder-delete'));
    }
    $lot->moderator_message = NULL;
    $lot->deleted_at = NULL;
    $lot->status = 'restricted';
    $lot->save();

    $this->getUser()->setFlash('lot_success', 'Объявление восстановлено. Необходимо его отредактировать и активировать.');

    if ($request->isXmlHttpRequest()){
      return $this->renderText(json_encode(array('save' => true)));
    }
    else {
      $this->redirect($request->getReferer());
    }
  }

  public function executeDelete(sfWebRequest $request) {
    $lot = Doctrine::getTable('Lot')->getRestrictedToUser($request->getParameter('id'));
    $this->forward404Unless($lot);
    if ($lot->user_id != $this->getUser()->id) {
      $this->forward404Unless($this->getUser()->hasCredential('moder-delete'));
    }

    $lot->delete();
    $lot->User->deleteRegion($lot->region_id);

    $this->getUser()->setFlash('lot_success', 'Объявление удалено.');

    if ($request->isXmlHttpRequest()){
      return $this->renderText(json_encode(array('save' => true)));
    }
    else {
      $this->redirect($request->getReferer());
    }
  }

  public function executeRestrict(sfWebRequest $request) {
    $lot = Doctrine::getTable('Lot')->getRestrictedToUser($request->getParameter('id'));
    $this->forward404Unless($lot);

    $lot->restrict()->save();
    $lot->User->deleteRegion($lot->region_id);

    $this->getUser()->setFlash('lot_success', 'Объявление запрещено к показу.');
    if ($request->isXmlHttpRequest()){
      return $this->renderText(json_encode(array('save' => true)));
    }
    else {
      $this->redirect($request->getReferer());
    }
  }


  public function executeInfowindow(sfWebRequest $request) {
    $this->lots = Doctrine::getTable('Lot')->createQueryActive()
      ->leftJoin('Lot.User')
      ->andWhereIn('id', explode(',', $request->getParameter('id')))
      ->execute();
    $this->forward404Unless(count($this->lots));
  }
  public function executeInfowindowmoderate(sfWebRequest $request) {
     $this->lots = Doctrine::getTable('Lot')->createQuery()
      ->leftJoin('Lot.User')
      ->andWhereIn('id', explode(',', $request->getParameter('id')))
      ->execute();
    $this->forward404Unless(count($this->lots));
  }
  public function executePhone(sfWebRequest $request) {
    $lot = Doctrine::getTable('Lot')->createQuery('l')
        ->leftJoin('l.User u')
        ->andWhere('l.id = ?', $request->getParameter('id'))
        ->andWhere('l.status = ?', 'active')
        ->fetchOne();
    $this->forward404Unless($lot);

    $lot_view = new LotView();
    $lot_view->lot_id = $lot->id;
    $lot_view->ip_address = $request->getRemoteAddress();
    $lot_view->lot_type = $lot->type;
    $lot_view->save();

    if ($lot->organization_contact_phone) {
      $text = '<strong>' . $lot->organization_contact_phone . '</strong>';
    }
    else {
      $text = '';
    }

    if (!$lot->User->is_inner && !$lot->organization_contact_phone) {
      $text .= '<strong>' . $lot->User->phone .'</strong>';
    }


    return $this->renderText(
      $text
      . "<p class='telcomm'>Пожалуйста, сообщите специалисту, что Вы нашли это объявление на сайте Место.ру.</p>"
    );
  }


  public function executeModerate(sfWebRequest $request) {
    $user = $this->getUser();
    $filterForm = new ModerateFilterForm();

    if ($request->hasParameter('filter')) {
      $filter = array();
      $filterForm->bind($request->getParameter('filter'));

      foreach ($filterForm->getValues() as $name => $value) {
        if ($value != '') {
          $filter[$name] = $value;
        }
      }

      $user->setAttribute('moderator-filters', $filter);
    }
    else {
      if (!$user->hasAttribute('moderator-filters')) {
        $filter = $filterForm->getDefaults();
      }
      else {
        $filter = $user->getAttribute('moderator-filters');
      }
      $filterForm->bind($filter);

    }
    if ($request->hasParameter('do')) {
      $this->forward404If($request->getParameter('do') == 'delete' && !$this->getUser()->hasCredential('moder-delete'));
      $this->forward404If($request->getParameter('do') != 'delete' && !$this->getUser()->hasCredential('moder-actions'));

      $lots = Doctrine::getTable('Lot')->getRestrictedToUser($request->getParameter('id'));
      if ($lots) {
        foreach ($lots as $lot) {
          switch ($request->getParameter('do')) {
            case 'activate':
              $lot->activate()->save();
              break;
            case 'restrict':
              $lot->restrict()->save();
              break;
            case 'restrict_send_message':
              break;
            case 'delete':
              $lot->delete();
              break;
          }
        }
        $this->getUser()->setFlash('lot_success', 'Действие успешно выполнено.');
        if ($request->isXmlHttpRequest()){
          return $this->renderText(json_encode(array('save' => true)));
        }
        else {
          $this->redirect($request->getReferer());
        }
      }
    }
    $query = Doctrine::getTable('Lot')->createQuerySlave();
    $where_query = array();
    foreach ($filter as $name => $value) {
      switch ($name) {
        case 'id':
        case 'region_id':
        case 'type':
        case 'status':
          if ($value != 'deleted') {
            $query->andWhere("$name = ?", $value);
          }
          else {
            $query->andWhere('Lot.deleted_at IS NOT NULL');
          }
          break;
        case 'address':
          $query->andWhere('address1 like ? or address2 like ?', array("%,%$value%", "%$value%"));
          break;
        case 'username':
          $query->andWhere('Lot.User.name like ? or Lot.User.company_name like ?', array("%$value%", "%$value%"));
          break;
        case 'email':
          $query->andWhere('Lot.User.email like ?', $value);
          break;
        case 'created_at_from':
          $value = strtotime($value);
          $date = date('Y-m-d', $value).'%';
          $query->andWhere("created_at >= ?", array($date));
          break;
         case 'created_at_to':
          $value = strtotime($value);
          $date = date('Y-m-d', $value).'%';
          $query->andWhere("created_at <= ?", array($date));
          break;
        case 'active_till_from':
          $value = strtotime($value);
          $date = date('Y-m-d', $value).'%';
          $query->andWhere("active_till >= ?", array($date));
          break;
         case 'active_till_to':
          $value = strtotime($value);
          $date = date('Y-m-d', $value).'%';
          $query->andWhere("active_till <= ?", array($date));
          break;
        case 'description':
          $query->andWhere("$name like ?", array("%" . $value . "%"));
          break;
        case 'phone':
          $query->andWhere(
            "Lot.User.phone like ? or Lot.description like ?",
            array("%" . $value . "%", "%" . $value . "%")
          );
          break;

        case 'num_rooms':
          if (isset($filter['type'])){
            if ($filter['type'] == 'apartament-sale'){
              $where_query[54] = array(
                'fq.field_id = ? and fq.value like ?',
                array('54', '%' . $value . '%')
              );
              break;
            }
            if ($filter['type'] == 'apartament-rent'){
              $where_query[55] = array(
                'fq.field_id = ? and fq.value like ?',
                array('55', '%' . $value . '%')
              );
               break;
            }
          }
          break;

        case 'area_from':
          if (isset($filter['type'])){
            if ($filter['type'] == 'apartament-sale' || $filter['type'] == 'apartament-rent'){
              if (isset($filter['area_to'])){
                $where_query[1] = array(
                  'fq.field_id = ? and fq.value + 0 >= ? and fq.value + 0 <= ?',
                  array('1', $value, $filter['area_to'])
                );
              }
              else {
                $where_query[1] = array(
                  'fq.field_id = ? and fq.value + 0 >= ?',
                  array('1', $value)
                );
              }
            }
            elseif ($filter['type'] == 'house-sale' || $filter['type'] == 'house-rent'){
              if (isset($filter['area_to'])){
                $where_query[26] = array(
                  'fq.field_id = ? and fq.value + 0 >= ? and fq.value + 0 <= ?',
                  array('26', $value, $filter['area_to'])
                );
              }
              else {
                $where_query[26] = array(
                  'fq.field_id = ? and fq.value + 0 >= ?',
                  array('26', $value)
                );
              }
            }
          }
          break;

        case 'area_to':
          if (isset($filter['type'])){
            if ($filter['type'] == 'apartament-sale' || $filter['type'] == 'apartament-rent'){
              if (!isset($filter['area_from'])){
                $where_query[1] = array(
                  'fq.field_id = ? and fq.value + 0 <= ?',
                  array('1', $value)
                );
              }else {
                break;
              }
            }
            elseif ($filter['type'] == 'house-sale' || $filter['type'] == 'house-rent'){
              if (!isset($filter['area_from'])){
                $where_query[26] = array(
                  'fq.field_id = ? and fq.value + 0 <= ?',
                  array('26', $value)
                );
              }else {
                break;
              }
            }
          }
          break;

        case 'area_country_from':
          if (isset($filter['type'])){
            if ($filter['type'] == 'house-sale' || $filter['type'] == 'house-rent'){
              if (isset($filter['area_country_to'])){
                $where_query[27] = array(
                  '(fq.field_id = ? and fq.value + 0 >= ? and fq.value + 0 <= ?) or (fq.field_id = 26 and fq.value <> NULL)',
                  array('27', $value, $filter['area_country_to'])
                );
              }
              else {
                $where_query[27] = array(
                  '(fq.field_id = ? and fq.value + 0 >= ?) or (fq.field_id = 26 and fq.value <> NULL)',
                  array('27', $value)
                );
              }
            }
          }
          break;

        case 'area_country_to':
          if (isset($filter['type'])){
            if ($filter['type'] == 'house-sale' || $filter['type'] == 'house-rent'){
              if (!isset($filter['area_country_from'])){
                $where_query[27] = array(
                  '(fq.field_id = ? and fq.value + 0 <= ?) or (fq.field_id = 26 and fq.value <> NULL)',
                  array('27', $value)
                );
              }else {
                break;
              }
            }
          }
          break;

        case 'price_from':
          //на будущее, может будем использовать в модерации не только RUR
          $currency = 'RUR';
          if (isset($filter['price_to'])){
            $query->andWhere('price * exchange >= ? and price * exchange <= ?',
              array(
                Currency::convert($value, $currency, 'RUR'),
                Currency::convert($filter['price_to'], $currency, 'RUR')
            ));
          }
          else {
            $query->andWhere('price * exchange >= ?',
              Currency::convert($value, $currency, 'RUR')
            );
          }
          break;
        case 'price_to':
          //на будущее, может будем использовать в модерации не только RUR
          $currency = 'RUR';
          if (!isset($filter['price_from'])){
            $query->andWhere('price * exchange <= ?',
              Currency::convert($value, $currency, 'RUR')
            );
          }
          break;
        case 'coords':
          if (isset($filter['map_search']) && $filter['map_search'] == 'on'){
            if ($value != ''){
              preg_match_all('/\d+\.\d+/', $value, $coords);
              if (is_array($coords)){
                $query->andWhere('Lot.latitude between ? and ?', array($coords[0][0], $coords[0][2]));
                $query->andWhere('Lot.longitude between ? and ?', array($coords[0][1], $coords[0][3]));
              }
            }
          }
          break;
      }
    }
    if (!isset($filter['status']) || $filter['status'] != 'deleted'){
       $query->andWhere('Lot.deleted_at = ? OR Lot.deleted_at IS NULL', array(0));
    }

    if (count($where_query)){
      $query->leftJoin('Lot.LotInfo fq');
      $query_parts = $query_params = array();
      foreach ($where_query as $id => $value){
        $clause = $value[0];
        if (count($id) > 0){
          $clause = '(' . $clause .')';
        }
        $query_parts[] = $clause;
        if (isset($value[1])){
          $query_params = array_merge($query_params, $value[1]);
        }
      }
      $query_parts = implode(' or ', $query_parts);

      $query->andWhere($query_parts, $query_params);
      $query->groupBy('Lot.id');
      $query->addHaving('count(Lot.id) >= ?', count($where_query));
    }
    if (isset($filter['usertype1']) || isset($filter['usertype2'])
      || isset($filter['usertype3']) || isset($filter['usertype4'])
      || isset($filter['usertype5'])) {

      $user_filters = array();

      if (isset($filter['usertype1'])) {
        $user_filters[] = 'company';
      }
      if (isset($filter['usertype2'])) {
        $user_filters[] = 'employee';
      }
      if (isset($filter['usertype3'])) {
        $user_filters[] = 'realtor';
      }
      if (isset($filter['usertype4'])) {
        $user_filters[] = 'owner';
      }
      if (isset($filter['usertype5'])) {
        $user_filters[] = 'source';
      }
      $query->andWhereIn('Lot.User.type', $user_filters);
    }
    //sorting
    if (isset($filter['sort'])){
      switch ($filter['sort']) {
        case 'id-desc':
          $query->orderBy('id desc');
          break;
        case 'id-asc':
          $query->orderBy('id asc');
          break;
        case 'price-desc':
          $query->orderBy('price desc');
          break;
        case 'price-asc':
          $query->orderBy('price asc');
          break;
        case 'address-desc':
          $query->orderBy('address1 desc, address2 desc');
          break;
        case 'address-asc':
          $query->orderBy('address1 asc, address2 asc');
          break;
        case 'created_at-desc':
          $query->orderBy('created_at desc');
          break;
        case 'created_at-asc':
          $query->orderBy('created_at asc');
          break;
        case 'active_till-asc':
          $query->orderBy('active_till asc');
          break;
        case 'active_till-desc':
          $query->orderBy('active_till desc');
          break;
        case 'email-desc':
          $query->orderBy('Lot.User.email desc');
          break;
        case 'email-asc':
          $query->orderBy('Lot.User.email asc');
          break;
        case 'type-desc':
          $query->orderBy('type desc');
          break;
        case 'type-asc':
          $query->orderBy('type asc');
          break;
      }
    }
    else {
      $query->orderBy('id desc');
    }


    if (!($per_page = $filterForm->getValue('per-page'))) {
      $per_page = 10;
    }
    $this->pager = new sfDoctrinePager('Lot', $per_page);
    $this->pager->setQuery($query);
    $this->pager->setPage(isset($filter['page']) ? $filter['page'] : 1);
    $this->pager->init();

    $this->filterForm = $filterForm;
  }

  public function executeLotscount(sfWebRequest $request)
  {
    $this->forward404Unless($request->hasParameter('type'));
    $type = $request->getParameter('type');
    if ($request->hasParameter('only_count')){
      $this->only_count = true;
    }
    else {
      $this->only_count = false;
    }
    $user = $this->getUser();
    $cache = new DomusCache(array(
      'prefix'    => 'lots_',
      'lifetime'  => 2000,
    ));

    $key = sprintf ('count_%s_%s', $type, $user->current_region->id);
    if ($cache->has($key)){
      $lots = unserialize($cache->get($key));
      $this->lots_nb = $lots['lots_nb'];
    }
    else {
      $this->lots_nb = Doctrine_Query::create()
        ->from('Lot l')
        ->select('COUNT(l.id) as count')
        ->andWhere('l.type = ?', $type)
        ->andWhere('l.region_id = ?', $this->getUser()->current_region->id)
        ->andWhere('l.status = ?', 'active')
        ->setHydrationMode(Doctrine::HYDRATE_NONE)
        ->fetchOne();
      $this->lots_nb = $this->lots_nb[0];

      $cache->set($key, serialize(array('lots_nb' => $this->lots_nb)));
    }
  }

  public function executeAddFlat(sfWebRequest $request)
  {
    $d = new DynamicForm('new_building-sale');
    $fname = rand();
    $d->addEntity('Flat',$fname);
    return $this->renderPartial('flat', array('add_button'=> true, 'flat' => $d['flats'][$fname]));
  }

  public function executeAddCottage(sfWebRequest $request)
  {
    $d = new DynamicForm('cottage-sale');
    $fname = rand();
    $d->addEntity('Cottage',$fname);
    return $this->renderPartial('cottage', array('add_button'=> true, 'cottage' => $d['cottages'][$fname]));
  }
  
  public function executeAddTownhouse(sfWebRequest $request)
  {
    $d = new DynamicForm('cottage-sale');
    $fname = rand();
    $d->addEntity('Townhouse',$fname);
    return $this->renderPartial('townhouse', array('add_button'=> true, 'townhouse' => $d['townhouses'][$fname]));
  }
}
