<?php

class PM extends BasePM
{
  const
    DELETED_SENDER = 1,
    DELETED_RECEIVER = 2;

  public function sendCommentAsPm($comment)
  {
    if (sfContext::getInstance()->getUser()->id == $this->receiver) {
      return false;
    }

    $this->sender = $comment->user_id ? $comment->user_id : null;
    $this->receiver = $comment->Post->User->id;
    $this->subject = 'Коментарий к "' . $comment->Post->title . '"';
    $this->message = $comment->body;
    $this->user_name = $comment->user_name;
    $this->user_email = $comment->user_email;
    $this->save();

    $moder_ids = Doctrine::getTable('User')->getModersIds();

    foreach ($moder_ids as $id) {
      $copy = $this->copy();
      $copy->receiver = $id['id'];
      $copy->save();
      $copy->free();
    }
  }

  public function email($notify_only = true)
  {
    if (!$this->UserReceiver->getSettingsValue('send_email')) {
      return false;
    }

    if ($notify_only) {
      $subject = 'Новое сообщение на сайте mesto.ru';

      $file = sfConfig::get('sf_config_dir') . '/email_themes.yml';
      $config = sfYaml::load($file);
      $body = str_replace('{имя фамилия}', $this->UserReceiver->name, $config[$subject]['body']);
    }
    else {
      $subject = $this->subject;
      $body = $this->message;
    }

    DomusMail::create()
      ->addTo($this->UserReceiver->email)
      ->setSubject($subject)
      ->setBodyHtml($body)
      ->send();
  }

  public function markAsDeleted($type)
  {
    if ('received' == $type) {
      $this->is_deleted = $this->is_deleted | self::DELETED_RECEIVER;
    }
    elseif ('sent' == $type) {
      $this->is_deleted = $this->is_deleted | self::DELETED_SENDER;
    }

    $this->save();
  }
}