<?php

class myUser extends sfBasicSecurityUser
{
  public function __get($attr) {
    if ($attr == 'current_region') {
      return $this->getCurrentRegion();
    }
    if ($attr == 'current_search') {
      return $this->getAttribute('current_search');
    }
    if ($attr == 'current_landings') {
      return $this->getCurrentLandings();
    }
    if ($this->isAuthenticated()) {
      return $this->getObject()->$attr;
    }
    return null;
  }

  public function __set($attr, $val) {
    if ($attr == 'current_region') {
      return $this->setCurrentRegion($val);
    }
    if ($attr == 'current_search') {
      return $this->setAttribute('current_search', $val);
    }
    if ($attr == 'current_landings') {
      return $this->setCurrentLandings($val);
    }
    if ($this->isAuthenticated()) {
      return $this->getObject()->$attr = $val;
    }
    return null;
  }


  public function __call($method, $args)
  {
    if ($this->isAuthenticated()) {
      return call_user_func_array(array($this->getObject(), $method), $args);
    }
    return null;
  }


  public function getObject() {
    return $this->getAttribute('object', null);
  }

  public function processLogout() {
    $this->setAuthenticated(false);
    $this->clearCredentials();
    sfContext::getInstance()->getResponse()->setCookie('remember_me', null);
    $this->getAttributeHolder()->remove('object');
  }

  public function processLogin(array $params) {
    $query = Doctrine::getTable('User')->createQuery();
    if (isset($params['key'])) {
      $query
        ->where('remember_key = ?', $params['key'])
        ->addWhere('remember_till > ?', date('Y-m-d'));
    }
    elseif (isset($params['id'])) {
      $query->where('id = ?', $params['id']);
    }
    else {
      $query
        ->where('email = ?', $params['email'])
        ->addWhere('password = ?', md5($params['password']));
    }

    $user = $query->fetchOne();

    if ($user) {
      $user->last_login = date('Y-m-d H:i:s');
      $user->save();
      $this->setAttribute('object', $user);

      $this->clearCredentials();
      if ($user->group_id) {
        $this->addCredentials($user->Group->credentials);
      }

      $this->setAuthenticated(true);

      if ($params['remember']) {
        if (!$this->remember_key) {
          $this->remember_key = Toolkit::generatePassword(64);
        }
        $this->remember_till = date('Y-m-d H:i:s', strtotime('+14 days'));
        sfContext::getInstance()->getResponse()->setCookie('remember_me', $this->remember_key, strtotime($this->remember_till));
        $this->save();
      }

      return true;
    }
    return false;
  }

  public function resetPassword($param) {
    if (isset($param['email'])) {
      $query = Doctrine::getTable('User')
        ->createQuery()
        ->where('email = ?', $param['email']);
      $user = $query->fetchOne();
    }
    else{
      $user = $this->getObject();
    }

    if ($user instanceOf User) {
      $reset = new UserResetKey();
      $reset->user_id = $user;
      $reset->reset_key = Toolkit::generatePassword(32);
      $reset->save();

      $url = sfContext::getInstance()->getRouting()->generate(
        null,
        array(
          'module' => 'user',
          'action' => 'setpassword',
          'key' => $reset->reset_key
        ),
        true
      );

      $mail = DomusMail::create();
      $mail->addTo($user->email)
           ->setSubject('Смена пароля')
           ->setBodyHtml(sprintf(
               '<p>Для того чтобы сменить пароль пройдите по ссылке - <a href="%1$s">%1$s</a></p>',
                $url))
           ->send();


      return true;
    }

    return null;
  }

  public function delete() {
    return $this->getObject()->delete() && $this->processLogout();
  }

  public function getCurrentRegion() {
    return $this->getAttribute('current_region');
  }

  public function setCurrentRegion(Region $region) {
    $this->setAttribute('current_region', $region);
  }

  public function getCurrentLandings() {
    if (!is_array($this->getAttribute('current_landings'))) {
      return array();
    }
    return $this->getAttribute('current_landings');
  }

  public function setCurrentLandings(array $landings) {
    $landings = array_unique($landings);
    if (count($landings) > 15) {
      //вытесняем из начала массива элемент, по мере роста массива
      array_shift($landings);
    }
    $this->setAttribute('current_landings', $landings);
  }

  public function shutdown() {
    if ($user = $this->getObject()) {
      $this->readComments();
      $user->isModified() && $user->save();
    }
    parent::shutdown();
  }

  /**
   * Function mark all comments from post as read
   */
  public function readComments() {
    $comments = $this->getAttribute('read_post_comments', array());
    $comments = array_unique($comments);
    if (count($comments)) {
      foreach ($comments as $post_id => $comment_ids) {
        $comment_ids = array_unique($comment_ids);
        foreach ($comment_ids as $comment_id) {
          $read_post_comment = new ReadPostComment();
          $read_post_comment->fromArray(array(
              'user_id' => $this->id,
              'post_id' => $post_id,
              'post_comment_id' => $comment_id
            ));
          $read_post_comment->save();
          $read_post_comment->free();
          unset($read_post_comment);
        }
      }
    }
    $this->getAttributeHolder()->remove('read_post_comments');
  }

  public function compareAdd($id) {
    if (!$this->compareIsset($id)) {
      $list = $this->compareList();
      $list[] = $id;
      $this->setAttribute('compare', $list);
    }
  }

  public function compareDelete($id) {
    if ($this->compareIsset($id)) {
      $list = $this->compareList();
      unset($list[array_search($id, $list)]);
      $this->setAttribute('compare', $list);
    }
  }

  public function compareList() {
    return $this->getAttribute('compare', array());
  }

  public function compareIsset($id) {
    return in_array($id, $this->compareList());
  }

  public function compareCount() {
    return count($this->getAttribute('compare', array()));
  }
}
