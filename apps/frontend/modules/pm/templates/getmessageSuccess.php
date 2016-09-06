<form action="<?= url_for('@lot_action?action=notify&id='.$sf_params->get('id')) ?>" method="post" class="ajax-validate">
  <fieldset>
    <legend><span class="prependClose"><?= $pm->subject ?></span></legend>
    <div>
      <?= nl2br(strip_tags($pm->message)) ?>
    </div>
  </fieldset>
</form>