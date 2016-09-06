<?php

class spamTask extends sfBaseTask
{
  protected
    $_emails = null,
    $_sent   = array(),
    $_file   = null;

  const
    ERROR_NOEMAILS = 1;

  protected function configure()
  {
    $this->addArgument('file', sfCommandArgument::REQUIRED);
    $this->addOptions(array(
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      new sfCommandOption('app', 'frontend', sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
    ));

    $this->namespace        = 'domus';
    $this->name             = 'spam';
    $this->briefDescription = '';
    $this->detailedDescription = '';
  }

  protected function execute($arguments = array(), $options = array())
  {
    $this->createConfiguration($options['app'], 'dev');
    $databaseManager = new sfDatabaseManager($this->configuration);

    $this->_file = $arguments['file'];

    $mail_template = $this->getMail(true);

    while ($email = $this->getNextEmail()) {
      $mail = clone $mail_template;

      if (is_array($email)) {
        $mail->addTo($email[0], $email[1]);
      }
      else {
        $mail->addTo($email);
      }

      $this->sendMail($mail, is_array($email) ? $email[0] : $email);
    }

    $this->logSection('done', count($this->_sent) . ' mails');
  }

  private function sendMail(Zend_Mail $mail, $email)
  {
    $mail->send();
    $this->logSection('sent', $email);

    $this->_sent[] = $email;
    $file = (0 === strpos($this->_file, '/') ? '' : sfConfig::get('sf_root_dir') . '/') . $this->_file;
    file_put_contents(dirname($file) . '/sent.txt', $email . "\n", FILE_APPEND);

    $lines = file($file);
    array_shift($lines);
    file_put_contents($file, implode('', $lines));
    usleep(rand(5000, 25000));
  }

  private function getNextEmail()
  {
    if (null === $this->_emails) {
      $this->loadEmails();
    }

    if (!count($this->_emails)) {
      return false;
    }

    if (count($this->_sent) == 200) {
      return false;
    }

    return array_shift($this->_emails);
  }
  
  private function loadEmails()
  {
    $file = (0 === strpos($this->_file, '/') ? '' : sfConfig::get('sf_root_dir') . '/') . $this->_file;
    $this->_emails = array();

    if (!function_exists('aw_trim')) {
      function aw_trim(&$value) {
        $value = trim($value);
      }
    }
    
    $text = '';
    foreach (file($file) as $line) {
      $email = null;
      $line = explode(',', $line);
      array_walk($line, 'aw_trim');
      
      if (count($line) > 2) {
        $email = array_pop($line);
      }
      elseif (!empty($line[0]) && !empty($line[1])) {
        $email = array($line[1], $line[0]);
      }
      elseif (!empty($line[1])) {
        $email = $line[1];
      }

      if (!empty($email)) {
        if (is_array($email)) {
          $key = is_array($email) ? $email[0] : $email;
          $text .= sprintf("%s,%s\n", $email[1], $email[0]);
        }
        else {
          $key = $email;
          $text .= sprintf(",%s\n", $email);
        }
        
        $this->_emails[$key] = $email;
      }
    }

    // cleaning and fixing source - excluding whitespaces and etc
    file_put_contents($file, $text);
  }

  /**
   * @return Zend_Mail
   */
  private function getMail($attach_images = false)
  {
    $body = <<<EOB
<div style="width: 800px;">
  <img src="/images/mestorutop.png" alt="Место.ру"/>
  <h2 style="color: #000; font: normal 20px/1.6 Arial, Helvetica, sans-serif; margin: 10px 70px 30px 20px;">
    Разместите все свои объявления на федеральном портале недвижимости абсолютно бесплатно.
    Вы можете сделать это
    <a href="http://mesto.ru/user/register?forward=/lot/add" style="color: #06c;">прямо сейчас</a>.
  </h2>
  <p style="font: normal 14px/1.6 Arial, Helvetica, sans-serif; color: #000; margin: 0 70px 15px 20px;">
    <a href="http://www.mesto.ru/">www.mesto.ru</a>  – это место, где находятся Ваши клиенты!
  </p>
  <p style="font: normal 14px/1.6 Arial, Helvetica, sans-serif; color: #000; margin: 0 70px 15px 20px;">
    Подать объявление «на раз-два-три» может каждый.
  </p>
  <p style="font: normal 14px/1.6 Arial, Helvetica, sans-serif; color: #000; margin: 0 70px 15px 20px;">
    <strong>Раз.</strong> Нажмите ссылку «<a href="http://mesto.ru/user/register?forward=/lot/add">Подать объявление</a>».
    <strong>Два.</strong> Заполните простую форму регистрации.
    <strong>Три.</strong> Добавьте объявление. От того насколько информативно Вы заполните поля объявления,
    будет зависеть рейтинг объявления на портале.
    Чем больше информации в объявлении - тем больше клиентов!
  </p>
  <p style="text-align: center; margin: 0 0 35px;">
    <a style="font-size: 24px; font-weight: bold;" href="http://mesto.ru/user/register?forward=/lot/add">
      <img style="width: 581px; height: 94px; border: 0;font-size: 24px; font-weight: bold;" src="/images/mailbutton.png" alt="Подать объявление прямо сейчас - получить клиентов!" title="Подать объявление прямо сейчас - получить клиентов!" />
    </a>
  </p>
  <p style="text-align: center; margin: 0 0 20px;">
    <a href="http://www.mesto.ru/about/why" style="color: #06c; font: normal 12px Arial, Helvetica, sans-serif">
      Почему стоит обратить внимание на Место.ру?
    </a>
  </p>
  <p style="text-align: center; margin: 0 0 20px;">
    Свои пожелания и вопросы Вы можете присылать на
    <a href="mailto:obuhovav@mesto.ru" style="color: #06c; font: normal 12px Arial, Helvetica, sans-serif">obuhovav@mesto.ru</a>
  </p>
</div>
EOB;

    $mail = DomusMail::create()->setSubject('Бесплатные объявления на портале недвижимости Место.ру');

    if ($attach_images) {
      $this->attachImages($mail, $body);
    }
    else {
      $this->linkImages($mail, $body);
    }

    return $mail;
  }

  private function attachImages(Zend_Mail $mail, $body)
  {
    $mail->setType(Zend_Mime::MULTIPART_RELATED);

    if (preg_match_all('/<img.+src="\/images\/([^"]+)"/iU', $body, $matches)) {
      foreach ($matches[1] as $name) {
        $filename = sfConfig::get('sf_web_dir') . '/images/' . $name;
        if (is_readable($filename)) {
          $at = $mail->createAttachment(file_get_contents($filename));
          $at->type = $this->mimeByExtension($filename);
          $at->disposition = Zend_Mime::DISPOSITION_INLINE;
          $at->encoding = Zend_Mime::ENCODING_BASE64;
          $at->id = 'cid_' . md5_file($filename);

          $body = str_replace('/images/' . $name, 'cid:' . $at->id, $body);
        }
      }
    }

    $mail->setBodyHtml($body, 'UTF-8', Zend_Mime::ENCODING_8BIT);
  }

  private function linkImages(Zend_Mail $mail, $body)
  {
    $body = str_replace('"/images/', '"http://mesto.ru/images/', $body);
    $mail->setBodyHtml($body);
  }


  public function mimeByExtension($filename)
  {
    if (is_readable($filename)) {
      switch (pathinfo($filename, PATHINFO_EXTENSION)) {
      case 'gif':
        return 'image/gif';

      case 'jpg':
      case 'jpeg':
        return 'image/jpg';

      case 'png':
        return 'image/png';

      default:
        return 'application/octet-stream';
      }
    }
  }
}
