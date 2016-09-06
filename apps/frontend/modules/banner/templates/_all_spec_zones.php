<div class="index-adv-teaser-wt">
  <div class="wrap closer">
    <script type='text/javascript'><!--// <![CDATA[
      for (var i = 1; i <= 12; i++) {
        mesto_bind(333, 3000 + i, 'tail');
      }
      <?php if(!empty($page) || !empty($lot)):?>
         // ]]> -->
      </script>
      <div id="spec-page-text" class="content box-post">
        <?php if (empty($lot)): ?>
          <?php if ($page instanceof SpecialTargeting): ?>
            <h1><?= $page->name ?></h1>
          <?php else: ?>
            <h1><a href="<?=$page->url?>"><?=$page->name?></a></h1>
          <?php endif ?>
          <?=$page->text?>
          <?php if ($page instanceof SpecialTargeting): ?>
          <?php else: ?>
            <a href="<?=$page->url?>">читать дальше &gt;&gt;&gt;</a>
          <?php endif ?>
        <?php else: ?>
            <h1><?= link_to_if($lot->active, $lot->address_full, prepare_show_lot_url($lot))?></h1>
          <?php include_partial('lot/spec_card', array('lot' => $lot)) ?>
          <?= link_to_if($lot->active, 'подробнее &gt;&gt;&gt;', prepare_show_lot_url($lot), array('class' => 'more_info'))?>
        <?php endif ?>
      </div>
      <script type='text/javascript'>
        $(function() {
          var offset = $('#spec-page-text h1').offset().top - $('#spec-page-text img').offset().top
          var origin = $('#spec-page-text img').css('margin-top').match(/\d+/g).shift()*1;
          $('#spec-page-text img').css('margin-top', (offset - origin + 4) + 'px');
        });
      </script>
      <script type='text/javascript'><!--// <![CDATA[
    <?php endif;?>
        for (var i = 13; i <= 72; i++) {
          mesto_bind(333, 3000 + i, 'tail');
        }
  // ]]> -->
  </script>
  </div>
</div>