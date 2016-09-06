<?php

/**
 * user actions.
 *
 * @package    domus
 * @subpackage user
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class userActions extends sfActions
{

  public function preExecute()
  {
    $no_logged = array('login', 'register', 'reset');
    $request = $this->getRequest();
    $user = $this->getUser();

    if (in_array($this->getActionName(), $no_logged) && $user->isAuthenticated()) {
      $this->redirect('@homepage');
    }
  }

  public function postExecute()
  {
    MetaParse::setMetas($this);
  }


  public function executeLogin(sfWebRequest $request)
  {
    if (!$request->isXmlHttpRequest()) {
      $this->redirect('/#c:login;forward:' . urlencode($request->getUri()));
    }

    $form = new UserLoginForm();

    if ($request->isMethod('post')) {
      $form->bind($request->getParameter('user'));
      if ($form->isValid()) {
        if ($request->getParameter('forward')) {
          $url = $request->getParameter('forward');
        }
        elseif ($request->getReferer()) {
          $url = $request->getReferer();
        }
        else {
          $url = '/';
        }
        $user = $form->getObject();
        if ($user->inactive == true){
          $user->inactive = false;
          $user->save();
          $this->getUser()->setFlash('activate_success', 'Ваша учётная запись снова активирована.');
        }
        if ($request->isXmlHttpRequest()) {
          return $this->renderText(json_encode(array('valid' => true, 'locate' => $url)));
        }
        else {
          $this->redirect($url);
        }
      }
      else {
        return $this->renderText(json_encode($form->getErrorsArray()));
      }
    }

    $this->form = $form;
  }

  public function executeLogout(sfWebRequest $request)
  {
    $this->getUser()->processLogout();
    $this->redirect($request->getReferer());
  }

  public function executeRegister(sfWebRequest $request)
  {

    if (!$request->isXmlHttpRequest()) {
      $url = '/#c:register';
      if ($request->hasParameter('invite')) {
        $url .= ';user[type]:employee;user[invite]:' . $request->getParameter('invite');
      }
      $this->redirect($url);
    }

    $form = new UserRegisterForm();

    if ($request->isMethod('post')) {
      $data = $request->getParameter('user');
      if (!empty($data['type'])) {
        $form->changeWidgets($data['type']);
      }

      $form->bind($data);
      if ($form->isValid()) {
        $this->getUser()->setFlash('retarget', true);

        $user = $form->getObject();
        $user->save();

        if (!empty($data['invite'])) {
          $invite = Doctrine::getTable('Invite')->createQuery()
              ->andWhere('email = ?', $data['email'])
              ->andWhere('code = ?', $data['invite'])
              ->fetchOne();
          if ($invite) {
            $invite->delete();
          }
        }

        $user->Info->email_confirmation = Toolkit::generatePassword(8);
        $user->Info->save();

        $url = $this->generateUrl(
          null,
          array(
            'module' => 'user',
            'action' => 'confirm',
            'code' => $user->Info->email_confirmation,
          ),
          true
        );
        DomusMail::create()
          ->addTo($user->email)
          ->setSubject('Подтверждение адреса на сайте ' . sfConfig::get('app_site'))
          ->setBodyHtml(sprintf('
              <p>Вы зарегистрировались на сайте %s</p>
              <p>Для того чтобы подтвердить ваш адрес пройдите по ссылке - <a href="%2$s">%2$s</a></p>
              ', sfConfig::get('app_site'), $url))
          ->send();
        $this->getUser()->setFlash('register_success', 'Для того чтобы подтвердить свой адрес эл. почты пройдите по ссылке в письме.');


        return $this->executeLogin($request);
      }
      elseif ($request->getParameter('validate')) {
        return $this->renderText(json_encode($form->getErrorsArray()));
      }
    }

    $this->form = $form;
  }

  public function executeConfirm (sfWebRequest $request)
  {
    $user = $this->getUser();
    if ($user->Info->email_confirmation == $request->getParameter('code')) {
      $user->Info->email_confirmation = 1;
      $user->Info->save();
      $user->setFlash('confirm_success', 'Вы подтвердили свой адрес эл. почты!');
    }
    else {
      $user->setFlash('confirm_error', 'Не удалось подтвердить адрес эл. почты!');
    }
    $this->redirect('user/profile');
  }

  public function executeReset(sfWebRequest $request)
  {
    $form = new UserResetForm();

    if ($request->isMethod('post'))
    {
      $form->bind($request->getParameter('user'));
      if ($form->isValid()) {
        $msg = 'Письмо выслано! Для смены пароля пройдите по ссылке указанной в письме.';
        $this->getUser()->resetPassword($form->getValues());

        if ($request->isXmlHttpRequest()) {
          return $this->renderText(json_encode(array('valid' => true, 'message' => $msg)));
        }
        else {
          $this->msg = $msg;
          $this->success = true;
        }
      }
      else {
        return $this->renderText(json_encode($form->getErrorsArray()));
      }
    }

    $this->form = $form;
  }

  public function executeUnsubscribe(sfWebRequest $request)
  {
    $this->forward404Unless($request->getParameter('email') && $request->getParameter('hash'));
    $email = urldecode(str_replace('~', '.', $request->getParameter('email')));
    $notification = Doctrine::getTable('Notification')->createQuery()
                      ->where('email = ?', $email)
                      ->andWhere('md5(concat(model, field, pk, period)) = ?',
                                 $request->getParameter('hash'))
                      ->fetchOne();
    if ($notification) {
      $notification->delete();
      $this->result = 'Вы успешно отписаны!';
    }
    else {
      $this->result = 'К сожалению, не удалось найти вашу подписку.';
    }
  }

  public function executeSetpassword(sfWebRequest $request)
  {
    $this->forward404Unless($request->hasParameter('key'));

    $this->getUser()->processLogout();
    $form = new UserSetPasswordForm();
    $key = Doctrine::getTable('UserResetKey')->createQuery()
        ->andWhere('reset_key = ?', $request->getParameter('key'))->fetchOne();

    $this->forward404Unless($key);

    if ($request->isMethod('post')) {
      $form->bind($request->getParameter('user', array()));
      if ($form->isValid()) {
        $user = Doctrine::getTable('User')->find($key->user_id);
        $user->password = md5($form->getValue('password'));
        $user->save();

        $key->delete();

        $this->getUser()->processLogin(array('id' => $user->id, 'remember' => true));
        $this->getUser()->setFlash('password_success', 'Вы успешно сменили пароль');


        $this->redirect('@homepage');
      }
    }

    $this->form = $form;
  }

  public function executeProfile(sfWebRequest $request)
  {
    $user = $this->getUser();

    if ($request->getParameter('edit')) {
      $form = new UserInfoForm($user->Info, array('user_type' => $user->type));
      $user_image_form = new UserImageForm($user->type);
      $this->user_phone = Toolkit::unformatPhoneNumber($this->getUser()->phone);
      if ($user->type == 'company') {
        $this->image_name = 'Логотип';
      }
      else {
        $this->image_name = 'Фото';
      }

      if ($request->isMethod('post')) {
        $user_data = Toolkit::escape($request->getParameter('user', array()));
        if ($user->type == 'company' && !empty($user_data['company_name'])) {
          $user->company_name = $user_data['company_name'];
        }
        if (!empty($user_data['name'])) {
          $user->name = $user_data['name'];
        }
        $this->user_phone = $user_data['phone'];
        $phone = Toolkit::formatPhoneNumber($user_data['phone']['country'], $user_data['phone']['area'], $user_data['phone']['number']);
        $phone_error = false;
        if(null === $phone || strlen($phone) > 18 )  {
          $user->setFlash('phone_error', 'Телефон введен нeправильно');
          $phone_error = true;
        }
        else {
          $query = Doctrine::getTable('User')->createQuery()
                  ->select('count(id) as cnt')
                  ->andWhere('id != ?')
                  ->andWhere('phone = ?')
                  ->execute(array($user->id, $phone), Doctrine::HYDRATE_ARRAY);
          if($query[0]['cnt'] == 1) {
            $phone_error = true;
            $user->setFlash('phone_error', 'Телефон используется другим пользователем. Введите другой.');
          } 
          else {
            $user->phone = $phone;
          }
        }
        $user->save();

        //Save Image
        if ($user_data['photo']) {
          $user_image_form->bind(array('photo' => $user_data['photo']));
          if ($user_image_form->isValid()) {
            if (!$user_image_form->save()) {
             $user->setFlash('profile_image_error', 'Не удалось сохранить изображение.');
            }
          }
          else {
            $user->setFlash('profile_image_error', 'Не удалось загрузить изображение.');
          }
        }
        $form->bind(Toolkit::escape($request->getParameter('user_info', array())));
        if ($form->isValid() && !$phone_error) {
          $user->Info->fromArray($form->getValues());
          $user->save();
          $user->setFlash('profile_success', 'Ваш профайл обновлен!');
          $this->redirect('user/profile');
        }
        elseif (!$phone_error) {
          $user->setFlash('profile_error', 'Форма содержит ошибки.');
        }
      }

      $this->form = $form;
      $this->user_image_form = $user_image_form;
      $this->setTemplate('profileedit');
    }
    else {
      if ($request->isMethod('post')) {
        if (md5($request->getParameter('delete')) == $user->password) {
          $user->inactive();
          $this->getUser()->processLogout();
          $user->setFlash('account_inactive_success', 'Процесс деактивации запущен. Для активации снова войдите.');
          $this->redirect('@homepage');
        }
      }
    }
  }

  public function executeCard(sfWebRequest $request)
  {
    $user = $this->getRoute()->getObject();
    $this->forward404If($user->is_inner);
    $this->forward404If($user->inactive == true);
    sfConfig::set('banner', 'usercard');
    $this->getResponse()->addMeta('name', $user->type == 'company' ? $user->company_name . ($user->approved ? '<div class="approved">Проверена</div>' : '') : $user->name);

    $this->lots = array();
    foreach (sfConfig::get('app_lot_types') as $type => $type_names) {
      $this->lots[$type] = array(
        'name'    => $type_names['plural'],
        'nb_lots' => Doctrine::getTable('Lot')->createQueryActive()
            ->andWhereIn('user_id', $user->lots_user_id)
            ->andWhere('type = ?', $type)
            ->count(),
      );

      if ($this->lots[$type]['nb_lots'] > 0) {
        $this->lots[$type]['lots'] = Doctrine::getTable('Lot')->createQueryActiveList()
            ->andWhereIn('user_id', $user->lots_user_id)
            ->andWhere('type = ?', $type)
            ->orderBy('active_till desc')
            ->limit(5)
            ->execute();
      }
    }

    $this->user = $user;
    $request->setParameter('user_card', $user);
  }

  public function executeInvite (sfWebRequest $request)
  {
    $user = $this->getUser();
    $this->redirectUnless($user->type == 'company', '/');

    switch ($request->getParameter('do')) {
      case 'send':
        $this->sendInvite();
        break;

      case 'cancel':
        $invite = Doctrine::getTable('Invite')->createQuery()
            ->andWhere('user_id = ?', $user->id)
            ->andWhere('code = ?', $request->getParameter('code'))
            ->andWhere('email = ?', $email = $request->getParameter('email'))
            ->fetchOne();
        if ($invite) {
          $invite->delete();
          $user->setFlash('invite_cancel_success', sprintf('Приглашение для %s было отменено', $email));
        }
        else {
          $user->setFlash('invite_cancel_error', sprintf('Не удалось отменить приглашение для %s', $email));
        }
        break;
    }

    if ($request->getParameter('do')) {
      $this->redirect('user/invite');
    }

    $this->invites = Doctrine::getTable('Invite')->createQuery()
        ->andWhere('user_id = ?', $user->id)
        ->orderBy('created_at')
        ->execute();
  }

  private function sendInvite()
  {
    $user = $this->getUser();

    if ($email = $this->getRequest()->getParameter('email')) {
      // validating email

      //step 1
      $validator = new sfValidatorEmail(array('trim' => true));
      try {
        $email = $validator->clean($email);
      }
      catch (sfValidatorError $e) {
        $user->setFlash('invite_send_error', sprintf('Некорректный адрес: %s', $email));
        return false;
      }

      //step 2
      $query = Doctrine::getTable('User')->createQuery()->where('email = ?', $email);
      if ($query->count()) {
        $user->setFlash('invite_send_error', sprintf('Аккаунт с адресом %s уже существует.', $email));
        return false;
      }

      //step 3
      $query = Doctrine::getTable('Invite')->createQuery()->where('email = ?', $email);
      if ($query->count()) {
        $user->setFlash('invite_send_error', sprintf('На адрес %s уже существует приглашение.', $email));
        return false;
      }

      // finaly we can send
      $invite = new Invite();
      $invite->fromArray(array(
        'code' => Toolkit::generatePassword(12),
        'email' => $email,
        'user_id' => $user->id,
      ));
      $invite->save();

      $url = $this->generateUrl(
        null,
        array(
          'module' => 'user',
          'action' => 'register',
          'invite' => $invite->code,
        ),
        true);
      DomusMail::create()
        ->addTo($email)
        ->setSubject('Приглашение на сайт ' . sfConfig::get('app_site'))
        ->setBodyHtml(sprintf('
            <p>Вы получили приглашение зарегистрироваться на сайте %s как сотрудник компании %s</p>
            <p>Для того чтобы зарегистрироваться пройдите по ссылке - <a href="%4$s">%4$s</a></p>
            <p>Ваш код - %s</p>
            ', sfConfig::get('app_site'), $user->company_name, $invite->code, $url))
        ->send();

      $user->setFlash('invite_send_success', sprintf('Приглашение успешно выслано на адрес %s', $email));
      return true;
    }

    return false;
  }

  public function executeLots(sfWebRequest $request)
  {
    $user = Doctrine::getTable('User')->find($request->getParameter('id'));
    $this->forward404Unless($user);

    $this->getResponse()->addMeta('name', $user->type == 'company' ? $user->company_name : $user->name);

    $query = Doctrine::getTable('Lot')->createQueryActiveList()
        ->andWhereIn('user_id', $user->lots_user_id)
        ->andWhere('type = ?', $request->getParameter('type'))
        ->orderBy('active_till desc');

    $this->pager = new sfDoctrinePager('Lot', 10);
    $this->pager->setPage($request->getParameter('page', 1));
    $this->pager->setQuery($query);
    $this->pager->init();

    $names = sfConfig::get('app_lot_types');
    $this->name = $names[$request->getParameter('type')]['plural'];
    $request->setParameter('user_card', $user);
  }

  public function executeRating(sfWebRequest $request)
  {
    $type = $request->getParameter('type');
    $form = new RatingFilterForm($type);
    $ns = 'filter/rating/' . $type;

    if ($request->isMethod('post')) {
      $this->getUser()->setAttribute('data', $request->getParameter('filter', array()), $ns);
    }
    $form->bind($this->getUser()->getAttribute('data', array(), $ns));
    if ($request->isMethod('get')) {
      $form->bind(array('region_id' => $this->getUser()->current_region->id));
    }

    if ($form->isValid()) {
      $query = $form->getQuery();

      if ($query) {
        $this->pager = new sfDoctrinePager('User', 10);
        $this->pager->setPage($request->getParameter('page', 1));
        $this->pager->setQuery($query);
        $this->pager->init();
      }
      else {
        $this->pager = null;
      }
    }

    $this->form = $form;
    $this->getResponse()->addMeta('name', 'Рейтинг ' . ($type == 'company' ? 'компаний' : 'риэлторов'));
  }

  public function executeSavesettings(sfWebRequest $request)
  {
    $user = $this->getUser();

    $settings = $request->getParameter('settings', array());
    foreach (UserSettings::$defaults as $name => $default) {
      $user->setSettingsValue($name, isset($settings[$name]) ? $settings[$name] : null);
    }

    $user->setFlash('settings_save_success', 'Настройки успешно сохранены');
    $this->redirect($request->getReferer());
  }

  public function executeDeleteuserimage (sfWebRequest $request)
  {
    $user = $this->getUser();

    $this->forward404Unless($user->hasCredential('admin-user-actions'));
    $this->forward404Unless($request->hasParameter('user_id'));
    $this->forward404Unless($request->isXmlHttpRequest());

    $user = Doctrine::getTable('User')->find($request->getParameter('user_id'));
    if ($user instanceof User){
      $delete_image = $user->deletePhoto();
      if ($delete_image){
        $user->setSettingsValue('show_rating', NULL);
        return $this->renderText(json_encode(array('delete_image' => $delete_image,'image' => 'deleted')));
      }
      else {
        return $this->renderText(json_encode(array('image' => 'not deleted')));
      }
    }
  }

  public function executeEmployees (sfWebRequest $request)
  {
    $user = $this->getUser();
    $this->forward404Unless($user->type == 'company');
    if ($request->getParameter('mode') == 'kick_out' && $request->getParameter('user_id')) {
      $unemployed = Doctrine::getTable('User')->find($request->getParameter('user_id'));
      if ($unemployed && ($unemployed->employer_id == $user->id)) {
        $unemployed->type = 'realtor';
        $unemployed->employer_id = null;
        $unemployed->company_name = null;
        $unemployed->save();
        $this->redirect('user/employees');
      }
    }
    $this->user = $user;

  }
}
