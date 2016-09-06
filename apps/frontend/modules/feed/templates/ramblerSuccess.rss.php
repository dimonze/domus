<<?php ?>?xml version="1.0" encoding="UTF-8" ?>
<rss xmlns:rambler="http://news.rambler.ru" version="2.0">
<channel>
  <?php $config = sfConfig::get('app_rss_rambler')?>
  <title><?= $config['title']?></title>
  <link><?= $config['link']?></link>
  <description><?= $config['description']?></description>
  <?php if (count($news) > 0): ?>
    <?php foreach ($news as $new): ?>
      <item>
        <title><?= $new->title?></title>
        <link><?= url_for('news/show?id=' . $new->id, true) ?></link>
        <description><![CDATA[<?= $new->lid ?>]]></description>
        <category><?= implode(', ', $new->getThemesArray())?></category>
        <fulltext><![CDATA[<?= $new->post_text ?>]]></fulltext>
        <author><?= $config['author']?></author>
        <pubDate><?= date(DATE_RSS, strtotime($new->created_at))?></pubDate>
        <?php if ($photo = photo($new, 281, 209)): ?>
          <enclosure url="<?= $photo ?>" type="image/jpeg"/>
        <?php endif ?>
      </item>
    <?php endforeach ?>
  <?php endif ?>
  <?php if (count($articles) > 0): ?>
    <?php foreach ($articles as $article): ?>
      <item>
        <title><?= $article->title?></title>
        <link><?= url_for('posts/show?post_type=' . $article->post_type . '&id=' . $article->id, true) ?></link>
        <description><![CDATA[<?= $article->lid ?>]]></description>
        <category><?= implode(', ', $article->getThemesArray())?></category>
        <fulltext><![CDATA[<?= $article->post_text ?>]]></fulltext>
        <author><?= $config['author']?></author>
        <pubDate><?= date(DATE_RSS, strtotime($article->created_at))?></pubDate>
        <?php if ($photo = photo($article, 281, 209)): ?>
          <enclosure url="<?= $photo ?>" type="image/jpeg"/>
        <?php endif ?>
      </item>
    <?php endforeach ?>
  <?php endif ?>
  <?php if (count($events) > 0): ?>
    <?php foreach ($events as $event):?>
      <item>
        <title><?= $event->title ?></title>
        <link><?= url_for('posts/show?post_type=' . $event->post_type . '&id=' . $event->id, true) ?></link>
        <description><?= feed_escaping($event->lid) ?></description>
        <author><?= $config['author']?></author>
        <category><?= implode(', ', $event->getThemesArray())?></category>
        <pubDate><?= date(DATE_RSS, strtotime($event->created_at))?></pubDate>
        <?php if ($photo = photo($event, 281, 209)): ?>
          <enclosure url="<?= $photo ?>" type="image/jpeg"/>
        <?php endif ?>
      </item>
    <?php endforeach ?>
  <?php endif ?>
  <?php if (count($analytics) > 0): ?>
    <?php foreach ($analytics as $analytic):?>
      <item>
        <title><?= $analytic->title ?></title>
        <link><?= url_for('posts/show?post_type=' . $analytic->post_type . '&id=' . $analytic->id, true) ?></link>
        <description><?= feed_escaping($analytic->lid) ?></description>
        <author><?= $config['author']?></author>
        <category><?= implode(', ', $analytic->getThemesArray())?></category>
        <pubDate><?= date(DATE_RSS, strtotime($analytic->created_at))?></pubDate>
        <?php if ($photo = photo($analytic, 281, 209)): ?>
          <enclosure url="<?= $photo ?>" type="image/jpeg"/>
        <?php endif ?>
      </item>
    <?php endforeach ?>
  <?php endif ?>
  <?php if (count($author_articles) > 0): ?>
    <?php foreach ($author_articles as $author_article):?>
      <item>
        <title><?= $author_article->title ?></title>
        <link><?= url_for('@author_article_show?author_id='.$author_article->author_id.'&id='.$author_article->id, true) ?></link>
        <description><?= feed_escaping($author_article->lid) ?></description>
        <author><?= $config['author']?></author>
        <category><?= implode(', ', $author_article->getThemesArray())?></category>
        <pubDate><?= date(DATE_RSS, strtotime($author_article->created_at))?></pubDate>
        <?php if ($photo = photo($author_article, 281, 209)): ?>
          <enclosure url="<?= $photo ?>" type="image/jpeg"/>
        <?php endif ?>
      </item>
    <?php endforeach ?>
  <?php endif ?>
  <?php if (count($expert_articles) > 0): ?>
    <?php foreach ($expert_articles as $expert_article):?>
      <item>
        <title><?= $expert_article->title ?></title>
        <link><?= url_for('@expert_article_show?author_id='.$expert_article->author_id.'&id='.$expert_article->id, true) ?></link>
        <description><?= feed_escaping($expert_article->lid) ?></description>
        <author><?= $config['author']?></author>
        <category><?= implode(', ', $expert_article->getThemesArray())?></category>
        <pubDate><?= date(DATE_RSS, strtotime($expert_article->created_at))?></pubDate>
        <?php if ($photo = photo($expert_article, 281, 209)): ?>
          <enclosure url="<?= $photo ?>" type="image/jpeg"/>
        <?php endif ?>
      </item>
    <?php endforeach ?>
  <?php endif ?>
</channel>
</rss>