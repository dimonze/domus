<?php if (isset($pager) && $pager->haveToPaginate()): ?>

  <?php
  // @todo: дублирование кода. и вообще, его тут не должно быть
  if (!empty($url_type) && $url_type == 'append') {
    $route = $sf_request->getPathInfo();
    $route = preg_replace('/page\/\d+\/?/', '', $route);
    $route = preg_replace('/\/$/', '', $route);
    $route .= '/page/';
  }
  else {
    if (!isset($route)) {
      $route = sfContext::getInstance()->getRouting()->getCurrentInternalUri(false);
    }

    if (!isset($route_params)) {
      $route_params = array();
    }

    $params = explode('?', $route);

    if (isset($params[1])) {
      $params[1] = explode('&', $params[1]);
      foreach ($params[1] as $i => $v) {
        $v = explode('=', $v, 1);
        if ($v[0] == 'page') {
          unset($params[1][$i]);
        }
        elseif (isset($route_params[$v[0]])) {
          $params[1][$i] = $v[0].'='.$route_params[$v[0]];
          unset($route_params[$v[0]]);
        }
      }
    }
    else {
      $params[1] = array();
    }

    if (count($route_params)) {
      foreach ($route_params as $k => $v) {
        $params[1][] = "$k=$v";
      }
    }
    $params[1][] = 'page=';

    $route = implode('?', array($params[0], implode('&', $params[1])));
  }
  ?>

  <ul class="resultPageMenu">
    <li>
      <?php $page = $pager->getPreviousPage() ?>
      <?php if ($page == 1): ?>
        <?= link_to('предыдущая', str_replace('/page/', '', $route),
                    'class=previous rel=' . $pager->getPreviousPage()) ?>
      <?php else: ?>
        <?= link_to('предыдущая', $route . $page,
                    'class=previous rel=' . $page) ?>
      <?php endif ?>
    </li>

    <li class="pageNumber">
      <?= $pager->getPage() ?>/<?= ceil($pager->getNbResults()/$pager->getMaxPerPage()) ?>
    </li>
    <li>
      <?= link_to('следующая', $route.$pager->getNextPage(),
                  'class=next rel='.$pager->getNextPage()) ?>
    </li>
  </ul>

<?php endif ?>