<?php

/**
 * form actions.
 *
 * @package    domus
 * @subpackage form
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class formActions extends sfActions
{
  public function executeRegions(sfWebRequest $request)
  {
    $q = Doctrine::getTable('Region')->createQuery()
          ->select('id as value, name as text');

    $data = $q->execute(array(), Doctrine::HYDRATE_ARRAY);
    return $this->renderText(json_encode($data));
  }

  public function executeSpecialities(sfWebRequest $request)
  {
    $data = array();
    foreach (sfConfig::get('app_speciality_types', array()) as $i => $type) {
      $data[] = array('id' => $i, 'text' => $type);
    }
    return $this->renderText(json_encode($data));
  }

  public function executeCurrentregionnodes(sfWebRequest $request)
  {
    $this->nodes = array();
    $this->shosse = array();
    $this->rayon = array();
    
    $sphinx = new DomusSphinxClient(array( 'offset' => 0, 'limit' => 1000 ));
    $sphinx->ResetFilters();
    $sphinx->ResetGroupBy();
    $sphinx->ResetOverrides();
    $sphinx->_search_query = '';
    
    if($this->getRequest()->hasParameter('region_id'))
      $region_id = $this->getRequest()->getParameter('region_id');
    else
      $region_id = empty($this->getUser()->current_search['region_id']) ? 
        intval($this->getUser()->current_region->id) : intval($this->getUser()->current_search['region_id']);

    $sphinx->SetFilter('region_id',array($region_id));
    $sphinx->SetMatchMode(DomusSphinxClient::SPH_MATCH_EXTENDED2);
    
    if (in_array($region_id, array(77,78))) {
      $sphinx->SetSelect('id, name, socr, region_id, CRC32(name) AS ncrc');
      if($region_id == 77)
        $sphinx->SetFilter('ncrc', array(crc32('Москва')), true);
      else
        $sphinx->SetFilter('ncrc', array(crc32('Санкт-Петербург')), true);
      $sphinx->SetSortMode(DomusSphinxClient::SPH_SORT_EXTENDED, 'socr DESC, name ASC');
    } else {
      $sphinx->SetSelect('id, name, socr, region_id');
      $sphinx->SetFilter('list', array( 1 ));
      $sphinx->SetSortMode(DomusSphinxClient::SPH_SORT_EXTENDED, 'socr ASC, name ASC');
    }
    
    $res = $sphinx->Query($sphinx->_search_query, 'regionnodes_main');
    if(!empty($res['total_found'])){
      foreach ($res['matches'] as $v) {
        $node = new Regionnode();
        $node->fromArray($v['attrs']);
        
        if($node->socr == 'ш')
          $this->shosse[] = $node;
        else
          $this->nodes[] = $node;

        if($node->socr == 'р-н' || ($region_id == 78 && $node->socr == 'г')) {
          $this->rayon[] = $node;
        }

      }
    }
    $sphinx->Close();
    
    $this->stage = $request->getParameter('stage');
    $this->region_id = $region_id;
    $this->setLayout(false);
  }

  public function executeRegionnode(sfWebRequest $request)
  {
    $data = array(
      0 => array(),
      1 => array()
    );
    $region_id = $request->getParameter('region_id');

    if ($region_id) {
      $q = Doctrine::getTable('Regionnode')
        ->createQuery()
        ->select('id,name,socr,has_street,has_children')
        ->where('region_id = ? and list = 1', $region_id)
        ->orderBy('socr')
        ->addOrderBy('name');

      foreach($q->fetchArray() as $row) {
        $indx = 0;
        $socr = $row['socr'];
        //Checking by RegExp for blind
        if (77 == $region_id || 78 == $region_id) {
          (preg_match('#^м\.?$#u',$socr)) ? $indx = 1 : $indx = 0;
        }
        else {
          (preg_match('#^ш\.?$#u',$socr)) ? $indx = 1 : $indx = 0;
        }

        $data[$indx][] = array(
          'value'        => $row['id'],
          'text'         => Regionnode::formatName($row['name'], $row['socr']),
          'has_street'   => (int) $row['has_street'],
          'has_children' => (int) $row['has_children']
        );
        
      }

      $data[2] = $data[1]; 
    }

    return $this->renderText(json_encode($data));
  }

  public function executeValues(sfWebRequest $request)
  {
    $formField = Doctrine::getTable('FormField')->find($request->getParameter('id'));
    $this->forward404Unless($formField);
    return $this->renderText(json_encode($formField->getChoices(false)));
  }

  public function executeCity(sfWebRequest $request) {
    $data = array();

    if ($request->hasParameter('q')) {
      $string = preg_replace('/^[^\s.]{1,3}\. /', '', $request->getParameter('q'));

      $q = Doctrine::getTable('Regionnode')
        ->createQuery()
        ->where('name like ?', $string.'%')
        ->andWhere('parent = ?', $request->getParameter('regionnode'))
        ->andWhere('list = ?', false);

      foreach($q->fetchArray() as $row) {
        $data[] = Regionnode::formatName($row['name'], $row['socr']) .
                  ($row['has_street'] ? '|' . $row['id'] : '');
      }
    }

    return $this->renderText(implode("\n", $data));
  }

  public function executeStreet(sfWebRequest $request) {
    $data = array();
    $search_index = 'streets';
    $this->query_string = '';
    if ($request->hasParameter('q')) {
      $options = array(
          'offset'  => 0,
          'limit' => 35
      );
      $sphinx = new DomusSphinxClient($options);
      $sphinx->ResetFilters();
      $sphinx->ResetGroupBy();
      $sphinx->ResetOverrides();
      $sphinx->setMatchMode(DomusSphinxClient::SPH_MATCH_EXTENDED2);

      if($request->hasParameter('frontstreet')) {
        $nodes = explode(',', $request->getParameter('regionnode'));
        foreach ($nodes as $k => $node) {
          $name = Regionnode::unformatName($node);
          $nodes[$k] =  "(@regionnode_name \"{" . 
            $sphinx->EscapeString($name[0]) . "}\" @regionnode_socr \"{" . 
            $sphinx->EscapeString($name[1]) . "}\")";
        }
        
        $this->query_string .= implode(' | ', $nodes);
      }
      elseif ($request->hasParameter('regionnode')) {
        $regionnode = $request->getParameter('regionnode');
        $regionnode = is_array($regionnode) ? $regionnode : array( $regionnode );
        $sphinx->SetFilter('regionnode_id', $regionnode);
      }
      elseif ($request->hasParameter('region')) {
        $region = $request->getParameter('region');
        $lot_type = $request->getParameter('lot_type', array());
        $type = $request->getParameter('type', array());
        
        $region = is_array($region) ? $region : array( intval($region) );
        if(in_array(77, $region)) $region[] = 2295; //Москва special
        if(in_array(78, $region)) $region[] = 2296; //Санкт-Петербург special
        
        $sphinx->setFilter('region_id', $region);
        if(!empty($lot_type)){
          $lot_type = is_array($lot_type) ? $lot_type : array( $lot_type );
          $sphinx->setFilter('lot_type', $lot_type);
        }
        if(!empty($type)){
          $type = is_array($type) ? $type : array( $type );
          $sphinx->setFilter('type', $type);
        }
        $this->query_string .= '@regionnode_socr ("г" | "ш")';
        
        //And towns + shosse
        $search_index .= ' regionnodes_main';
      }

      $sphinx->SetSortMode(DomusSphinxClient::SPH_SORT_RELEVANCE);
      $q = $request->getParameter('q');
      if (!empty($q)) {
        $q = $sphinx->EscapeString($q);
        $this->query_string .= " @name ($q | $q*) - (\"{$sphinx->EscapeString('Москва')}\" | \"{$sphinx->EscapeString('Санкт-Петербург')}\")";
      }
      $sphinx->query($this->query_string, $search_index);

      $pager = new DomusSphinxSearchPager('Street', null, $sphinx);
      $pager->init();
      $data = array_unique( $pager->getResults() );
    }
    
    return $this->renderText(implode("\n", $data));
  }

  public function executeUpload(sfWebRequest $request) {
    $path = sfConfig::get('app_upload_tmp_dir', 'tmp');
    $file = null;
    $validator = new sfValidatorFile(array(
      'mime_categories' => 'web_images',
      'path' => sprintf('%s/%s/source', sfConfig::get('sf_web_dir'), $path)
      ));

    if ($request->isMethod('post')) {
      $uploaded_file = $request->getFiles();
      if (count($uploaded_file) == 1) {
        $uploaded_file = array_shift($uploaded_file);
        $uploaded_file = $validator->clean($uploaded_file);
        if ($uploaded_file instanceOf sfValidatedFile) {
          $uploaded_file->save();
          $file = basename($uploaded_file->getSavedName());
        }
      }
    }
    elseif ($request->hasParameter('name')) {
      $file = $request->getParameter('name');
    }
    elseif ($request->hasParameter('url')) {
      $url = $request->getParameter('url');
      if (strpos($url, 'http://') !== 0) {
        $url = "http://$url";
      }

      $filename = sprintf('%s/%s/%s', sfConfig::get('sf_web_dir'), $path, microtime(true));

      if (copy($url, $filename)) {
        $uploaded_file = $validator->clean(array('tmp_name' => $filename));
        if ($uploaded_file instanceOf sfValidatedFile) {
          $uploaded_file->save();
          $file = basename($uploaded_file->getSavedName());
          @unlink($filename);
        }
      }
    }

    if ($file) {
      $this->file = $file;
      sfCachedThumbnail::setBaseDir(sfConfig::get('sf_web_dir'));
      $this->thumbnail = sfCachedThumbnail::getImage($path, $this->file, 200, 66);
      sfCachedThumbnail::setBaseDir();
    }

    $this->setLayout(false);
  }

  public function executeCrop(sfWebRequest $request)
  {
    $this->type = $request->getParameter('type');
    if ($this->type == 'company') {
        $this->image_name = 'Логотип';
      }
      else {
        $this->image_name = 'Фото';
      }
    $path = sfConfig::get('app_upload_tmp_dir', 'tmp');
    $file = null;
    $validator = new sfValidatorFile(array(
        'mime_categories' => 'web_images',
        'path' => sprintf('%s/%s/source', sfConfig::get('sf_web_dir'), $path),
        'max_size' => 2 * pow(1024, 2)
      ),
      array(
        'max_size' => 'Размер файла не может превышать 2 MB.'
      )
    );
    $form = new ImageAjaxCropForm();
    if ($request->isMethod('POST')){
      $uploaded_file = $request->getFiles();
      if (count($uploaded_file) == 1) {
        $uploaded_file = array_shift($uploaded_file);
        try {
          $uploaded_file = $validator->clean($uploaded_file);
        }
        catch (sfValidatorError $e) {
          $this->error = $e->getMessage();
        }

        if ($uploaded_file instanceOf sfValidatedFile) {
          $uploaded_file->save();
          $file = basename($uploaded_file->getSavedName());
          $image = new Imagick($uploaded_file->getSavedName());
          $d = $image->getImageGeometry();
          if (($d['width'] < 150) && ($d['height'] < 150)) {
            $this->message = 'Изображение слишком мало';
          }
          if ($d['width'] > $d['height']) {
            $image->extentImage($d['width'], $d['width'], 0, (($d['width'] - $d['height']) / 2));//Фикс исключительно для mesto.ru. На локальном сервере нужно с минусом передавать 4й параметр
          } else {
            $image->extentImage($d['height'], $d['height'], (($d['height'] - $d['width']) / 2), 0);
          }
          $image->writeImage($uploaded_file->getSavedName());
        }
      }
    }
    else if ($request->isXmlHttpRequest () && $request->hasParameter('image_file')) {
      $file = $request->getParameter('image_file');
      $crop = array();
      if ($request->hasParameter('coord_x') && $request->hasParameter('coord_y')
          && $request->hasParameter('width') && $request->hasParameter('height')) {
        $coords_x = $request->getParameter('coord_x');
        $coords_y = $request->getParameter('coord_y');
        $width = $request->getParameter('width');
        $height = $request->getParameter('height');
        $crop = array(
          'width' => $width,
          'height' => $height,
          'x' => $coords_x,
          'y' => $coords_y
        );

      }

      if ($file) {
        $this->file = basename($file);
        $this->file = new sfThumbnail();
        $this->file->loadFile(sfConfig::get('sf_web_dir') . '/' . $file, $crop);
        $this->file->save(sfConfig::get('sf_web_dir') . '/' . $file);
      }

      return $this->renderText(json_encode(array('source' => basename($file))));
    }

    if ($file){
      $this->file = sprintf('/%s/source/%s', $path, $file);
    }
    $this->form = $form;
    $this->setLayout('simple');
  }

  public function executeSearch(sfWebRequest $request) {
    $this->config = array();
    if ($request->hasParameter('currency_type')) {
      $currency_type = $request->getParameter('currency_type');
    }
    $config = sfConfig::get('app_search_fields', array());
    if (!empty($config[$request->getParameter('type')]))
    {
      $config = $config[$request->getParameter('type')];
      foreach ($config as $key => $group)
      {
        if (!empty($group['name'])) {
          $this->config[$group['name']] = isset($group['value']) ? $group['value'] : array();
        }
        else {
          if (!empty(Lot::$currency_types[$request->getParameter('type')][$key])) {
            if (!empty($currency_type) && $currency_type == $key) {
                foreach ($group as $field) {
                  $this->config[$field['name']] = isset($field['value']) ? $field['value'] : array();
                }
            }
            else if (empty($currency_type)) {
              if ($key == Lot::$currency_default_type[$request->getParameter('type')]) {
                foreach ($group as $field) {
                  $this->config[$field['name']] = isset($field['value']) ? $field['value'] : array();
                }
              }
            }
          }
        }
      }
    }
  }

  public function executeGmap(sfWebRequest $request) {
    if ($request->hasParameter('region_id')){
      $region = Doctrine::getTable('Region')
        ->createQuery('r')->andWhere('r.id = ?', $request->getParameter('region_id'))
        ->execute();
      if ($region){
        $this->region = $region[0];
      }
    }
    else{
      $this->region = $this->getUser()->current_region;
    }
  }

  public function executeTranslations(sfWebRequest $request)
  {
    $this->translation_table = DomusSearchRoute::$translation_table;
    $this->translit_table = DomusSearchRoute::$translit_table;
    $this->translit_fields = DomusSearchRoute::$translit_fields;
  }

  public function executeCreateblog (sfWebRequest $request)
  {
    $this->forward404Unless($request->isXmlHttpRequest());
    $this->form = new BlogUserForm();

    if ($request->isMethod('post')) {
      $data = $request->getParameter('blog');
      $data['user_id'] = $this->getUser()->id;
      $this->form->bind($data);
      if ($this->form->isValid()) {
        $this->form->save();
        return $this->renderText(json_encode(array('save' => true)));
      }
      else {
        return $this->renderText(json_encode($this->form->getErrorsArray()));
      }
    }
  }

  public function executeDistrict (sfWebRequest $request)
  {
    $this->forward404Unless($request->isXmlHttpRequest());
    $this->forward404Unless($request->hasParameter('district'));

    $district = $request->getParameter('district');
    if (array_key_exists($district, Regionnode::$districts[77])){
      return $this->renderText(json_encode(Regionnode::$districts[77][$district]));
    }
    return $this->renderText(json_encode(array('error' => true)));
  }

  /**
   * Create internal links on posts
   * @param sfWebRequest $request
   */
  public function executeAutolinkator (sfWebRequest $request)
  {
    $this->forward404unless($request->isXmlHttpRequest());
    $this->forward404Unless(
      $request->hasParameter('text')
      && $request->isMethod('post')
      && $request->hasParameter('post_type')
    );

    $text = $request->getParameter('text');
    $this->post_type = $request->getParameter('post_type');
    //Проверяем сколько у нас уже есть внутренних ссылок в тексте
    preg_match_all('/<a\s+href="(.*mesto\.ru.*)"\s+target=.*>.*<\/a>/isU', $text, $internal_links);
    if (count($internal_links[1] < 3)) {
      $links = sfYaml::load(sfConfig::get('sf_config_dir') . '/autolinkator.yml');
      $links_on_page = sfConfig::get('app_autolinks_on_post', 3) - count($internal_links[1]);
      $unique_links = array();
      foreach ($links as $word => $link) {
        if ($links_on_page > 0) {
          if (empty($unique_links[$link])) {
            $replacement = '<a href="' . $link . '" target="_blank">' . $word . '</a>';
            if (mb_stristr($text, $word) && !mb_stristr($text, $link)) {
              $text = preg_replace("#$word#", $replacement, $text, 1);
              $unique_links[$link] = $word;
              $links_on_page--;
            }
          }
        }
        else {
          break;
        }
      }
      if (count($unique_links) > 0) {
        $this->text   = $text;
        $this->links  = $unique_links;
      }
    }
  }

  public function executePortalSearch(sfWebRequest $request)
  {
    $this->query = '';
    if ($request->hasParameter('q-search')) {
      $this->query = trim($request->getParameter('q-search'));
      if ($this->query != '') {
        $options = array(
          'limit'   => sfConfig::get('app_search_max_per_page'),
          'offset'  => ($request->getParameter('page', 1) - 1) * sfConfig::get('app_search_max_per_page')
        );

        $news = new DomusSphinxClient($options);
        $news->searchNewsPortal($this->query);

        $this->news_portal_pager = new DomusSphinxSearchPager('Post', $options['limit'], $news);
        $this->news_portal_pager->setPage($request->getParameter('page', 1));
        $this->news_portal_pager->init();

        $author_articles = new DomusSphinxClient($options);
        $author_articles->searchAuthorArticles($this->query);

        $this->author_articles_pager = new DomusSphinxSearchPager('Post', $options['limit'], $author_articles);
        $this->author_articles_pager->setPage($request->getParameter('page', 1));
        $this->author_articles_pager->init();

        $blogs = new DomusSphinxClient($options);
        $blogs->searchBlogs($this->query);

        $this->blogs_pager = new DomusSphinxSearchPager('BlogPost', $options['limit'], $blogs);
        $this->blogs_pager->setPage($request->getParameter('page', 1));
        $this->blogs_pager->init();

        $news_result = $news->getRes();
        $author_articles_result = $author_articles->getRes();
        $blogs_result = $blogs->getRes();
        $total['news_portal'] = (int) $news_result['total_found'];
        $total['author_articles'] = (int) $author_articles_result['total_found'];
        $total['blogs'] = (int) $blogs_result['total_found'];

        $pager = max($total);
        $pager = array_search($pager, $total) . '_pager';
        $this->pager = $this->$pager;
      }
    }

    $this->setLayout('homepage');
  }

  public function executeConsult(sfWebRequest $request) {
    $this->forward404unless($request->isXmlHttpRequest());
    $this->form = new ConsultForm();
    if ($request->isMethod('post')) {
      $data = $request->getPostParameters();
      $this->form->bind($data);
      if ($this->form->isValid()) {
        $list = sfConfig::get('app_consult_list');
        $list = $data['hasrealtor'] == 'no' ? $list['type_1'] : $list['type_2'];

        $receivers = array();

        foreach($list as $receiver) {
          if(!array_key_exists('is_bcc', $receiver) && !array_key_exists('is_cc', $receiver)) {
            $receivers[] = $receiver;
          }
          else {
            $last_receiver = array_pop($receivers);
            foreach(array_keys($receiver) as $option) {
              if($option != 'email' && $option == true) {
                $key = str_replace('is_', '', $option);
                $last_receiver[$key][] = $receiver['email'];
              }
            }
            $receivers[] = $last_receiver;
          }
        }

        $message = ''
        . "Предполагаемый объект: type" . "\n"
        . "Географическое пололжение: where" . "\n"
        . "Предполагаемая сумма: price" . "\n"
        . "Работаете с риэлтором: hasrealtor" . "\n"
        . "Готовы к сотрудничеству: fearsrealtor" . "\n"
        . "Имя: name"  . "\n"
        . "Телефон: phone" . "\n"
        . "E-mail: email" . "\n"
        . "Комментарий: comment" . "\n";

        $answers = $data;
        $answers['type']         = ConsultForm::$_values['type'][$answers['type']];
        $answers['where']        = ConsultForm::$_values['where'][$answers['where']];
        $answers['price']        = ConsultForm::$_values['price'][$answers['price']];
        $answers['hasrealtor']   = ConsultForm::$_values['bool'][$answers['hasrealtor']];
        $answers['fearsrealtor'] = ConsultForm::$_values['bool'][$answers['fearsrealtor']];

        $message = str_replace(array_keys($answers), $answers, $message);

        sfGearmanProxy::doBackground('pm', array(
          'data'       => array(
            'subject'  => 'Заявка на звонок риэлтора',
            'message'  => nl2br(strip_tags($message)),
            'priority' => 'high',
            'receiver' => $receivers[0]['email'],
          ),
          'receivers'  => $receivers,
          'sender'     => 1,
          'free_send'  => true,
        ));

        return $this->renderText(json_encode(array('save' => true)));
      }
      else {
        return $this->renderText(json_encode($this->form->getErrorsArray()));
      }
    }
  }

  public function executeConsultByPhone(sfWebRequest $request)
  {
    $this->forward404unless($request->isXmlHttpRequest());
    $data = $request->getPostParameters();

    if (empty($data['phone'])) {
      return $this->renderText(json_encode(array('phone' => 'invalid')));
    }

    if (empty($data['lot_type'])) {
      return $this->renderText(json_encode(array('lot_type' => 'invalid')));  
    }

    $email = 'nesterov@mesto.ru';

    switch($data['lot_type']) {
      case 'apartament-sale':
        $email = 'petrovka.miel@gmail.com';
        break;
      case 'apartament-rent':
        $email = 'a@napetrovke.ru';
        break;
      default:
        $email = 'nesterov@mesto.ru';
    }

    sfGearmanProxy::doBackground('pm_send', array(
        'data'  => array(
          'subject'   => '[Mesto.ru] Заказать звонок, заявка с сайта.',
          'message'   => '<h1>Перезвони мне!</h1><b>Телефон:</b> ' . strip_tags($data['phone']),
          'priority'  => 'high',
          'receiver'  => $email,
          'bcc'       => array('obuhov@mesto.ru', 's1l3nt@garin-studio.ru')
        ),
        'free_send' => true,
      )
    );

    return $this->renderText(json_encode(array('send' => 'true')));
  }
}
