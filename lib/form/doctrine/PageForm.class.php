<?php

/**
 * Page form.
 *
 * @package    form
 * @subpackage Page
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class PageForm extends BasePageForm
{
  public function configure()
  {
    $this->widgetSchema['content'] = new sfWidgetFormTextareaTinyMCE(
      array(
        'width' => 800,
        'height' => 350,
        'theme'  => 'advanced',
        'config' => 'extended_valid_elements : "iframe[src|width|height|name|align]", ' . BaseForm::$TinyMCEConfig
      ),
      array('class' => 'tiny_mce_c')
    );
    $this->widgetSchema['url'] = new sfWidgetFormInputText();
    $this->widgetSchema['description'] = new sfWidgetFormTextarea();
    $this->widgetSchema['keywords'] = new sfWidgetFormTextarea();
    

    $query = Doctrine::getTable('Page')->createQuery()->andWhere('parent_id is null');
    if (!$this->isNew()) {
      $query->andWhere('id != ?', $this->getObject()->id);
    }
    
    $this->widgetSchema['parent_id'] = new sfWidgetFormDoctrineChoice(array('model' => 'Page', 'add_empty' => true, 'query' => $query));
    
    $this->validatorSchema['url'] = new sfValidatorString(array('max_length' => 50, 'required' => true));
    $this->validatorSchema['name'] = new sfValidatorString(array('max_length' => 255, 'required' => true));
    $this->validatorSchema['content'] = new sfValidatorString(array( 'required' => false ));

    if ($this->getObject()->url == 'home') {
      unset($this['parent_id'], $this['url']);
    }

    unset($this['position']);
  }

  public function save($con = null) {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (is_null($con))
    {
      $con = $this->getConnection();
    }

    try
    {
      $con->beginTransaction();
      $this->updateObject();

      $page = $this->object;
      $page->url = preg_replace('/[^a-z0-9\-_]+/', '-', $page->url);

      if ($page->title == '')
      {
        $page->title = $page->name;
        if ($page->parent_id)
        {
          $page->title = $page->Page->name . ' - ' . $page->name;
        }
      }
      if ($page->parent_id) {
        $page->url = $page->Page->url . '/' .$page->url;
      }

      $this->object->save($con);
      $con->commit();
    }
    catch (Exception $e)
    {
      $con->rollback();
      throw $e;
    }

    return $this->object;
  }


  protected function updateDefaultsFromObject()
  {
    if (!$this->isNew())
    {
      $url = array_reverse(explode('/', $this->getObject()->url));
      $this->getObject()->url = $url[0];
    }
    parent::updateDefaultsFromObject();
  }

}