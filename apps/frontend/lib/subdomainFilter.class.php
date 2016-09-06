<?php

class subdomainFilter extends sfFilter {
  public function execute($filterChain)
  {
    $ctx = $this->getContext();
    $uri = $ctx->getRequest()->getPathInfo();
    
    if (preg_match('#^/(.*)(-sale|-rent)/(\d{1,2})/search/(.*)#u', $uri, $params)) {
      $host = Toolkit::getGeoHostByRegionId($params[3]);
      $type = Lot::getRoutingType($params[1]. $params[2]);
      $ctx->getController()->redirect($host . '/' . $type, 301);
    }
    
    if ($this->isFirstCall() && !Toolkit::getRegionId()) {
      $params = $ctx->getRequest()->getParameterHolder()->getAll();
      $route  = $ctx->getRouting()->getCurrentRouteName();

      $geo       = sfGeoIpRu::find($_SERVER['REMOTE_ADDR']);
      $region_id = $geo ? $geo->region_id : 77;
      $host      = Toolkit::getGeoHostByRegionId($region_id);
      
      $ctx->getUser()->current_region = Doctrine::getTable('Region')->find($region_id);
      $ctx->getResponse()->setCookie('current_region', $region_id);

      $ctx->getController()->redirect($host . $uri);
    }
    elseif (!$ctx->getRequest()->isXmlHttpRequest()
      && !preg_match('/.js(\?.*?)?$/', $ctx->getRequest()->getPathInfo())
      && $region_id = Toolkit::getRegionId()){
      $ctx->getUser()->current_region = Doctrine::getTable('Region')->find($region_id);
      $ctx->getResponse()->setCookie('current_region', $region_id);
    }

    //Редиректы со старого роутинга на новый для главной
    if (preg_match('#^/(\d{1,2})/main$#u',  $uri, $matches)) {
      $host = Toolkit::getGeoHostByRegionId($matches[1]);
      $ctx->getController()->redirect($host, 301);
    }
    
    //Редиректы со старого роутинга на новый для новостного портала
    if (preg_match('#^/(\d{1,2})/(all|news|articles|experts|authors|posts|qa|blog)(.*)$#u', $uri, $params)) {
      $host = Toolkit::getGeoHostByRegionId($params[1]);
      $path = '/' . preg_replace('#^qa(/?\d*)$#', 'qas$1', $params[2] . $params[3]);
      $ctx->getController()->redirect($host . $path, 301);
    }
    
    $filterChain->execute();
  }
}
