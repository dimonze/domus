<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Blog extends BaseBlog
{
  public static
    $_status = array(
      'active'      => 'Активен',
      'inactive'    => 'Неактивен',
      'moderate'    => 'Ожидает модерации',
      'restricted'  => 'Запрещён'
    );

  public function getLatestPosts() {
    return Doctrine::getTable('BlogPost')->createQuery()
      ->andWhere('status = ?', 'publish')
      ->andWhere('blog_id = ?', $this->id)
      ->limit(sfConfig::get('app_blog_max_on_index_page', 3))
      ->orderBy('created_at DESC')
      ->execute();
  }
  /**
   * get author name
   * @return User object
   */
//  public function getAuthorName()
//  {
//    return Doctrine::getTable('BlogAuthor')->find($this->author_id);
//  }

//  public function getAuthorBlogsTableProxy()
//  {
//    return Doctrine::getTable('Blog')->createQuery('p')
//      ->leftJoin('p.BlogAuthor a')
//      ->andWhere('a.author_type = ?', 'author')
//      ->execute();
//  }

//  public function getExpertBlogsTableProxy()
//  {
//    return Doctrine::getTable('Blog')->createQuery('p')
//      ->leftJoin('p.BlogAuthor a')
//      ->andWhere('a.author_type = ?', 'expert')
//      ->execute();
//  }
}