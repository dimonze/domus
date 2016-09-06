<?php

/**
 * blogs actions.
 *
 * @package    domus
 * @subpackage blogs
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class blogsActions extends sfActions {

  public function postExecute()
  {
    MetaParse::setMetas($this);
    $this->action = $this->getActionName();
  }

  /**
   * Executes index action
   *
   * @param sfRequest $request A request object
   */
  public function preExecute()
  {
    parent::preExecute();
    $request = $this->getRequest();
    $this->cache_prefix = sprintf(
        '%d_%d_',
        $request->getCookie('js_on'),
        $this->getUser()->current_region->id
    );

    $this->themes = Theme::getThemesWithActiveBlogPosts();
  }

  public function executeIndex(sfWebRequest $request)
  {
    $user = $this->getUser();
    if ($request->hasParameter('sort_order')) {
      $order = $request->getParameter('sort_order');
    }
    else {
      $order = 'created_at-desc';
    }
    switch ($order) {
      case 'created_at-desc':
        $sort_order = 'p.created_at desc';
        $this->sort_order_date = 'asc';
        break;
      case 'created_at':
        $sort_order = 'p.created_at asc';
        $this->sort_order_date = 'desc';
        break;
      case 'author-desc':
        $sort_order = 'u.name desc';
        $this->sort_order_author = 'asc';
        break;
      case 'author':
        $sort_order = 'u.name asc';
        $this->sort_order_author = 'desc';
        break;
      default:
        $sort_order = 'p.created_at desc';
    }

    $blogs = Doctrine::getTable('Blog')->createQuery('a')
        ->select('a.*')
        ->leftJoin('a.BlogPost p')
        ->leftJoin('a.User u')
        ->andWhere('p.status = ?', 'publish')
        ->andWhere('a.status = ?', 'active')
        ->groupBy('a.id')
        ->orderBy($sort_order);
    $this->pager = new sfDoctrinePager('Blog', sfConfig::get('app_blog_my_max_per_page', 3));
    $this->pager->setQuery($blogs);
    $this->pager->setPage($request->getParameter('page', 1));
    $this->pager->init();


    $this->setLayout('homepage');
  }

  public function executeTheme(sfWebRequest $request)
  {
    $this->forward404Unless($request->hasParameter('theme'));
    $user = $this->getUser();

    $trans_tbl = DomusSearchRoute::$translit_table;
    $this->theme = $request->getParameter('theme');
    $theme = str_replace(array_values($trans_tbl), array_keys($trans_tbl), $this->theme);
    $this->post_theme = Doctrine::getTable('Theme')->createQuery()
        ->select('title, id')
        ->andWhere('title = ?', $theme)
        ->fetchOne();
    $this->forward404Unless($this->post_theme);

    $query = Doctrine::getTable('BlogPost')->createQuery('p')
        ->andWhere('p.status = ?', 'publish')
        ->andWhere('p.theme_id = ?', $this->post_theme->id)
        ->orderBy('p.created_at desc');

    $this->pager = new sfDoctrinePager('BlogPost', sfConfig::get('app_blog_my_max_per_theme_page', 10));
    $this->pager->setQuery($query);
    $this->pager->setPage($request->getParameter('page', 1));
    $this->pager->init();

    $this->setLayout('homepage');
  }

  public function executeAdd(sfWebRequest $request)
  {
    $user = $this->getUser();
    $this->forward404Unless('active' == $user->Blog->status);
    $form = new BlogPostForm();

    if ($request->isMethod('post')) {
      $data = $request->getParameter('blog_post');
      $form->bind($data);
      if ($form->isValid()) {
        $form->save();
        $bp = $form->getObject();
        $bp->blog_id = $user->Blog->id;
        $bp->save();

        $user = $this->getUser();
        $user->active_count++;
        $user->save();

        if ($request->isXmlHttpRequest()) {
          return $this->renderText(json_encode(array('valid' => true, 'locate' => '/blogs/my')));
        }
        else {
          $this->getUser()->setFlash('qa_success', 'Запись добавлена в блог.');
          $this->redirect('blog_post_my');
        }
      }
      elseif ($request->getParameter('validate')) {
        return $this->renderText(json_encode($form->getErrorsArray()));
      }
    }
    $this->form = $form;
    $this->setLayout('homepage');
  }

  public function executeEditpost(sfWebRequest $request)
  {
    $user = $this->getUser();
    $this->forward404Unless('active' == $user->Blog->status);

    $this->post = Doctrine::getTable('BlogPost')->find($request->getParameter('id'));
    $this->forward404Unless($this->post || ($user->Blog !== $this->post->Blog));

    $form = new BlogPostForm($this->post);

    if ($request->isMethod('post')) {
      $data = $request->getParameter('blog_post');
      $form->bind($data);
      if ($form->isValid()) {
        $bp = $form->save();
        $bp->Blog = $user->Blog;
        $bp->save();

        if ($request->isXmlHttpRequest()) {
          return $this->renderText(json_encode(array('valid' => true, 'locate' => '/blogs/my')));
        }
        else {
          $user->setFlash('success', 'Запись успешно изменена.');
          $this->redirect('/blog/' . $bp->Blog->url . '/' . $bp->id . '/edit');
        }
      }
      elseif ($request->getParameter('validate')) {
        return $this->renderText(json_encode($form->getErrorsArray()));
      }
    }
    else {
      
    }

    $this->form = $form;
    $this->setLayout('homepage');
  }

  public function executePublishpost(sfWebRequest $request)
  {
    $this->forward404Unless($request->isXmlHttpRequest());

    $user = $this->getUser();
    $this->forward404Unless('active' == $user->Blog->status);

    $post = Doctrine::getTable('BlogPost')->find($request->getParameter('id'));
    $this->forward404Unless($post || ($user->Blog !== $post->Blog));

    if ('publish' == $post->status || 'not_publish' == $post->status) {
      $post->status = ('publish' == $post->status) ? 'not_publish' : 'publish';
      $valid = ('publish' == $post->status) ? 'publish' : 'not_publish';
      $post->save();
    } else
      $valid = false;

    return $this->renderText(json_encode(array('valid' => $valid)));
  }

  public function executeDeletepost(sfWebRequest $request)
  {
    $this->forward404Unless($request->isXmlHttpRequest());

    $user = $this->getUser();
    $this->forward404Unless(count($user->Blog));

    $post = Doctrine::getTable('BlogPost')->find($request->getParameter('id'));
    $this->forward404Unless($post || ($user->Blog !== $post->Blog));

    $post->delete();

    return $this->renderText(json_encode(array('deleted' => 'ok')));
  }

  public function executeMy(sfWebRequest $request)
  {
    $user = $this->getUser();

    $this->user = $user;
    if (('active' == $user->Blog->status)) {
      $query = Doctrine::getTable('BlogPost')->createQuery()->andWhere('blog_id = ?', $user->Blog->id);

      $this->pager = new sfDoctrinePager('BlogPost', sfConfig::get('app_blog_my_max_per_page'));
      $this->pager->setQuery($query);
      $this->pager->setPage($request->getParameter('page', 1));
      $this->pager->init();
    }

    $this->setLayout('homepage');
  }

  public function executeShow(sfWebRequest $request)
  {
    $blog = Doctrine::getTable('Blog')->getActiveBlog($request->getParameter('blog_url'));
    $this->forward404Unless($blog);
    $this->forward404Unless('active' == $blog->status);

    $this->blog = $blog;
    $this->current_blog_id = $blog->id;
    $this->user = $blog->User;
    $query = Doctrine::getTable('BlogPost')->getActiveBlogPostsQuery($blog->id);

    $this->pager = new sfDoctrinePager('BlogPost', sfConfig::get('app_blog_my_max_per_page'));
    $this->pager->setQuery($query);
    $this->pager->setPage($request->getParameter('page', 1));
    $this->pager->init();

    $this->setLayout('homepage');
  }

  public function executeShowpost(sfWebRequest $request)
  {
    $this->forward404Unless($request->hasParameter('id') && $request->hasParameter('blog_url'));

    $this->post = Doctrine::getTable('BlogPost')->find($request->getParameter('id'));
    $this->forward404Unless('active' == $this->post->Blog->status);
    $this->forward404Unless($this->post);
    $this->forward404If($this->post->status != 'publish');
    $user = $this->getUser();
    $this->author = $this->post->Blog->User;
    if ($user->isAuthenticated()) {
      $comment_form = new BlogPostCommentForm();
      if ($request->hasParameter('comment')) {
        $data = $request->getParameter('comment');
        $data['user_id'] = $user->id;
        $data['post_id'] = $this->post->id;

        $comment_form->bind($data);
        if ($comment_form->isValid()) {
          $comment_form->save();
          $comment = $comment_form->getObject();
          if ($request->isXmlHttpRequest()) {
            return $this->renderPartial('comments/comment', array(
              'comment' => $comment,
              'section' => 'author_article'
            ));
          }
          else {
            $this->redirect($this->generateUrl('blog_post_show', array(
                'blog_url' => $this->post->Blog->url,
                'id' => $this->post->id
              )) . '#comment-' . $comment->id);
          }
        }
      }
      $this->comment_form = $comment_form;
    }

    $this->setLayout('homepage');
  }

}
