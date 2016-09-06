<?php
class translitFilter extends sfFilter {
  
  protected $translation_table = array(
    '33' => '_',
    '22' => '__',
    'Y1' => 'YI',
    'y1' => 'yi'
  );
  
  protected $rules = array(
      'posts' => 'theme',
      'search' => 'rn',
  );
  
  public function execute($filterChain) {
    $ctx = $this->getContext();
    $host = $ctx->getRequest()->getHost();
    $filterChain->execute();
    $params = $ctx->getRequest()->getParameterHolder()->getAll();
    $module = $params['module'];
    
    if (isset($this->rules[$module]) && isset($params[$this->rules[$module]])) {
      $param = $params[$this->rules[$module]];
      $v = strtr($param, $this->translation_table);
      if ($v != $param) {
        $params[$this->rules[$module]] = $v;
        $route = $ctx->getRouting()->getCurrentRouteName();
        $ctx->getController()->redirect('http://' . $host . $ctx->getRouting()->generate($route, $params));
      }
    }
  }
  
  
}