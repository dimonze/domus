<?= $page->getContent(ESC_RAW) ?>
<br />
<div class="pageHeader">
<h1>Дополнительные поля для картир</h1>
</div>
<?php include_partial('fields_list', array('fields' => $afields)) ?>
<br />
<div class="pageHeader">
<h1>Дополнительные поля для домов</h1>
</div>
<?php include_partial('fields_list', array('fields' => $hfields)) ?>
<br />
<div class="pageHeader">
<h1>Дополнительные поля для комерческой недвижимости</h1>
</div>
<?php include_partial('fields_list', array('fields' => $cfields)) ?>
