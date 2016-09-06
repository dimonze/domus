<?php

/**
 * menu components.
 *
 * @package    domus
 * @subpackage menu
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class menuComponents extends sfComponents
{
  public function executeDefault()
  { }

  public function executeMain()
  {
    // TODO fix this!
    // $items = sfConfig::get('app_lot_types');
    // $current_type = sfContext::getInstance()->getRequest()->getParameter('current_type');
    // foreach ($items as $key => $names) {
    //   if ($key == $current_type) {
    //     $items[$key]['active'] = true;
    //   }
    //   $items[$key]['hasLots'] = $this->getUser()->current_region->hasLots($key) ? true : false;
    // }
    // $this->items = $items;
  }

  public function executeFooter()
  {
    $this->pages = Doctrine::getTable('Page')->createQuery()
      ->andWhere('in_menu = ?', true)
      ->andWhere('parent_id is null')
      ->execute();
  }

  public function executeUser()
  {
    if (!sfContext::getInstance()->getUser()->isAuthenticated()) {
      return sfView::NONE;
    }
    $this->cache_prefix = sprintf('homepage_%d_%d_',
        $this->request->getCookie('js_on'),
        $this->getUser()->current_region->id);
  }

  public function executeUsercard()
  {
    $user = $this->getRequestParameter('user_card');
    if (!($user instanceOf User)) {
      return sfView::NONE;
    }
    
    $current_route = sfContext::getInstance()->getRouting()->getCurrentInternalUri(true);
    $menu = array(
      'user' => array(
        array(
          'name'   => $user->type == 'company' ? 'Информация о компании' : 'Персональные данные',
          'url'    => '@user_card?id=' . $user->id,
          'active' => strpos($current_route, '@user_card') === 0,
        ),
      ),
      'lots' => array(),
    );

    foreach (sfConfig::get('app_lot_types') as $type => $type_names) {
      $query = Doctrine::getTable('Lot')->createQueryActive()
          ->andWhereIn('user_id', $user->lots_user_id)
          ->andWhere('type = ?', $type);

      if ($query->count()) {
        $url = '@user_lots?id=' . $user->id . '&type=' . $type;
        $menu['lots'][] = array(
          'name'   => $type_names['plural'],
          'url'    => $url,
          'active' => $url == $current_route,
        );
      }
    }
    
    $this->menu = $menu;
  }
}
