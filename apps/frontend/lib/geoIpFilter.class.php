<?php
class geoIpFilter extends sfFilter
{
  public function execute($filterChain)
  {
    if ($this->isFirstCall()) {
      $user = $this->context->getUser();
      $news_portal_routes = array(
        'homepage', 'news_show', 'news_by_theme', 'news_by_section', 'author_article_show',
        'author_article', 'author_article_by_theme', 'author_article_show_author',
        'expert_article_show', 'expert_article', 'expert_article_by_theme',
        'expert_article_show_author', 'posts', 'post_show', 'posts_by_theme',
        'blogs', 'blog_post_show', 'qa'
      );
      $request = $this->context->getRequest();

      if (($region_id = Toolkit::getRegionId())
           && ($request->getParameter('module') == 'search'
               || in_array($this->context->getRouting()->getCurrentRouteName(), $news_portal_routes))) {
        $user->current_region = Doctrine::getTable('Region')->find($region_id);
        $this->context->getResponse()->setCookie('current_region', $region_id);
      }

      if (!$user->current_region) {
        $geo = sfGeoIpRu::find($_SERVER['REMOTE_ADDR']);
        $region_id = $geo ? $geo->region_id : 77;

        $user->current_region = Doctrine::getTable('Region')->find($region_id);
        $this->context->getResponse()->setCookie('current_region', $region_id);

        if ($this->context->getRequest()->getPathInfo() == '/' && 77 != $region_id) {
          return $this->context->getController()->redirect('@homepage?region_id=' . $region_id);
        }
      }
    }

    $filterChain->execute();
  }
}