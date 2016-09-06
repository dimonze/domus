<blockquote style="margin-left: 0;">
  <h3 style="overflow: hidden;">

    <div style="float:left; padding-right: 3px; font-size: 0.65em; color: gray; line-height: 0.9em;">
      m<br />e<br />n<br />u
    </div>

    <div style="margin: 0 0 5px 12px; padding-bottom: 3px; border-bottom: 1px dotted silver;">
      <?php $menu = sfConfig::get('app_menu', array()) ?>
      <?php foreach ($menu as $item): ?>
        <?php if (empty($item['credential']) || $sf_user->hasCredential($item['credential'])): ?>
          <?= link_to($item['text'], $item['route']) ?>,
        <?php endif ?>
      <?php endforeach ?>
      <a href="/">сайт</a>
    </div>

    <?php if ($sf_params->get('module') == 'form'): ?>
      Формы &mdash;
        квартиры:
         <?= link_to('продажа', 'form/index?type=apartament-sale') ?>/<?= link_to('аренда', 'form/index?type=apartament-rent') ?>,
        дома:
         <?= link_to('продажа', 'form/index?type=house-sale') ?>/<?= link_to('аренда', 'form/index?type=house-rent') ?>,
        коммерческая:
         <?= link_to('продажа', 'form/index?type=commercial-sale') ?>/<?= link_to('аренда', 'form/index?type=commercial-rent') ?>,
        новостройки:
         <?= link_to('продажа', 'form/index?type=new_building-sale') ?>,
        коттеджные поселки:
         <?= link_to('продажа', 'form/index?type=cottage-sale') ?>.

    <?php elseif ($sf_params->get('module') == 'settings'): ?>
      <?= link_to('Настройки', 'settings/index') ?> &mdash;
        отображение &mdash;
        <?= link_to('карточка объекта', 'settings/lot-info-order') ?>,
        <?= link_to('округи', 'settings/districts') ?>;
        <?= link_to('баллы', 'settings/rating') ?>;
        <?= link_to('боковой блок', 'settings/aside') ?>;
        <?= link_to('экспорт в Yandex', 'settings/yaexport') ?>.

        Справочники &mdash;
        <?= link_to('форма поиска', 'settings/search-reference') ?>,
        <?= link_to('стоп-слова', 'settings/stop-words') ?>,
        <?= link_to('специализации', 'settings/specialities') ?>,
        <?= link_to('мета-данные', 'settings/meta') ?>,
        <?= link_to('темы сообщений', 'settings/emailthemes') ?>.

    <?php elseif ($sf_params->get('module') == 'news_portal'): ?>
        Блоги &mdash;
        <?= link_to('авторы', 'news_portal/postauthor') ?>,
        <?= link_to('авторские статьи', 'news_portal/authorarticle') ?>,
        <?= link_to('экспертные мнения', 'news_portal/expertarticle') ?>.

        Тексты &mdash;
        <?= link_to('новости', 'news_portal/news') ?>,
        <?= link_to('статьи', 'news_portal/article') ?>,
        <?= link_to('аналитика', 'news_portal/analytics') ?>,
        <?= link_to('события', 'news_portal/events') ?>,
        <?= link_to('коментарии', 'news_portal/comments') ?>,
        <?= link_to('вопросы', 'news_portal/qa') ?>/<?= link_to('ответы', 'news_portal/qa_answers') ?>,
        <?= link_to('опросы', 'news_portal/questionnaire') ?>.

        Настройки &mdash;
        <?= link_to('темы', 'news_portal/themes') ?>.

    <?php elseif ($sf_params->get('module') == 'claim' || $sf_params->get('module') == 'claimtheme'): ?>
        <?php if ($sf_user->hasCredential('claims-add-theme')): ?>
          Настройки &mdash;
          <?= link_to('темы', 'claimtheme/index') ?>.
        <?php endif ?>

    <?php endif ?>
  </h3>
</blockquote>