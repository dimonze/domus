<?php

require_once dirname(__FILE__) . '/../lib/userGeneratorConfiguration.class.php';
require_once dirname(__FILE__) . '/../lib/userGeneratorHelper.class.php';

/**
 * user actions.
 *
 * @package    domus
 * @subpackage user
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12474 2008-10-31 10:41:27Z fabien $
 */
class userActions extends autoUserActions
{

  protected function executeBatchDelete(sfWebRequest $request)
  {
    $ids = $request->getParameter('ids');

    $users = Doctrine_Query::create()
        ->from('User')
        ->whereIn('id', $ids)
        ->execute();

    foreach ($users as $user) {
      $user->delete();
    }
    $this->getUser()->setFlash('notice', 'The selected items have been deleted successfully.');

    $this->redirect('@user');
  }

  public function executeRestore(sfWebRequest $request)
  {
    $conn = Doctrine_Manager::getInstance()->connection();
    $id = $request->getParameter('id');

    $stmt = $conn->prepare('select email, phone from user where id = ?');
    $stmt->execute(array($id));
    list($email, $phone) = $stmt->fetch(Doctrine::FETCH_NUM);

    $stmt = $conn->prepare('select count(*) from user where email = ? or phone = ?');
    $stmt->execute(array($email, $phone));
    $nb = $stmt->fetch(Doctrine::FETCH_COLUMN);

    if (1 == $nb) {
      $conn->execute('update user set deleted_at = ? where id = ?', array(null, $id));
      $this->getUser()->setFlash('notice', 'Пользователь был восстановлен');
    }
    else {
      $this->getUser()->setFlash('error', 'Уже зарегистрирован другой пользователь с такой почтой/телефоном');
    }

    $this->redirect($request->getReferer());
  }

  public function executeIndex(sfWebRequest $request)
  {
    // sorting
    if ($request->getParameter('sort')) {
      if ($this->isValidSortColumn($request->getParameter('sort'))
        || $request->getParameter('sort') == 'active_count') {
        $this->setSort(array($request->getParameter('sort'), $request->getParameter('sort_type')));
      }
    }

    // pager
    if ($request->getParameter('page')) {
      $this->setPage($request->getParameter('page'));
    }

    $this->pager = $this->getPager();
    $this->sort = $this->getSort();
    $users = Doctrine::getTable('User')->createQuery('u')->select('u.id')->execute(array(), Doctrine::HYDRATE_ARRAY);
    $this->users = array();
    foreach ($users as $user) {
      $this->users[] = $user['id'];
    }
    $this->users = json_encode($this->users);
  }

  public function executePromote(sfWebRequest $request)
  {
    if ($request->isXmlHttpRequest()) {
      $user = Doctrine::getTable('User')->find((int) $request->getParameter('id'));
      $this->forward404Unless($user);

      $app_config = sfYaml::load(sfConfig::get('sf_apps_dir') . '/frontend/config/app.yml');
      $promotion = $app_config['all']['user']['promotion'];

      $user->Info->promotion++;
      $user->rating += $promotion;

      $user->save();
      $this->renderText($user->rating);
    }

    return sfView::NONE;
  }

  public function executeUnpromote(sfWebRequest $request)
  {
    if ($request->isXmlHttpRequest()) {
      $user = Doctrine::getTable('User')->find((int) $request->getParameter('id'));
      $this->forward404Unless($user);

      $app_config = sfYaml::load(sfConfig::get('sf_apps_dir') . '/frontend/config/app.yml');
      $promotion = $app_config['all']['user']['promotion'];

      if (!$user->rating) {

        $user->Info->promotion--;
      }

      $new_rate = $user->rating - $promotion;
      if ($new_rate < 0)
        $new_rate = 0;

      $user->rating = $new_rate;

      $user->save();
      $this->renderText($user->rating);
    }

    return sfView::NONE;
  }

  public function executeFilter(sfWebRequest $request) {
    $user_filters = $request->getParameterHolder()->get('user_filters', null);
    $deleted_at = array(
          'from' => array(
              'year' => '',
              'month' => '',
              'day' => ''
          ),
          'to' => array(
              'year' => '',
              'month' => '',
              'day' => ''
          )
      );
        
    if(isset($user_filters['deleted']) && $user_filters['deleted']){
      $deleted_at = array(
          'from' => array(
              'year' => 1900,
              'month' => 1,
              'day' => 1
          ),
          'to' => array(
              'year' => date('Y'),
              'month' => date('m'),
              'day' => date('d')
          )
      );
    }
    
    $user_filters['deleted_at'] = $deleted_at;
    $request->getParameterHolder()->set('user_filters', $user_filters);
    
    parent::executeFilter($request);
  }
}
