<?php

class mailTask extends sfBaseTask
{
  protected $routing = null;

  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      new sfCommandOption('app', 'frontend', sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
    ));

    $this->namespace        = 'mail';
    $this->name             = 'send';
    $this->briefDescription = '';
    $this->detailedDescription = '';
  }

  protected function execute($arguments = array(), $options = array())
  {
    $this->createConfiguration($options['app'], 'dev');

    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

    /*$text = '<p>Добрый день, Вы размещали объявления на федеральном портале недвижимости Место.ру.
                Для того чтобы сделать сайт еще более полезным и удобным, ответьте, пожалуйста,
                на два простых вопроса:</p>
             <ol>
               <li>Что на портале Вам кажется особенно удобным?</li>
               <li>Что на портале Вам мешает им удобно и эффективно пользоваться?</li>
             </ol>
             <p>Заранее благодарим Вас за обратную связь!</p>
             <p>&nbsp;</p>
             <p>Директор по развитию портала Mesto.ru<br />
                тел. +7(495) 979 91 40</p>';*/

    $text = '<p>Уважаемый пользователь!</p>
             <p>Если у Вас есть вопрос, связанный с публикацией объявления,
                Вы можете выслать его на почту <a href="mailto:help@mesto.ru">help@mesto.ru</a> и получить подробный ответ.</p>
             <p>Для того чтобы быть Вам более полезными, нам жизненно необходимо знать Ваше мнение о сайте.
                Если у Вас вдруг освободилось 5 минут, расскажите, пожалуйста, что на сайте кажется вам особенно удобным,
                а что затрудняет эффективное привлечение клиентов с помощью mesto.ru?
                Удается ли Вам размещать все свои объявления на нашем сайте, а если не удается,
                то по какой причине это происходит?</p>
             <p>Вы можете редактировать свои объявления в разделе <a href="http://domania.ru/myadverts/">Мои объявления</a>.</p>
             <p>С уважением,<br />
              Команда mesto.ru<br />
              <a href="http://www.mesto.ru">www.mesto.ru</a></p>';

    $query = Doctrine::getTable('User')
            ->createQuery('u')
            ->select('u.email')
            ->where('u.deleted_at IS NULL');

    foreach ($query->fetchArray() as $user) {
      if ($this->isSent($user['email'])) {
        $this->logSection('already', $user['email']);
        continue;
      }

      DomusMail::create()
          ->clearFrom()
          ->setFrom('obuhovav@mesto.ru')
          ->addTo($user['email'])
          ->setSubject('Администрация портала Место.ру')
          ->setBodyHtml($text)
          ->send();

      $this->logSection('sent', $user['email']);
    }
  }

  private function isSent($email)
  {
    return in_array($email, array());
  }
}
