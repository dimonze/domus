<?php

/**
 * qa actions.
 *
 * @package    domus
 * @subpackage qa
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class qaActions extends sfActions
{

  public function postExecute()
  {
    MetaParse::setMetas($this);
    $this->getUser()->setAttribute('post_type', 'qa');
  }

  /**
   * Executes index action
   *
   * @param sfRequest $request A request object
   */
  public function executeIndex(sfWebRequest $request) {

    //$user = $this->getUser();

    $this->getQADateList($request);

    $this->cache_prefix = sprintf(
        '%d_%d_',
        $request->getCookie('js_on'),
        $this->getUser()->current_region->id
    );

    $this->max_days_per_page = sfConfig::get('app_qa_max_days_per_index_page', 5);

    $this->setLayout('homepage');
  }

  public function executeTheme(sfWebRequest $request) {
    $this->forward404Unless($request->hasParameter('theme'));
    //$user = $this->getUser();

    $trans_tbl = DomusSearchRoute::$translit_table;
    $this->theme = $request->getParameter('theme');
    $qa_theme = str_replace(
        array_values($trans_tbl),
        array_keys($trans_tbl),
        $request->getParameter('theme')
    );

    $this->qa_theme = Doctrine::getTable('Theme')
        ->createQuery()
        ->select('title, id')
        ->andWhere('title = ?', $qa_theme)
        ->fetchOne();
    $this->forward404Unless($this->qa_theme);

    $this->current_theme = $this->qa_theme->id;

    $query = Doctrine::getTable('Post')->createQueryActive('p')
        ->select('p.title, p.lid, p.created_at')
        ->leftJoin('p.PostTheme t')
        ->andWhere('p.post_type = ?', 'qa')
        ->andWhere('p.created_at <= ?', date('Y-m-d H:i:s'))
        ->andWhere('t.theme_id = ?', $this->qa_theme->id)
        ->orderBy('p.created_at desc');

    $this->pager = new sfDoctrinePager('Post', sfConfig::get('app_qa_max_q_per_theme_page', 5));
    $this->pager->setQuery($query);
    $this->pager->setPage($request->getParameter('page', 1));
    $this->pager->init();

    $this->cache_prefix = sprintf('%d_%d_',
        $request->getCookie('js_on'),
        $this->getUser()->current_region->id
    );

    $this->setLayout('homepage');
  }

  public function executeShow(sfWebRequest $request) {
    $this->forward404Unless($request->hasParameter('id'));
    $post_type = 'qa';
    $user = $this->getUser();  
    $this->post = Doctrine::getTable('Post')->createQuery('p')
        ->separateThemes()
        ->andWhere('p.id = ?', $request->getParameter('id'))
        ->andWhere('p.post_type = ?', $post_type)
        ->fetchOne(); 
    $this->forward404Unless($this->post);
    if (!$this->post->title_h1) {
      sfConfig::set('no_metas', true);
    }
    $this->other_posts = Post::getOtherPostTypes($post_type);
    $this->post_themes = $this->post->getThemesArray();
    $user->setAttribute('post_type', $post_type);
    $user->setAttribute('post_type_name', Post::$types[$post_type]);
    $this->cache_prefix = sprintf(
      '%s_%d_%d_%d_',
      $post_type,
      $request->getCookie('js_on'),
      $this->getUser()->current_region->id,
      sfConfig::get('is_new_building')
    );

    $comment_form = new PostCommentForm();
    if ($request->hasParameter('comment')) {
      $data = $request->getParameter('comment');
      if ($user->isAuthenticated()) {
          $data['user_id'] = $user->id;
      }
      $data['post_id'] = $this->post->id;
      $comment_form->bind($data);
      if ($comment_form->isValid()) {
        $comment_form->save();
        $comment = $comment_form->getObject();

        //send PM
        $pm = new PM(null, true);
        $pm->sendCommentAsPm($comment);

        if ($request->isXmlHttpRequest())
        {
          return $this->renderText(json_encode(array('comment_id' => $comment->id)));
        }
      }
      else {
        return $this->renderText(json_encode(array('errors' =>  true)));
      }
    }
    $this->comment_form = $comment_form;
    $this->setLayout('homepage');

    $theme_ids = array();
    foreach ($this->post->Themes as $theme) {
      $theme_ids[] = $theme->id;
    }
    $this->qa_list = Doctrine::getTable('Post')->createQueryActive('p')
        ->leftJoin('p.PostTheme t')
        ->andWhere('p.post_type = ?', 'qa')
        ->andWhere('p.id != ?', $this->post->id)
        ->andWhereIn('t.theme_id', $theme_ids)
        ->orderBy('p.created_at desc')
        ->limit(5)
        ->execute();
    $this->getResponse()->addMeta('title', $this->post->title_h1);
    $this->getResponse()->addMeta('description', $this->post->description);
    $this->getResponse()->addMeta('keywords', $this->post->keywords);
  }

  public function executeAdd(sfWebRequest $request) {
    $this->user = $this->getUser();
    $this->cache_prefix = sprintf('%d_%d_',
        $request->getCookie('js_on'),
        $this->getUser()->current_region->id
    );
    $this->getQADateList($request);
    $form = new QAForm();    
    if ($request->isMethod('post')) {

      $data = $request->getPostParameter('qa');
      $form->bind($data);      
      if ($form->isValid()) {
        $form->save();
        $qa = $form->getObject();

        $theme_id = (int) $form->getValue('themes_list');
        $theme = Doctrine::getTable('Theme')->findOneById($theme_id);
        $qa->Themes[] = $theme;
        $qa->save();

        $user = $this->getUser();
        $user->active_count++;
        $user->save();

        $this->getUser()->setFlash('qa_success', 'Опрос отправлен на модерацию и будет опубликован после проверки модератором');

        $message = ''
        . 'Здравствуйте, {имя фамилия}!<br><br>'
        . 'В разделе "Вопросы и ответы" появился новый вопрос.<br><br>'
        . 'Чтобы его увидеть, перейдите по ссылке:<br>http://www.mesto.ru/qas<br><br><br><br>'
        . 'Отписаться от данной рассылки можно в разделе Мой кабинет -> Профайл и настройки.';


        sfGearmanProxy::doBackground('pm', array(
          'data'       => array(
            'subject'  => 'Новый вопрос на сайте mesto.ru',
            'message'  => $message,
            'priority' => 'high',
            'receiver' => 'aaa', //dirty hack
          ),
          'receivers'  => array('<QA_Subscribers>'),
          'sender'     => 1,
          'free_send'  => true,
        ));


        if ($request->isXmlHttpRequest()) {
          return $this->renderText(json_encode(array('valid' => true, 'locate' => '/qas')));
        }
        else {
          $this->redirect('qa');
        }
      }
      elseif ($request->getParameter('validate')) {
        return $this->renderText(json_encode($form->getErrorsArray()));
      }
    }
    
    $this->form = $form;
    $this->setLayout('homepage');
  }

  public function getQADateList(sfWebRequest $request) {
    sfContext::getInstance()->getConfiguration()->loadHelpers('Date');
    $query = Doctrine::getTable('Post')->createQueryActive('p')
        ->select('p.created_at')
        ->filterNewBuilding()
        //->leftJoin('p.PostRegion r')
        ->andWhere('p.post_type = ?', 'qa')
        //->andWhere('r.region_id = ?', $this->getUser()->current_region->id)
        ->groupBy('DATE(p.created_at)')
        ->orderBy('p.created_at desc');
    
    $this->pager = new sfDoctrinePager('Post', sfConfig::get('app_qa_max_days_per_index_page', 5));
    $this->pager->setQuery($query);
    $this->pager->setPage($request->getParameter('page', 1));
    $this->pager->init();

    $this->qa_dates = array();
    foreach ($this->pager->getResults() as $date) {
      $form_date = format_date($date->created_at, 'yyyy-MM-dd');
      $this->qa_dates[$form_date] = Doctrine::getTable('Post')
          ->getQaForDay($form_date)
          ->filterNewBuilding()
          ->execute();
    }
  }

}

