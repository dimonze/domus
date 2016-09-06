<?php
$config = sfConfig::get('app_search_fields');
if (!empty($currency_type)) {
  $currency_type = array_search($currency_type, Lot::$currency_types);  
  if (!empty($currency_type)) {
    $config = $config[$type][$currency_type][$id];    
  }
  else {
    $config = $config[$type][$id];
  }
}else {
  $config = $config[$type][$id];
}
$options = isset($config['value']) ? $config['value'] : array();

$default = array(strpos($config['name'], '[from]') ? 'от' :
             (strpos($config['name'], '[to]') ? 'до' : ''));
if(isset($empty_first) && empty($empty_first)) {
  $default = array( $options[0] );
  unset( $options[0] );
}
array_unshift($options, $default);
?>
<?php 
  $selected = getArrayValueByStringPath($sf_params, $config['name']);
  echo select_tag(
    $config['name'],
    options_for_select(array_combine(array('') + $options, $default + $options), $selected),
    isset($attr) ? $attr : null)
?>