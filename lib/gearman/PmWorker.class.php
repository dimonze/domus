<?php

class PmWorker extends sfGearmanWorker
{
  public
    $name = 'pm',
    $methods = array('pm', 'pm_send');

  public function doPm(GearmanJob $job)
  {
    $this->startJob();
    $settings = unserialize($job->workload());
    $receivers = $settings['receivers'];


    if ('<All>' == $receivers[0]) {
      $receivers = Doctrine::getTable('User')->createQuery()
        ->select('email')
        ->execute(array(), Doctrine::HYDRATE_ARRAY);
    }

    if ('<QA_Subscribers>' == $receivers[0]) {
      $receivers = Doctrine::getTable('User')->createQuery('u')
        ->select('u.name, u.email')
        ->leftJoin('u.Settings s with s.name = "qa_notify"')
        ->andWhere('u.type IN ("company", "employee")')
        ->andWhere('(s.value is not null or s.name is null)')
        ->execute(array(), Doctrine::HYDRATE_ARRAY);
      $message = $settings['data']['message'];
    }

    foreach ($receivers as $key => $receiver) {

      if(!empty($receiver['name'])){
        $settings['data']['message'] = str_replace(
          '{имя фамилия}',
          $receiver['name'],
          $message
        );
      }

      $data = array_merge(
        $settings['data'],
        array(
          'receiver'  => $receiver['email'],
          'bcc'       => !empty($receiver['bcc']) ? $receiver['bcc'] : array(),
          'cc'        => !empty($receiver['cc']) ? $receiver['cc'] : array(),
        )
      );

      if (count($data['bcc']) == 0) {
        unset($data['bcc']);
      }

      if (count($data['cc']) == 0) {
        unset($data['cc']);
      }

      $this->getClient()->queue('pm_send', array(
        'data'       => $data,
        'sender'     => $settings['sender'],
        'send_pm'    => $settings['send_pm'],
        'send_email' => $settings['send_email'],
        'free_send'  => $settings['free_send'],
      ));
    }

    $this->completeJob($job);
  }

  public function doPmSend(GearmanJob $job)
  {
    $this->startJob();
    $settings = unserialize($job->workload());

    if (isset($settings['data']) && !isset($settings['free_send'])) {
      // hack for admin form && import tasks
      unset($settings['data']['bcc'], $settings['data']['cc']);
      $form = new PMAdminForm();
      $form->bind($settings['data']);
      $pm = $form->getObject(isset($settings['sender']) ? $settings['sender'] : false);
    }
    elseif (isset($settings['pm_id'])) {
      $pm = Doctrine::getTable('PM')->find($settings['pm_id']);
    }


    if (!empty($settings['send_pm']) && isset($form) && $form->isValid()) {
      $pm->save();
    }
    if (!empty($settings['send_email'])) {
      // Send fulle message if form exists and no save asked
      $notify_only = !(isset($form) && empty($settings['send_pm']));
      $pm->email($notify_only);
    }
    if(!empty($settings['free_send'])) {
      // Send message to anybody. Make spam, not war!
      $mail = DomusMail::create()
        ->addTo($settings['data']['receiver'])
        ->setSubject($settings['data']['subject'])
        ->setBodyHtml($settings['data']['message']);

      foreach($settings['data']['bcc'] as $email) {
        $mail->addBcc($email);
      }
      foreach($settings['data']['cc'] as $email) {
        $mail->addCc($email);
      }

      $mail->send();
    }


    $this->completeJob($job);
  }
}