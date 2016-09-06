<?php

/**
 * pm actions.
 *
 * @package    domus
 * @subpackage pm
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class pmActions extends sfActions
{
  public function postExecute()
  {
    MetaParse::setMetas($this);
  }

  public function executeIndex(sfWebRequest $request)
  {
    $user = $this->getUser();
    $this->pagers = array();

    $query = Doctrine::getTable('PM')->createQueryReceived($user->id);
    $this->pagers['received'] = new sfDoctrinePager('PM', 20);
    $this->pagers['received']->setQuery($query);
    $this->pagers['received']->setPage($request->getParameter('received_page', 1));
    $this->pagers['received']->init();

    $query = Doctrine::getTable('PM')->createQuerySent($user->id);
    $this->pagers['sent'] = new sfDoctrinePager('PM', 20);
    $this->pagers['sent']->setQuery($query);
    $this->pagers['sent']->setPage($request->getParameter('sent_page', 1));
    $this->pagers['sent']->init();
  }

  public function executeDelete(sfWebRequest $request)
  {
    $type = $request->getParameter('type');

    $pm = Doctrine::getTable('PM')->findOneForUser(
      $this->getUser()->id,
      $request->getParameter('id'),
      $type
    );
    $pm->markAsDeleted($type);

    if ($request->isXmlHttpRequest()) {
      return $this->renderText('OK');
    }
    else {
      $this->redirect($request->getReferer() ? $request->getReferer() : '/');
    }
  }

  /**
   * Function for send messages from anonymous users
   * @param sfWebRequest $request
   * @return mixed
   */
  public function executeAddnologin(sfWebRequest $request)
  {
    $this->forward404Unless($request->isXmlHttpRequest());

    $user = $this->getUser();
    $form = new PMNoLoginForm();

    if ($request->hasParameter('to')) {
      $receiver = Doctrine::getTable('User')->find($request->getParameter('to'));
      if ($receiver) {
        $this->receiver = $receiver;
      }
    }

    if ($request->isMethod('post')) {
      $data = $request->getParameter('pm');

      if ($request->hasParameter('send_to')) {
        $send_to = Doctrine::getTable('User')->find($request->getParameter('send_to'));
        if ($send_to) {
          $this->receiver = $send_to;
          $data['receiver'] = $send_to->email;
        }
      }

      $form->bind($data);
      if ($form->isValid()) {
        $this->queueEmail($form->save());
        $user->setFlash('pm_send_success', 'Ваше сообщение отправлено.');
        return $this->renderText(json_encode(array('valid' => true, 'reload' => true)));
      }
      else {
        return $this->renderText(json_encode($form->getErrorsArray()));
      }
    }

    $this->checkPmSource();
    $this->form = $form;
  }

  public function executeAddcreateblog(sfWebRequest $request)
  {
    $form = new PMCreateBlogForm();
    $user = $this->getUser();

    $this->receiver = Doctrine::getTable('User')->find(sfConfig::get('app_blog_creator_user_id'));

    if ($request->isMethod('post')) {
      $data = $request->getParameter('pm');

      if ($request->hasParameter('send_to')) {
        $send_to = Doctrine::getTable('User')->find($request->getParameter('send_to'));
        if ($send_to) {
          $this->receiver = $send_to;
          $data['receiver'] = $send_to->email;
        }
      }

      $form->bind(array(
        'receiver'  => $data['receiver'],
        'blog_name' => $data['blog_name'],
        'blog_url'  => $data['blog_url'],
        'subject'   => 'Запрос на создание блога',
        'message'   => 'Здравствуйте, создайте пожалуйста блог "' . $data['blog_name'] .
                       '" c Url "' . $data['blog_url'] . '".'
      ));

      if ($form->isValid()) {
        $this->queueEmail($form->save());
        $user->setFlash('pm_send_success', 'Ваш запрос отправлен модератору.');
        return $this->renderText(json_encode(array('valid' => true, 'reload' => true)));
      }
      else {
        return $this->renderText(json_encode($form->getErrorsArray()));
      }
    }

    $this->form = $form;
  }

  public function executeAdd(sfWebRequest $request)
  {
    $form = new PMForm();
    $user = $this->getUser();

    if ($request->hasParameter('id')) {
      $pm = Doctrine::getTable('PM')->createQuery()
        ->where('id = ?', $request->getParameter('id'))
        ->andWhere('receiver = ? or sender = ?', array($user->id, $user->id))
        ->leftJoin('PM.UserSender')
        ->fetchOne();

      if ($pm) {
        $message = sprintf(
          "\n\n> %s %s\n&nbsp;&nbsp;%s",
          $pm->UserSender->name,
          date('H:i d.m.Y', strtotime($pm->sent_at)),
          $pm->message
        );

        $form->bind(array(
          'receiver' => $pm->UserSender->email,
          'subject' => 'RE: ' . $pm->subject,
          'message' => $message
        ));
        $this->receiver = $pm->UserSender;
      }
    }
    elseif ($request->hasParameter('moderator')) {
      $this->receiver = Doctrine::getTable('User')->find(sfConfig::get('app_feedback_user_id'));
    }
    elseif ($request->hasParameter('support')) {
      $this->receiver = Doctrine::getTable('User')->find(sfConfig::get('app_support_user_id'));
    }
    elseif ($request->hasParameter('to')) {
      $receiver = Doctrine::getTable('User')->find($request->getParameter('to'));
      if ($receiver) {
        $this->receiver = $receiver;
      }
    }

    if ($request->isMethod('post')) {
      $data = $request->getParameter('pm');

      if ($request->hasParameter('send_to')) {
        $send_to = Doctrine::getTable('User')->find($request->getParameter('send_to'));
        if ($send_to) {
          $this->receiver = $send_to;
          $data['receiver'] = $send_to->email;
        }
      }

      $form->bind($data);
      if ($form->isValid()) {
        $this->queueEmail($form->save());
        $user->setFlash('pm_send_success', 'Ваше сообщение отправлено.');
        return $this->renderText(json_encode(array('valid' => true, 'reload' => true)));
      }
      else {
        return $this->renderText(json_encode($form->getErrorsArray()));
      }
    }

    $this->checkPmSource();

    $this->form = $form;
  }

  public function executeAddmoderator(sfWebRequest $request)
  {
    $user = $this->getUser();
    if (!$user->hasCredential('moder-actions')) {
      $this->forward404();
    }
    $referer = basename($request->getReferer());
    if ($referer != 'moderate') {
      $this->location = 'homepage';
    }

    $form = new PMModeratorForm();

    $file = sfConfig::get('sf_config_dir') . '/email_themes.yml';
    $this->themes = sfYaml::load($file);

    if ($request->hasParameter('lot_id')) {
      $lot_ids = explode(',', $request->getParameter('lot_id'));
      if (is_array($lot_ids)) {
        $this->lot = array();
        foreach ($lot_ids as $lot_id) {
          $lot = Doctrine::getTable('Lot')->find($lot_id);
          if ($lot) {
            $this->lot[] = $lot;
          }
        }
      }
    }

    if ($request->hasParameter('to')) {
      $user_ids = explode(',', $request->getParameter('to'));
      if (is_array($user_ids)) {
        $this->receiver = array();
        foreach ($user_ids as $user_id) {
          $receiver = Doctrine::getTable('User')->find($user_id);
          if ($receiver) {
            $this->receiver[] = $receiver;
          }
        }
      }
      else {
        $receiver = Doctrine::getTable('User')->find($user_id);
        if ($receiver) {
          $this->receiver = $receiver;
        }
      }
    }

    if ($request->isMethod('post')) {
      $data = $request->getParameter('pm');

      if ($request->hasParameter('location')) {
        $redirect = $request->getParameter('location');
      }

      if ($request->hasParameter('send_to')) {
        $send_to = explode('; ', $request->getParameter('send_to'));
        if (is_array($send_to)) {
          $this->receiver = array();
          foreach ($send_to as $user_id) {
            $user_id = Doctrine::getTable('User')->find($user_id);
            $this->receiver[] = $user_id;
          }
        }
      }

      if (isset($data['lot_id'])) {
        $lot_ids = explode(',', $data['lot_id']);
        $this->lot = array();
        foreach ($lot_ids as $lot_id) {
          // Even if not found to preserve order
          $this->lot[] = Doctrine::getTable('Lot')->find($lot_id);
        }
      }

      $form_valid = false;
      $message = $data['message'];
      foreach ($this->receiver as $key => $receiver) {
        $data['message'] = $message;
        $data['receiver'] = $receiver->email;

        $data['message'] = preg_replace('/{имя фамилия}/', $receiver->name, $data['message']);

        if (!empty($this->lot[$key])) {
          $data['message'] = preg_replace(
            '/{адрес объявления}/',
            sprintf(
              '<a href="%s" class="address_full">%s</a>',
              $this->generateUrl('lot_action', array('id' => $this->lot[$key]->id, 'action' => 'edit')),
              $this->lot[$key]->address_full
            ),
            $data['message']
          );
        }
        elseif (isset($lot_ids) && strpos($data['message'], '{адрес объявления}')) {
          $user->setFlash('pm_send_error_l' . $key, sprintf(
            'Объявление id=%d не найдено.', $lot_ids[$key])
          );
          continue;
        }

        $form->bind($data);

        if ($form->isValid()) {
          $pm_id = $form->save();
          $this->queueEmail($pm_id);

          if (!empty($this->lot[$key])) {
            $lot = Doctrine::getTable('Lot')->find($this->lot[$key]->id);
            if ($lot && $lot->restrict($pm_id)) {
              $lot->save();
            }
          }
        }
      }

      $user->setFlash('pm_send_success', 'Ваше сообщение отправлено.');

      if (!empty($redirect)) {
        return $this->renderText(json_encode(array(
              'valid' => true,
              'reload' => true,
              'location' => $redirect
        )));
      }
      else {
        return $this->renderText(json_encode(array('valid' => true, 'reload' => true)));
      }
    }

    $this->form = $form;
  }

  public function executeAddadminmessage(sfWebRequest $request)
  {
    $user = $this->getUser();
    if (!$user->hasCredential('admin-access')) {
      $this->forward404();
    }

    $form = new PMAdminForm();
    $file = sfConfig::get('sf_config_dir') . '/email_themes.yml';
    $this->themes = sfYaml::load($file);

    if ($request->hasParameter('to') && $request->isMethod('post')) {
      $user_ids = explode(',', $request->getParameter('to'));
      if ($request->getParameter('to') == '<All>') {
        $this->receiver = array('<All>');
      }
      else {
        $this->receiver = array();
        foreach ($user_ids as $user_id) {
          $receiver = Doctrine::getTable('User')->find($user_id);
          if ($receiver) {
            $this->receiver[] = $receiver;
          }
        }
      }
    }
    else if ($request->isMethod('post')) {
      $data = $request->getParameter('pm');

      if ($request->hasParameter('send_to')) {
        if ($request->getParameter('send_to') == '<All>') {
          $this->receiver = array('<All>');
        }
        else {
          $send_to = explode('; ', $request->getParameter('send_to'));
          if (is_array($send_to)) {
            $this->receiver = array();
            foreach ($send_to as $user_id) {
              $user_id = Doctrine::getTable('User')->find($user_id, Doctrine::HYDRATE_ARRAY);
              $v = array('id' => $user_id['id'], 'email' => $user_id['email']);
              $this->receiver[] = $v;
            }
          }
        }
      }

      $settings = array();

      if ($request->hasParameter('email_send')) {
        $settings['email_send'] = true;
      }
      if ($request->hasParameter('pm_send')) {
        $settings['pm_send'] = true;
      }
      $data['receiver'] = 'aaa'; //dirty hack
      $form->bind($data);

      if ($form->isValid()) {
        sfGearmanProxy::doBackground('pm', array(
          'data'       => $data,
          'receivers'  => $this->receiver,
          'sender'     => $this->getUser()->id,
          'send_pm'    => $request->hasParameter('pm_send'),
          'send_email' => $request->hasParameter('email_send'),
        ));

        $user->setFlash('pm_send_success', 'Ваше сообщение отправлено.');
        return $this->renderText(json_encode(array('valid' => true, 'reload' => true)));
      }
      else {
        return $this->renderText(json_encode(array_merge($form->getErrorsArray(), array('form_not_valid' => 'true'))));
      }
    }

    $this->form = $form;
  }

  public function executeGetmessage(sfWebRequest $request)
  {
    $user = $this->getUser();
    $pm = Doctrine::getTable('PM')->createQuery()
      ->where('id = ?', $request->getParameter('id'));

    if (!$user->hasCredential('moder-access')) {
      $pm->andWhere('receiver = ? or sender = ?', array($user->id, $user->id));
    }

    $pm = $pm->fetchOne();
    $this->forward404Unless($pm);

    if (!$pm->red && $pm->receiver == $user->id) {
      $pm->red = true;
      $pm->save();
    }

    if ($request->hasParameter('only-text')) {
      return $this->renderText(nl2br(strip_tags($pm->message)));
    }

    $this->pm = $pm;
  }

  public function executeReadandnext(sfWebRequest $request){

    $user = $this->getUser();
    $pm = Doctrine::getTable('PM')->createQuery()
      ->andWhere('id = ?',$request->getParameter('id'))
      ->andWhere('receiver = ?', $user->id)
      ->fetchOne();

    $this->forward404Unless($pm);

    if (!$pm->red) {
      $pm->red = true;
      $pm->save();
    }

    $next = Doctrine::getTable('PM')->createQuery()
    ->select('id, sent_at, subject, message, priority')
    ->andWhere('priority != ?', 'none')
    ->andWhere('red = ?', 0)
    ->andWhere('receiver = ?', sfContext::getInstance()->getUser()->id)
    ->limit(1)
    ->offset(2)
    ->orderBy('sent_at desc')
    ->fetchOne();

    $message_class = array(
      'low'  => 'info-message',
      'mid'  => 'finance-message',
      'high' => 'warn-message',
    );

    if($next) {
      return $this->renderText(json_encode(array(
        'id'       => $next->id,
        'subject'  => $next->subject,
        'message'  => mb_substr($next->message,0,500,'utf-8'),
        'date'     => date('H:i d.m.Y' ,strtotime($next->sent_at)),
        'priority' => $message_class[$next->priority],
      )));
    }
    else{
      return $this->renderText(json_encode(array('id' => false)));
    }

  }

  private function checkPmSource()
  {
    $referer = $this->getRequest()->getReferer();
    $types = sfConfig::get('app_lot_types');

    foreach ($types as $key => $type) {
      if (preg_match('/' . $key . '/', $referer)) {
        $url = explode('/', $referer);
        $lot_id = $url[count($url) - 1];
        $lot = Doctrine::getTable('Lot')->find(array($lot_id));

        $this->subject = sprintf('Вопрос о %s: %s, %s', $type['pm-ask'], $lot->address1, $lot->address2);
        return;
      }
    }
  }

  protected function queueEmail($pm_id)
  {
    sfGearmanProxy::doBackground('pm_send', array(
      'pm_id'      => $pm_id,
      'send_email' => true,
    ));
  }
}