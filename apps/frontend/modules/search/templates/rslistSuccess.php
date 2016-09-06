<div class="contentLeft_02">
  <?php foreach($data as $rdata): 
    if(empty($rdata['nodes'])) continue;
    
    if(count($data) > 1) {
      if($rdata['region']['fullname'] == 'Московской области') $rdata['region']['fullname'] = 'Подмосковья';
      echo '<h1>'.($type == 'rajon' ? 'Районы ' : 'Шоссе ').$rdata['region']['fullname'].'</h1>';
    } else {
      echo '<h1>'.($type == 'rajon' ? 'Список районов' : 'Список шоссе').'</h1>';
    }
    
    if(!empty($rdata['region'][$type.'text'])) echo '<div class="rayon-description">'.$rdata['region'][$type.'text'].'</div>';
  ?>
    <?php foreach( array_keys(Lot::$type_ru) as $ctype): 
      if(!array_key_exists($ctype, $rdata['nodes'])) continue;
      $base_url = DomusSearchRoute::buildUrlForRedirect(array(
        'region_id' => $rdata['nodes'][$ctype][0]['attrs']['region_id'],
        'type' => $rdata['nodes'][$ctype][0]['attrs']['type'],
      ));
      
      if(count($rdata['nodes'])>1) {
        $ctitle = strtr(array_search($rdata['nodes'][$ctype][0]['attrs']['type'], Lot::$_routing_types), array_flip(DomusSearchRoute::$translit_table));
        $ctitle = preg_replace(array('#-#', '#скоy#u'), array(' ', 'ской'), $ctitle);
      }
    ?>
    <div class="rayon-list">
      <?php if(isset($ctitle)): ?><h2 class="caption"><?php echo MetaParse::mb_ucfirst($ctitle) ?></h2><?php endif; ?>
      <ul class="rayon">
        <?php foreach ($rdata['nodes'][$ctype] as $node): 
          $params = unserialize($node['attrs']['params']);
          $name = Regionnode::unformatName($params['regionnode'][0]);
        ?>
        <li><a href="<?php echo $base_url.$node['attrs']['url'] ?>"><?php echo $name[0] ?></a></li>
        <?php endforeach; ?>
      </ul>
      <div style="clear:both;"></div>
    </div>
    <?php endforeach; ?>
  <?php endforeach; ?>
</div>
<div class="contentRight_02">
  <?php include_partial('banner/block3-right-spec') ?>
</div>