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
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $app_config = sfYaml::load(sfConfig::get('sf_app_dir') . '/../frontend/config/app.yml');
    $app_config = $app_config['all'];
    
    if ($request->hasParameter('type') && array_key_exists($request->getParameter('type'), $app_config['lot']['types'])) {
      $this->type_names = $app_config['lot']['types'][$request->getParameter('type')];

      $this->widgets = array();
      $q = Doctrine::getTable('FormItem')
        ->createQuery()
        ->where('type = ?', $request->getParameter('type'))
        ->orderBy('position');

      foreach ($q->fetchArray() as $row) {
        $this->widgets[] = array(
          'id'                      => $row['field_id'],
          'required'                => $row['required'],
          'show_additional_header'  => $row['show_additional_header'],
        );
      }
    }
    else {
      $this->types = $app_config['lot']['types'];
    }
  }

  public function executeSave(sfWebRequest $request) {
    $this->forward404Unless($request->hasParameter('type') && $request->hasParameter('data'));
    
    $q = Doctrine::getTable('FormItem')
      ->createQuery()
      ->where('type = ?', $request->getParameter('type'))
      ->delete()->execute();
    
    foreach ($request->getParameter('data') as $row) {
      $form_row = new FormItem();
      $form_row->fromArray(array(
          'type'                   => $request->getParameter('type'),
          'field_id'               => (int)  $row['id'],
          'required'               => (bool) $row['required'],
          'position'               => (int)  $row['position'],
          'show_additional_header' => (bool) $row['show_additional_header'],
        ));
      $form_row->save();
    }

    return $this->renderText('Успешно сохранено');
  }

  public function executeWidgetnew(sfWebRequest $request) {
  }

  public function executeWidgetedit(sfWebRequest $request) {
    $this->widget = Doctrine::getTable('FormField')->find($request->getParameter('id'));
    $this->forward404Unless($this->widget);
  }

  public function executeWidgetsave(sfWebRequest $request) {
    $this->forward404Unless($request->isMethod('post'));

    if ($errors = $this->validateWidgetData($request)) {
      return $this->renderText($errors);
    }
    else {
      $data = array(
        'type' => $request->getParameter('type'),
        'value' => trim($request->getParameter('value')),
        'label' => trim($request->getParameter('label')),
        'comment' => trim($request->getParameter('comment')),
        'help' => $request->getParameter('help'),
        'rating' => $request->getParameter('rating'),
        'xml_name' => $request->getParameter('xml_name'),
        'xml_desc' => $request->getParameter('xml_desc'),
      );

      if ($request->hasParameter('id')) {
        $widget = Doctrine::getTable('FormField')->find($request->getParameter('id'));
        $this->forward404Unless($widget);
        unset($data['type']);
      }
      else {
        $widget = new FormField();
      }

      $widget->fromArray($data);
      $widget->save();

      return $this->renderText(json_encode(array('valid' => true, 'callback' => array('name' => 'widget_add', 'param' => $widget->toArray()), 'close' => true)));
    }
  }

  public function executeWidgetdelete(sfWebRequest $request) {
    $widget = Doctrine::getTable('FormField')->find($request->getParameter('id'));
    $this->forward404Unless($widget && $widget->id > 55);
    $widget->delete();
    return $this->renderText('ok');
  }

  private function validateWidgetData(sfWebRequest $request) {
    $errors = array();

    $fields = array('type', 'label');
    if (in_array($request->getParameter('type'), array('select', 'multiple', 'radio', 'radiocombo'))) {
      $fields[] = 'value';
    }

    if ($request->hasParameter('id')) {
      unset($fields[0]);
    }

    foreach ($fields as $type) {
      if (trim($request->getParameter($type)) == '') {
        $errors[$type] = 'Обязательное поле';
      }
    }

    if ($request->getParameter('rating') < 0) {
      $errors['rating'] = 'Рейтинг должен быть больше нуля';
    }

    if (count($errors)) {
      return json_encode(array('errors' => array('field' => $errors)));
    }
    else {
      return null;
    }
  }

}
