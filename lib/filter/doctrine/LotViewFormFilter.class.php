<?php

/**
 * LotView filter form.
 *
 * @package    filters
 * @subpackage LotView *
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 11675 2008-09-19 15:21:38Z fabien $
 */
class LotViewFormFilter extends BaseLotViewFormFilter
{
  public function configure()
  {
    $this->widgetSchema['lot_id'] = new sfWidgetFormInput();
    $this->validatorSchema['lot_id'] = new sfValidatorPass(array('required' => false));

    $this->widgetSchema['lot_type'] = new sfWidgetFormChoice(array('choices' => array_merge(array('' => ''), Lot::$type_ru)));
    $this->validatorSchema['lot_type'] = new sfValidatorChoiceKeys(array('required' => false, 'choices' => Lot::$type_ru));

    $this->widgetSchema['region_name'] = new sfWidgetFormDoctrineChoice(array('model' => 'Region', 'add_empty' => true));
    $this->validatorSchema['region_name'] = new sfValidatorPass(array('required' => false));
  }

  public function getFields()
  {
    $fields = parent::getFields();
    $fields['region_name'] = 'region_name';
    return $fields;
  }

  public function addRegionNameColumnQuery(Doctrine_Query $query, $field, $value)
  {
    $ra = $query->getRootAlias();
    $query->leftJoin($ra . '.Lot l')
          ->leftJoin('l.Region re')
          ->andWhere('re.id = ?', $value);
    return $query;
  }
}