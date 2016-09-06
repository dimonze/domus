<?php

/**
 * settings actions.
 *
 * @package    domus
 * @subpackage settings
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class settingsActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $app_config = sfYaml::load(sfConfig::get('sf_apps_dir') . '/frontend/config/app.yml');
    $this->settings = array(
      'search_max_per_page' => array(
          'name'  => 'Максимум объвления на страницу поиска',
          'value' => &$app_config['all']['search']['max_per_page']
        ),
      'lot_my_max_per_page' => array(
          'name'  => 'Максимум объвления на страницу "мои объявления"',
          'value' => &$app_config['all']['lot']['my_max_per_page'],
        ),
      'qa_max_days_per_index_page' => array(
          'name'  => 'Максимум дней на странице "Вопросы и ответы"',
          'value' => &$app_config['all']['qa']['max_days_per_index_page'],
        ),
      'qa_max_q_per_theme_page' => array(
          'name'  => 'Максимум вопросов на странице "Тема" - "Вопросы и ответы"',
          'value' => &$app_config['all']['qa']['max_q_per_theme_page'],
        ),
      'qa_max_q_on_sidebar' => array(
          'name'  => 'Максимум вопросов на боковой панели',
          'value' => &$app_config['all']['qa']['max_q_on_sidebar'],
        ),
      'user_promotion' => array(
          'name'  => 'Поощрение/наказание',
          'value' => &$app_config['all']['user']['promotion'],
        ),
      'footer_copyright' => array(
          'type' => 'textarea',
          'name' => 'Блок авторских прав',
          'value' => &$app_config['all']['layout']['copyright']
      ),
      'header_counters' => array(
        'type' => 'textarea',
        'name' => 'Блок для счётчиков',
        'value' => &$app_config['all']['layout']['header_counters']
      ),
      'rambler_top100' => array(
        'type' => 'textarea',
        'name' => 'Код Rambler-Top100',
        'value' => &$app_config['all']['layout']['rambler_top100']
      ),
      'admin_email' => array(
          'name'  => 'Email администратора',
          'value' => &$app_config['all']['admin']['email']
        ),
      'exchange_update' => array(
          'name'  => 'Использовать курс ЦБ РФ',
          'value' => &$app_config['all']['exchange']['update']
        )
    );

    foreach ($app_config['all']['exchange']['rates'] as $currency => $rate)
    {
      if ($currency == 'RUR')
      {
        continue;
      }
      $this->settings['exchange_'.$currency] = array(
          'name'  => 'Курс '.$currency,
          'value' => &$app_config['all']['exchange']['rates'][$currency]
        );
    }

    if ($request->isMethod('post')) {
      $data = $request->getParameter('settings', array());
      foreach ($this->settings as $param => $field)
      {
        if (is_bool($field['value']))
        {
          $field['value'] = isset($data[$param]);
        }
        elseif (is_int($field['value']))
        {
          $field['value'] = (int) $data[$param];
        }
        elseif (is_float($field['value']))
        {
          $field['value'] = (float) $data[$param];
        }
        else
        {
          $field['value'] = $data[$param];
        }
      }

      if ($app_config['all']['exchange']['update'])
      {
        currencyUpdateTask::doFetch($app_config['all']['exchange']['rates']);
      }
      $this->updateLotsCurrency(serialize($app_config['all']['exchange']['rates']));

      file_put_contents(sfConfig::get('sf_apps_dir') . '/frontend/config/app.yml', sfYaml::dump($app_config, 4));
      $this->flushAppConfig();

      $this->getUser()->setFlash('notice', 'Успешно сохранено');
      $this->redirect('settings/index');
    }

  }

  public function executeLotinfoorder(sfWebRequest $request)
  {
    $app_config = sfYaml::load(sfConfig::get('sf_apps_dir') . '/frontend/config/app.yml');
    if ($request->isMethod('post') && array_key_exists($request->getParameter('type'), $app_config['all']['lot']['types'])) {
      $app_config['all']['lot']['info-order'][$request->getParameter('type')] = $request->getParameter('data', array());
      file_put_contents(sfConfig::get('sf_apps_dir') . '/frontend/config/app.yml', sfYaml::dump($app_config, 4));
      $this->flushAppConfig();
      return $this->renderText('Успешно сохранено.');
    }
    else {
      if ($request->hasParameter('type') && array_key_exists($request->getParameter('type'), $app_config['all']['lot']['types']))
      {
        $this->type_names = $app_config['all']['lot']['types'][$request->getParameter('type')];
        $this->groups = $app_config['all']['lot']['info-order'][$request->getParameter('type')];
      }
      else {
        $this->types = $app_config['all']['lot']['types'];
      }
    }
  }

  public function executeSearchreference(sfWebRequest $request)
  {
    $app_config = sfYaml::load(sfConfig::get('sf_apps_dir') . '/frontend/config/app.yml');

    if ($request->isMethod('post') && $request->hasParameter('type'))
    {
      foreach ($request->getParameter('reference', array()) as $key => $value)
      {
        $field = array();
        if (is_array($value)) {
          foreach ($value as $k => $value) {
            $value = preg_split('/\r?\n/', trim($value));
            foreach ($value as $i => $row)
            {
              $value[$i] = (int) preg_replace('/\D+/', '', $row);
              if ($value[$i] <= 0)
              {
                unset($value[$i]);
              }
            }
            $field[$k]['value'] = $value;
            $field[$k]['label'] = $app_config['all']['search']['fields'][$request->getParameter('type')][$key][$k]['label'];
            $field[$k]['name'] = $app_config['all']['search']['fields'][$request->getParameter('type')][$key][$k]['name'];
          }
        }else {
          $value = preg_split('/\r?\n/', trim($value));
          foreach ($value as $i => $row)
          {
            $value[$i] = (int) preg_replace('/\D+/', '', $row);
            if ($value[$i] <= 0)
            {
              unset($value[$i]);
            }
          }
        }

        if (count($field) > 0) {
          $app_config['all']['search']['fields'][$request->getParameter('type')][$key] = $field;
        }
        else {
          $app_config['all']['search']['fields'][$request->getParameter('type')][$key]['value'] = $value;

        }
      }
      file_put_contents(sfConfig::get('sf_apps_dir') . '/frontend/config/app.yml', sfYaml::dump($app_config, 4));
      $this->flushAppConfig();

      $this->getUser()->setFlash('notice', 'Успешно сохранено');
      $this->redirect('settings/search-reference?type='.$request->getParameter('type'));
    }

    $app_config = $app_config['all'];

    if ($request->hasParameter('type') && array_key_exists($request->getParameter('type'), $app_config['lot']['types']))
    {
      $this->type_names = $app_config['lot']['types'][$request->getParameter('type')];
      $this->settings = $app_config['search']['fields'][$request->getParameter('type')];
    }
    else {
      $this->types = $app_config['lot']['types'];
    }
  }

  public function executeStopwords(sfWebRequest $request) {
    $config = sfYaml::load(sfConfig::get('sf_apps_dir') . '/../config/stopwords.yml');
    $themes = sfYaml::load(sfConfig::get('sf_apps_dir') . '/../config/email_themes.yml');
    $this->email_themes = array();
    foreach ($themes as $title => $message) {
      $this->email_themes['titles'][$title] = $title;
      $this->email_themes['body'][$title] = $message['body'];
    }
    $default_email_themes = sfYaml::load(sfConfig::get('sf_apps_dir') . '/../config/default_email_themes.yml');
    $this->default_email_theme = $default_email_themes['all']['restrict_message'];
    if ($request->isMethod('post'))
    {
      $words = preg_split('/\r?\n/', trim($request->getParameter('words', '')));
      $config['all'] = $words;
      file_put_contents(sfConfig::get('sf_apps_dir') . '/../config/stopwords.yml', sfYaml::dump($config));
      if ($request->hasParameter('default_email_theme')){
        $email_theme = trim($request->getParameter('default_email_theme'));
        if ($email_theme != ''){
          $default_email_themes['all']['restrict_message'] = $email_theme;
          file_put_contents(sfConfig::get('sf_apps_dir') . '/../config/default_email_themes.yml', sfYaml::dump($default_email_themes));
        }
      }
      $this->getUser()->setFlash('notice', 'Успешно сохранено');
      $this->redirect('settings/stopwords');
    }

    $this->words = $config['all'];
  }

  public function executeMeta(sfWebRequest $request) {
    $file = sfConfig::get('sf_apps_dir') . '/../config/meta.yml';
    $config = sfYaml::load($file);

    if ($request->isMethod('post') && $request->hasParameter('data'))
    {
      $config['all'] = $request->getParameter('data');
      file_put_contents($file, sfYaml::dump($config));

      $fs = new sfFilesystem();
      $fs->remove(sfFinder::type('file')
         ->name('config_meta.yml.php')
         ->in(sfConfig::get('sf_cache_dir')));

      $this->getUser()->setFlash('notice', 'Успешно сохранено');
      $this->redirect('settings/meta');
    }

    $this->data = $config['all'];
  }

  public function executeDistricts(sfWebRequest $request) {
    $file = sfConfig::get('sf_config_dir') . '/districts.yml';
    $config = sfYaml::load($file);

    if ($request->isMethod('post') && $request->hasParameter('data')) {
      file_put_contents($file, sfYaml::dump($request->getParameter('data')));

      $this->getUser()->setFlash('notice', 'Успешно сохранено');
      $this->redirect('settings/districts');
    }

    $this->names = array(
      'apartament-sale' => 'Квартиры продажа',
      'apartament-rent' => 'Квартиры аренда',
      'house-sale'      => 'Дома/участки продажа',
      'house-rent'      => 'Дома аренда',
      'commercial-sale' => 'Коммерческая продажа',
      'commercial-rent' => 'Коммерческая аренда',
    );
    $this->data = $config;
  }

  public function executeRating (sfWebRequest $request) {
    $file = sfConfig::get('sf_apps_dir') . '/../config/rating.yml';
    $config = sfYaml::load($file);

    if ($request->isMethod('post') && $request->hasParameter('data')) {
      $config = $request->getParameter('data');
      file_put_contents($file, sfYaml::dump($config));

      $this->getUser()->setFlash('notice', 'Успешно сохранено');
      $this->redirect('settings/rating');
    }

    $this->job = Job::get('rating');

    if ($request->getParameter('startjob')) {
      $this->job->run();
      $this->redirect('settings/rating');
    }


    $this->data = $config;
  }

  public function executeSpecialities(sfWebRequest $request)
  {
    $app_config = sfYaml::load(sfConfig::get('sf_apps_dir') . '/frontend/config/app.yml');
    if (!isset($app_config['all']['speciality'])) {
      $app_config['all']['speciality'] = array('types' => array());
    }

    if ($request->isMethod('post')) {
      $types = preg_split('/\r?\n/', trim($request->getParameter('types', '')));
      $app_config['all']['speciality']['types'] = array();
      foreach ($types as $type) {
        if (!empty($type)) {
          $app_config['all']['speciality']['types'][] = $type;
        }
      }

      file_put_contents(sfConfig::get('sf_apps_dir') . '/frontend/config/app.yml', sfYaml::dump($app_config, 4));
      $this->flushAppConfig();

      $this->getUser()->setFlash('notice', 'Успешно сохранено');
      $this->redirect('settings/specialities');
    }

    $this->types = $app_config['all']['speciality']['types'];
  }



  protected function flushAppConfig($name = 'config_app.yml.php')
  {
    $fs = new sfFilesystem();
    $fs->remove(sfFinder::type('file')->name($name)->in(sfConfig::get('sf_cache_dir')));
  }

  public function executeEmailthemes(sfWebRequest $request)
  {
    $file = sfConfig::get('sf_config_dir') . '/email_themes.yml';
    $config = sfYaml::load($file);
    $this->data = $config;
    if ($request->isMethod('post') && $request->hasParameter('data')) {
      file_put_contents($file, sfYaml::dump($request->getParameter('data')));

      $this->getUser()->setFlash('notice', 'Успешно сохранено');
      $this->redirect('settings/emailthemes');
    }
  }
  public function executeEmailthemedelete (sfWebRequest $request)
  {
    $title = $request->getParameter('title');
    $this->forward404If(!$title);
    if ($title != 'Новое сообщение на сайте mesto.ru') {
      $file = sfConfig::get('sf_config_dir') . '/email_themes.yml';
      $config = sfYaml::load($file);
      if (!$config[$title]){
        $this->getUser()->setFlash('error', $title . ' - тема не найдена.');
        $this->redirect('settings/emailthemes');
      }
      unset($config[$title]);
      file_put_contents($file, sfYaml::dump($config));
      $this->getUser()->setFlash('notice', 'Тема сообщения удалена.');
      $this->redirect('settings/emailthemes');
    }
    else {
      $this->getUser()->setFlash('error', 'Тема не удалена. Тема используется для рассылки.');
      $this->redirect('settings/emailthemes');
    }

  }
  public function executeEmailthemesnew (sfWebRequest $request)
  {
    $file = sfConfig::get('sf_config_dir') . '/email_themes.yml';
    $config = sfYaml::load($file);
    $this->data = $config;
    if ($request->isMethod('post') && $request->hasParameter('data')) {
      $data = $request->getParameter('data');
      $title = $data['title'];
      $body = $data['body'];
      $data = array("$title" => array('body' => "$body"));
      $this->data = array_merge($this->data, $data);

      file_put_contents($file, sfYaml::dump($this->data));

      $this->getUser()->setFlash('notice', 'Успешно сохранено');
      $this->redirect('settings/emailthemes');
    }
  }

  public function executeAside (sfWebRequest $request)
  {
    $file = sfConfig::get('sf_config_dir') . '/aside.yml';
    $config = sfYaml::load($file);

    if ($request->isMethod('post') && $request->hasParameter('data'))
    {
      $data = $request->getParameter('data');

      foreach ($config['all'] as $modul_name => $modul):
        foreach($modul as $action_name => $action):
          foreach($action as $aside_name => $aside):
            $config['all'][$modul_name][$action_name][$aside_name] = isset($data[$modul_name][$action_name][$aside_name]);
          endforeach;
        endforeach;
      endforeach;

      file_put_contents($file, sfYaml::dump($config));

      $fs = new sfFilesystem();
      $fs->remove(sfFinder::type('file')
         ->name('config_aside.yml.php')
         ->in(sfConfig::get('sf_cache_dir')));

      $this->getUser()->setFlash('notice', 'Успешно сохранено');
      $this->redirect('settings/aside');
    }
    $this->modul_label = array(
      'news' => 'Новости',
      'author_article' => 'Авторские статьи',
      'blogs' => 'Блоги',
      'expert_article' => 'Экспертные статьи',
      'qa' => 'Вопросы и Ответы',
      'questionnaire' => 'Опросы',
      'page' => 'Статические страницы',
      'posts' => 'Посты'
    );
    $this->action_label = array(
      'index' => 'Все записи',
      'show' => 'Одна запись',
      'theme' => 'Записи по тематикам',
      'add' => 'Добавление',
      'showauthor' => 'Страничка Автора',
      'showpost' => 'Страничка Поста в блоге ',
      'homepage' => 'Главная страница'
    );
    $this->aside_label = array(
      'events' => 'События',
      'analytics' => 'Аналитика',
      'article' => 'Статьи',
      'news' => 'Новости',
      'experts' => 'Экспертные статьи',
      'authors' => 'Авторские статьи',
      'qa' => 'Вопросы и ответы',
      'questionnaire' => 'Опросы',
      'blogs' => 'Блоги',
      'events_banner' => 'Реклама событий',
    );

    $this->data = $config['all'];
  }

  public function executeYaexport (sfWebRequest $request)
  {
    $file = sfConfig::get('sf_config_dir') . '/yandex.realty.export.yml';
    $config = sfYaml::load($file);

    if ($request->isMethod('post') && $request->hasParameter('config')) {
      $data = $request->getParameter('config');
      foreach($config['all']['users']['types'] as $user_type => $user) {
        $config['all']['users']['types'][$user_type]['value'] = isset($data['users']['types'][$user_type]['value']);
      }

      foreach($config['all']['users']['sources'] as $source_id => $source) {
        $config['all']['users']['sources'][$source_id]['value'] = isset($data['users']['sources'][$source_id]['value']);
      }

      foreach($config['all']['users']['partners'] as $group_id => $value) {
        $config['all']['users']['partners'][$group_id]['value'] = isset($data['users']['partners'][$group_id]['value']);
      }

      foreach($config['all']['regions'] as $region_id => $region) {
        $config['all']['regions'][$region_id]['value'] = isset($data['regions'][$region_id]['value']);
        $config['all']['regions'][$region_id]['limit'] = (int) $data['regions'][$region_id]['limit'];
      }

      foreach($config['all']['lot_type'] as $type => $val) {
        $config['all']['lot_type'][$type]['value'] = isset($data['lot_type'][$type]['value']);
      }


      file_put_contents($file, sfYaml::dump($config));

      $fs = new sfFilesystem();
      $fs->remove(sfFinder::type('file')
         ->name('config_yandex.realty.export.yml.php')
         ->in(sfConfig::get('sf_cache_dir')));

      $this->getUser()->setFlash('notice', 'Успешно сохранено');
      $this->redirect('settings/yaexport');
    }
    else {
      $regions = Doctrine::getTable('region')->findAll(Doctrine::HYDRATE_ARRAY);
      foreach($regions as $region) {
        if(!isset($config['all']['regions'][$region['id']])) {
          $config['all']['regions'][$region['id']]['name'] = $region['name'];
          $config['all']['regions'][$region['id']]['value'] = false;
          $config['all']['regions'][$region['id']]['limit'] = 0;
        }
      }

      $lot_types = Lot::$type_ru;
      foreach($lot_types as $type => $type_name) {
        if(!isset($config['all']['lot_type'][$type])) {
          $config['all']['lot_type'][$type]['name'] = $type_name;
          $config['all']['lot_type'][$type]['value'] = false;
        }
      }
      file_put_contents($file, sfYaml::dump($config));
    }

    $this->config = $config['all'];
  }


  protected function updateLotsCurrency ($config = null)
  {
    if (null != $config) {
      sfGearmanProxy::doBackground('update_lots_currency', $config);
    }
  }
}
