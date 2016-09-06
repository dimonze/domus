<<?php ?>?xml version="1.0" encoding="utf-8"?>
<rss version="2.0" xmlns="http://backend.userland.com/rss2" xmlns:yandex="http://news.yandex.ru">
<channel>
  <?php $config = sfConfig::get('app_rss_yandex')?>
  <title><?= $config['title']?></title>
  <link><?= $config['link']?></link>
  <description><?= $config['description']?></description>
  <image>
    <url>http://www.mesto.ru/uploads/content/mesto.gif</url>
    <title><?= $config['title']?></title>
    <link><?= $config['link']?></link>
  </image>
  <?php if (count($news) >0): ?>
    <?php foreach ($news as $new):?>
      <item>
        <title><?= $new->title ?></title>
        <?php $link = url_for('news/show?id=' . $new->id, true) ?>
        <?php if (!empty($proxy_server)): ?>
          <?php $link = $proxy_server . $link ?>
        <?php endif ?>
        <link><?=  $link ?></link>
        <description><?= feed_escaping($new->lid) ?></description>
        <yandex:genre>message</yandex:genre>
        <yandex:full-text><?= feed_escaping($new->post_text)?></yandex:full-text>
        <category><?= implode(', ', $new->getThemesArray())?></category>
        <?php if ($photo = photo($new, 281, 209)): ?>
          <enclosure url="<?= $photo ?>" type="image/jpeg"/>
        <?php endif ?>
        <pubDate><?= date(DATE_RSS, strtotime($new->created_at))?></pubDate>
      </item>
    <?php endforeach ?>
  <?php endif ?>
  <?php if (count($articles) > 0): ?>
    <?php foreach ($articles as $article):?>
      <item>
        <title><?= $article->title ?></title>
        <?php $link = url_for('posts/show?post_type=' . $article->post_type . '&id=' . $article->id, true) ?>
        <?php if (!empty($proxy_server)): ?>
          <?php $link = $proxy_server . $link ?>
        <?php endif ?>
        <link><?=  $link ?></link>
        <description><?= feed_escaping($article->lid) ?></description>
        <yandex:genre>article</yandex:genre>
        <yandex:full-text><?= feed_escaping($article->post_text)?></yandex:full-text>
        <category><?= implode(', ', $article->getThemesArray())?></category>
        <?php if ($photo = photo($article, 281, 209)): ?>
          <enclosure url="<?= $photo ?>" type="image/jpeg"/>
        <?php endif ?>
        <pubDate><?= date(DATE_RSS, strtotime($article->created_at))?></pubDate>
      </item>
    <?php endforeach ?>
  <?php endif ?>
  <?php if (count($events) > 0): ?>
    <?php foreach ($events as $event):?>
      <item>
        <title><?= $event->title ?></title>
        <?php $link = url_for('posts/show?post_type=' . $event->post_type . '&id=' . $event->id, true) ?>
        <?php if (!empty($proxy_server)): ?>
          <?php $link = $proxy_server . $link ?>
        <?php endif ?>
        <link><?=  $link ?></link>
        <description><?= feed_escaping($event->lid) ?></description>
        <yandex:genre>article</yandex:genre>
        <yandex:full-text><?= feed_escaping($event->post_text)?></yandex:full-text>
        <category><?= implode(', ', $event->getThemesArray())?></category>
        <?php if ($photo = photo($event, 281, 209)): ?>
          <enclosure url="<?= $photo ?>" type="image/jpeg"/>
        <?php endif ?>
        <pubDate><?= date(DATE_RSS, strtotime($event->created_at))?></pubDate>
      </item>
    <?php endforeach ?>
  <?php endif ?>
  <?php if (count($analytics) > 0): ?>
    <?php foreach ($analytics as $analytic):?>
      <item>
        <title><?= $analytic->title ?></title>
        <?php $link = url_for('posts/show?post_type=' . $analytic->post_type . '&id=' . $analytic->id, true) ?>
        <?php if (!empty($proxy_server)): ?>
          <?php $link = $proxy_server . $link ?>
        <?php endif ?>
        <link><?=  $link ?></link>
        <description><?= feed_escaping($analytic->lid) ?></description>
        <yandex:genre>article</yandex:genre>
        <yandex:full-text><?= feed_escaping($analytic->post_text)?></yandex:full-text>
        <category><?= implode(', ', $analytic->getThemesArray())?></category>
        <?php if ($photo = photo($analytic, 281, 209)): ?>
          <enclosure url="<?= $photo ?>" type="image/jpeg"/>
        <?php endif ?>
        <pubDate><?= date(DATE_RSS, strtotime($analytic->created_at))?></pubDate>
      </item>
    <?php endforeach ?>
  <?php endif ?>
  <?php if (count($author_articles) > 0): ?>
    <?php foreach ($author_articles as $author_article):?>
      <item>
        <title><?= $author_article->title ?></title>
        <?php $link = url_for('@author_article_show?author_id='.$author_article->author_id.'&id='.$author_article->id, true) ?>
        <?php if (!empty($proxy_server)): ?>
          <?php $link = $proxy_server . $link ?>
        <?php endif ?>
        <link><?=  $link ?></link>
        <description><?= feed_escaping($author_article->lid) ?></description>
        <yandex:genre>article</yandex:genre>
        <yandex:full-text><?= feed_escaping($author_article->post_text)?></yandex:full-text>
        <category><?= implode(', ', $author_article->getThemesArray())?></category>
        <?php if ($photo = photo($author_article, 281, 209)): ?>
          <enclosure url="<?= $photo ?>" type="image/jpeg"/>
        <?php endif ?>
        <pubDate><?= date(DATE_RSS, strtotime($author_article->created_at))?></pubDate>
      </item>
    <?php endforeach ?>
  <?php endif ?>
  <?php if (count($expert_articles) > 0): ?>
    <?php foreach ($expert_articles as $expert_article):?>
      <item>
        <title><?= $expert_article->title ?></title>
        <?php $link = url_for('@expert_article_show?author_id='.$expert_article->author_id.'&id='.$expert_article->id, true) ?>
        <?php if (!empty($proxy_server)): ?>
          <?php $link = $proxy_server . $link ?>
        <?php endif ?>
        <link><?=  $link ?></link>
        <description><?= feed_escaping($expert_article->lid) ?></description>
        <yandex:genre>interview</yandex:genre>
        <yandex:full-text><?= feed_escaping($expert_article->post_text)?></yandex:full-text>
        <category><?= implode(', ', $expert_article->getThemesArray())?></category>
        <?php if ($photo = photo($expert_article, 281, 209)): ?>
          <enclosure url="<?= $photo ?>" type="image/jpeg"/>
        <?php endif ?>
        <pubDate><?= date(DATE_RSS, strtotime($expert_article->created_at))?></pubDate>
      </item>
    <?php endforeach ?>
  <?php endif ?>
</channel>
</rss>
