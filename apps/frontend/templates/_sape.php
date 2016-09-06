<div class="ft-center">
  <?php $ctx = stream_context_create( array( 'http' => array('timeout' => 3) ) ); ?>
  <?= @file_get_contents("http://links.radiushosting.ru/get_links.php?url=".urlencode($_SERVER['REQUEST_URI'])."&host=www.mesto.ru&charset=utf-8", 0, $ctx); ?>
</div>