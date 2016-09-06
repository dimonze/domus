<form action="<?= url_for('search/landing') ?>" method="post" class="ajax-validate" id="landing_page_form"><!--class="ajax-validate" -->
  <fieldset>
    <legend>
      <span class="prependClose">Новая "посадочная" страница</span>
    </legend>

    <div>
      <label for="url">URL</label>
      <?= $form['url']->render() ?>
      <?php
        if(!empty($same)) {
          echo '<br /><div id="same-landing">Аналогичные страницы:<p class="metroList">' .
          implode(', ', array_map(create_function('$page', 'return \'<a href="\' . url_for1 ("@pages", true) . $page . \'" target="blank_">\' . substr(strrchr($page, \'/\'), 1) . \'</a>\';') , $same))
          . '</p></div>';
        }
      ?>
    </div>
    <div>
      <label for="h1">H1</label>
      <?= $form['h1']->render() ?>
    </div>
    <div class="wide-wrapper">
      <label for="title">Префикс для заголовков объявлений</label>
      <?= $form['lot_title_prefix']->render() ?>
    </div>
    <div>
      <label for="title">SEO-title</label>
      <?= $form['title']->render() ?>
    </div>
    <div class="textarea-wrapper">
      <label for="description">SEO-description</label>
      <?= $form['description']->render() ?>
    </div>
    <div class="textarea-wrapper">
      <label for="keywords">SEO-keywords</label>
      <?= $form['keywords']->render() ?>
    </div>
    <div class="textarea-wrapper">
      <label for="seo_text">SEO-text</label>
      <?= $form['seo_text']->render() ?>
    </div>

    <input type="hidden" name="landing_page[query]" id="landing_query_text" value="" />
    
    <div class="buttonDiv">
	   <span class="formButton"><input type="submit" value="Сохранить"/></span>
    </div>
  </fieldset>
</form>