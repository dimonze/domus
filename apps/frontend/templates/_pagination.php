<?php if (isset($pager) && $pager->haveToPaginate()): ?>
  <?php
  $request = $sf_request;
  $routing = sfContext::getInstance()->getRouting();

  $params = array(
      'type' => $request->getParameter('type', null),
      'page' => intval($request->getParameter('page',1))
  );
  
  if(empty($params['type'])) {
    $params = $request->getParameterHolder()->getAll();
  } elseif(in_array($params['type'], Lot::$_routing_types))
    $params['type'] = array_search ($params['type'], Lot::$_routing_types);

  $routes = $routing->getRoutes();
  $route_name = $routing->getCurrentRouteName();
  //В ajax передается текущий URL для поиска постраничного роута
  if($request->hasParameter('curl')) {
    $route = $routing->parse($request->getParameter('curl'));
    if($route) $route_name = array_search($route['_sf_route'], $routes);
  }
  
  //Если есть аналогичный постраничный путь
  if(!preg_match('#_paginated$#', $route_name) 
      && in_array($route_name.'_paginated', array_keys($routes))) {
    $route_name .= '_paginated';
  }
  //Костыль для slug
  $rv = $routes[$route_name]->getVariables();
  if(in_array('slug', array_keys($rv))) {
    if($request->hasParameter('slug')) 
      $params['slug'] = $request->getParameter('slug');
    elseif($request->hasParameter('curl')) {
      $params['slug'] = preg_split('#/#', $request->getParameter('curl'),0,PREG_SPLIT_NO_EMPTY);
      $params['slug'] = $params['slug'][array_search($rv['slug'], array_values($rv))];
    } else {
      $params['slug'] = 'error';
    }
  } else
    $params = $request->getParameterHolder()->getAll();
  ?>

  <div class="pagesNaviBox" <?= (!empty($styles))  ? 'style="' . $styles .'"' : '' ?>>
    <ul class="pagesNavi">
      <?php if($pager->getPreviousPage() != $pager->getPage() ):
        $params['page'] = $pager->getPreviousPage();
        $link = $routing->generate($route_name, $params);
        if($params['page'] == 1) $link = preg_replace('#/1$#', '', $link);
      ?>
      <li>
        <?= link_to('предыдущая', $link, array(
            'class' => 'previous', 
            'rel' => $pager->getPreviousPage()
        )) ?>
      </li>
      <?php endif ?>
      <?php foreach ($pager->getLinks() as $page):
        $params['page'] = $page;
        $link = $routing->generate($route_name, $params);
        if($params['page'] == 1) $link = preg_replace('#/1$#', '', $link);
      ?>
      <li>
        <?= link_to($page, $link, array(
            'class' => $page == $pager->getPage() ? 'active' : null, 
            'rel' => $page
        )) ?>
      </li> 
      <?php endforeach; ?>
      <?php if( $pager->getPage() != $pager->getNextPage() ):
        $params['page'] = $pager->getNextPage();
      ?>
      <li>
        <?= link_to2('следующая', $route_name, $params, array(
            'class' => 'next', 
            'rel' => $pager->getNextPage()
        )) ?>
      </li>
      <?php endif ?>

      <li class="pageNumber">
        <?= $pager->getPage() ?>/<?= ceil($pager->getNbResults()/$pager->getMaxPerPage()) ?>
      </li>

    </ul>

    <div class="clearBoth"></div>
  </div>
<?php endif ?>