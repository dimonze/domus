<?php

/**
 * User filter form.
 *
 * @package    filters
 * @subpackage User *
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 11675 2008-09-19 15:21:38Z fabien $
 */
class UserFormFilter extends BaseUserFormFilter
{
  public function configure()
  {
    $this->widgetSchema['deleted'] = new sfWidgetFormChoice(array('choices' => array(0 => 'нет', 1 => 'да')));
    $this->widgetSchema['per-page'] = new sfWidgetFormSelect(array(
      'choices' => array(
        10 => 10, 20 => 20, 50 => 50, 100 => 100, 200 => 200,
        500 => 500, 1000 => 1000, 5000 => 5000
      ),
      'default' => 10
    ));
    $this->widgetSchema['regions'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'RegionTable',  'add_empty' => true,
      'query' => Doctrine::getTable('Region')->createQuery()
        ->select('id, name'),
    ));
    $this->widgetSchema['type'] = new sfWidgetFormChoice(array('choices' => array('' => '') + User::$types));
    $this->widgetSchema['employer_id'] = new sfWidgetFormInput();

    $this->validatorSchema['per-page'] = new sfValidatorPass();
    $this->validatorSchema['regions'] = new sfValidatorPass();
    $this->validatorSchema['deleted'] = new sfValidatorPass();
  }
  
  public function getFields() {
    $fields = parent::getFields();
    $fields['regions'] = 'regions';
    
    return $fields;
  }
    
  protected function addRegionsColumnQuery(Doctrine_Query $query, $field, $value) {
    if (!empty($value)){
      $query->leftJoin('r.Lot l');
      $query->andWhere('l.region_id = ?',array($value));
      return $query;
    }
  }
}