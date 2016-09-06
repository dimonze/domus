<?= '<' ?>?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">
  <?php if (count($news) > 0): ?>
    <?php foreach ($news as $new):?>
    <url>
      <loc><?= url_for('news/show?id=' . $new->id, true) ?></loc>
      <news:news>
        <?php $config = sfConfig::get('app_rss_google')?>
        <news:publication>
          <news:name><?= $config['publication_name']?></news:name>
          <news:language><?= $config['publication_language']?></news:language>
        </news:publication>
        <news:genres>Opinion, UserGenerated</news:genres>
        <news:publication_date><?= date(DATE_RSS, strtotime($new->created_at))?></news:publication_date>
        <news:title><?= $new->title ?></news:title>
      </news:news>
    </url>
    <?php endforeach ?>
  <?php endif ?>
  <?php if (count($articles) > 0): ?>
    <?php foreach ($articles as $article):?>
    <url>
      <loc><?= url_for('posts/show?post_type=' . $article->post_type . '&id=' . $article->id, true) ?></loc>
      <news:news>
        <?php $config = sfConfig::get('app_rss_google')?>
        <news:publication>
          <news:name><?= $config['publication_name']?></news:name>
          <news:language><?= $config['publication_language']?></news:language>
        </news:publication>
        <news:genres>Opinion, UserGenerated</news:genres>
        <news:publication_date><?= date(DATE_RSS, strtotime($article->created_at))?></news:publication_date>
        <news:title><?= $article->title ?></news:title>
      </news:news>
    </url>
    <?php endforeach ?>
  <?php endif ?>
  <?php if (count($events) > 0): ?>
    <?php foreach ($events as $event):?>
    <url>
      <loc><?= url_for('posts/show?post_type=' . $event->post_type . '&id=' . $event->id, true) ?></loc>
      <news:news>
        <?php $config = sfConfig::get('app_rss_google')?>
        <news:publication>
          <news:name><?= $config['publication_name']?></news:name>
          <news:language><?= $config['publication_language']?></news:language>
        </news:publication>
        <news:genres>Opinion, UserGenerated</news:genres>
        <news:publication_date><?= date(DATE_RSS, strtotime($event->created_at))?></news:publication_date>
        <news:title><?= $event->title ?></news:title>
      </news:news>
    </url>
    <?php endforeach ?>
  <?php endif ?>
  <?php if (count($analytics) > 0): ?>
    <?php foreach ($analytics as $analytic):?>
    <url>
      <loc><?= url_for('posts/show?post_type=' . $analytic->post_type . '&id=' . $analytic->id, true) ?></loc>
      <news:news>
        <?php $config = sfConfig::get('app_rss_google')?>
        <news:publication>
          <news:name><?= $config['publication_name']?></news:name>
          <news:language><?= $config['publication_language']?></news:language>
        </news:publication>
        <news:genres>Opinion, UserGenerated</news:genres>
        <news:publication_date><?= date(DATE_RSS, strtotime($analytic->created_at))?></news:publication_date>
        <news:title><?= $analytic->title ?></news:title>
      </news:news>
    </url>
    <?php endforeach ?>
  <?php endif ?>
  <?php if (count($author_articles) > 0): ?>
    <?php foreach ($author_articles as $author_article):?>
    <url>
      <loc><?= url_for('@author_article_show?author_id='.$author_article->author_id.'&id='.$author_article->id, true) ?></loc>
      <news:news>
        <?php $config = sfConfig::get('app_rss_google')?>
        <news:publication>
          <news:name><?= $config['publication_name']?></news:name>
          <news:language><?= $config['publication_language']?></news:language>
        </news:publication>
        <news:genres>Opinion, UserGenerated</news:genres>
        <news:publication_date><?= date(DATE_RSS, strtotime($author_article->created_at))?></news:publication_date>
        <news:title><?= $author_article->title ?></news:title>
      </news:news>
    </url>
    <?php endforeach ?>
  <?php endif ?>
  <?php if (count($expert_articles) > 0): ?>
    <?php foreach ($expert_articles as $expert_article):?>
    <url>
      <loc><?= url_for('@expert_article_show?author_id='.$expert_article->author_id.'&id='.$expert_article->id, true) ?></loc>
      <news:news>
        <?php $config = sfConfig::get('app_rss_google')?>
        <news:publication>
          <news:name><?= $config['publication_name']?></news:name>
          <news:language><?= $config['publication_language']?></news:language>
        </news:publication>
        <news:genres>Opinion, UserGenerated</news:genres>
        <news:publication_date><?= date(DATE_RSS, strtotime($expert_article->created_at))?></news:publication_date>
        <news:title><?= $expert_article->title ?></news:title>
      </news:news>
    </url>
    <?php endforeach ?>
  <?php endif ?>
</urlset>