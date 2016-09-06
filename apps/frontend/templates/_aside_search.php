<form action="<?= url_for('@portal_search')?>" method="post" class="aside-search png24">
  <input type="text" name="q-search" class="aside-search-t" <?= (!empty($q_search) ? 'value="' . $q_search . '"' : '') ?>/>
  <input type="submit" value="" class="aside-search-s" />
  <span>Поиск по новостям, событиям, статьям, мнениям и вопросам</span>
</form>