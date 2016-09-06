<?php

class newBuildingFilter extends sfFilter {
  
  private $host;
  
  private $nb_domain;
  
  private $main_domain;

  public function execute($filterChain) {
    $filterChain->execute();
    $path = $this->getContext()->getRequest()->getPathInfo();
    $host = $this->getContext()->getRequest()->getHost();
    if ($this->getContext()->getRequest()->isXmlHttpRequest() || strpos($path, '.js')) {
      return;
    }

    $parts = explode('.', $this->host);
    if(false !== strpos($host, sfConfig::get('app_new_building_domain')) && !$path) {
      $this->getContext()->getController()->redirect(Toolkit::getGeoHostByRegionId(Toolkit::getRegionId()));
    }
  }


  public function execute2($filterChain) {
    $filterChain->execute();
    if ($this->getContext()->getRequest()->isXmlHttpRequest()) {
      return;
    }
    $path = $this->getContext()->getRequest()->getPathInfo();
    $this->host = $this->getContext()->getRequest()->getHost();
    $this->main_domain = sfConfig::get('app_site');
    $this->nb_domain = sfConfig::get('app_new_building_domain');
    $is_nb = strpos($path, 'new_building-sale');
    if (strpos($path, '.js')) {
      return;
    }
    if ($is_nb && ($this->host != $this->nb_domain)) {
      $this->getContext()->getController()->redirect('http://' . $this->nb_domain . $path);
    } elseif (!$is_nb && ($this->host == $this->nb_domain)) {
      $this->getContext()->getController()->redirect('http://' . $this->main_domain . $path);
    }
    $response = $this->getContext()->getResponse();
    $content = $response->getContent();
    $regex = "~href=(\"|')(.+)(\"|')~isU";
    if ($is_nb) {
      $content = preg_replace_callback($regex, array($this, 'fix'), $content);
    } else {
      $content = preg_replace_callback($regex, array($this, 'fix2'), $content);
    }
    $this->getContext()->getResponse()->setContent($content);
  }

  public function fix($input) {
    if (0 === strpos($input[2], '//') || 0 == strpos($input[2], 'http://')) {
      return $input[0];
    }
    
    if ((strpos($input[2], $base_domain) || $input[2][0] == '/')
            //don't perform replace if we find this
            && !strpos($input[2], 'new_building-sale')
            && !strpos($input[2], 'compare')
            && !strpos($input[2], 'favourite')
            && !strpos($input[2], 'notify')
            && !strpos($input[2], 'phone')
            && !strpos($input[2], 'claim')
            && !strpos($input[2], 'user/login')
            && !strpos($input[2], 'user/register')
            ) {
      $input[2] = str_replace('http://' . $this->main_domain, '', $input[2]);
      $input[2] = str_replace('http://' . $this->main_domain, '', $input[2]);
      $input[2] = 'http://' . $this->main_domain . $input[2];
    }
    return "href=$input[1]$input[2]$input[3]";
  }
  
  public function fix2($input) {
    if ((strpos($input[2], $this->main_domain) || $input[2][0] == '/') && strpos($input[2], 'new_building-sale')) {
      $input[2] = str_replace('http://' . $this->main_domain, '', $input[2]);
      $input[2] = 'http://' . $this->nb_domain . $input[2];
    }
    return "href=$input[1]$input[2]$input[3]";
  }
  
}