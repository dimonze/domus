<div id="content">
  <?php if (empty($query)): ?>
    <div class="rc-box events header-bl-box themes-main-box">
      <div class="rc t"><div></div></div>
      <div class="content">
        <h2>Для поиска введите запрос в поле справа</h2>
      </div>
      <div class="rc b"><div></div></div>
    </div>
  <?php endif ?>
  <?php if (
    (!empty($news_portal_pager) && $news_portal_pager->haveToPaginate())
    || (!empty($pager) && $pager->getPage() == 1)) :?>
    <div class="rc-box events header-bl-box themes-main-box">
      <div class="rc t"><div></div></div>
      <div class="content">
        <h2>Результат поиска в новостях, статьях, аналитике, событиях</h2>
        <?php
          include_partial('search_post', array(
            'pager' => !empty($news_portal_pager) ? $news_portal_pager : false,
            'query' => $query
          ))
        ?>
      </div>
      <div class="rc b"><div></div></div>
    </div>
  <?php endif ?>
  <?php if ((!empty($auhtor_articles_pager) && $author_articles_pager->haveToPaginate())
           || (!empty($pager) && $pager->getPage() == 1)): ?>
    <div class="rc-box events header-bl-box header-oth-box">
      <div class="rc t"><div></div></div>
      <div class="content">
        <h2>Результат поиска в авторских колонках и экспертных мнениях</h2>
        <?php
          include_partial('search_post', array(
            'pager' => !empty($author_articles_pager) ? $author_articles_pager : false,
            'query' => $query
          ))
        ?>
      </div>
      <div class="rc b"><div></div></div>
    </div>
  <?php endif ?>
  <?php if ((!empty($blogs_pager) && $blogs_pager->haveToPaginate())
           || (!empty($pager) && $pager->getPage() == 1)): ?>
    <div class="rc-box events header-bl-box themes-main-box">
      <div class="rc t"><div></div></div>
      <div class="content">
        <h2>Результат поиска в блогах</h2>
        <?php
          include_partial('search_post', array(
            'pager' => !empty($blogs_pager) ? $blogs_pager : false,
            'query' => $query
          ))
        ?>
      </div>
      <div class="rc b"><div></div></div>
    </div>
  <?php endif ?>
  <?php
    include_partial('global/posts-paginator', array(
      'pager' => !empty($pager) ? $pager : false,
      'query' => $query
    ))
  ?>
</div>
<div id="aside">
  <?php include_partial('page/aside-head')?>
  <?php include_partial('global/aside_search', array('q_search' => $query))?>
  <?php include_partial('banner/block3-right-spec') ?>
</div>