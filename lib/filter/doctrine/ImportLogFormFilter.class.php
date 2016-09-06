<?php

/**
 * ImportLog filter form.
 *
 * @package    filters
 * @subpackage ImportLog *
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 11675 2008-09-19 15:21:38Z fabien $
 */
class ImportLogFormFilter extends BaseImportLogFormFilter
{
  public function configure()
  {
    parent::configure();
    $this->widgetSchema['created_at'] = new sfWidgetFormFilterDate(
      array(
        'from_date'   => new sfWidgetFormDate(),
        'to_date'     => new sfWidgetFormDate(),
        'with_empty'  => false,
        'template'    => 'от %from_date%<br />до %to_date%'
      )
    );
    $this->widgetSchema['file_name']  = new sfWidgetFormFilterInput(
      array('with_empty'  =>  false)
    );
    $this->widgetSchema['user_id'] = new sfWidgetFormInput();
    $this->validatorSchema['user_id'] = new sfValidatorPass();
    $this->widgetSchema['user_id']->setLabel('ID пользователя');
    
    $this->widgetSchema['email']  = new sfWidgetFormInput();
    $this->validatorSchema['email'] = new sfValidatorPass();
    $this->widgetSchema['email']->setLabel('Email');
    
    $this->widgetSchema['user_name']  = new sfWidgetFormInput();
    $this->validatorSchema['user_name'] = new sfValidatorPass();
    $this->widgetSchema['user_name']->setLabel('ФИО пользователя');
    
    $this->widgetSchema->setPositions(array('user_id', 'email', 'user_name', 'created_at', 'file_name', 'file_type', 'lots', 'errors'));    
        
    unset($this['file_type'], $this['lots']);
  }

  public function getFields()
  {
  	$fields = parent::getFields();
  	$fields['email'] = 'email';
  	$fields['user_name'] = 'user_name';
  	return $fields;
  }
  
  protected function addUserNameColumnQuery(Doctrine_Query $query, $field, $value) {
    if (!empty($value)){
      $query->leftJoin('r.User u');
      $query->andWhere('u.name like ?',array('%'. $value . '%'));
      return $query;
    }
  }
  
  protected function addEmailColumnQuery(Doctrine_Query $query, $field, $value) {    
    if (!empty($value)){
      $query->leftJoin('r.User u');
      $query->andWhere('u.email like ?',array('%'. $value . '%'));
      return $query;
    }
  }
  
  protected function addCreatedAtColumnQuery(Doctrine_Query $query, $field, $value) {
    if (!empty($value)) {
      if (!empty($value['from']) && !empty($value['to']) && $value['from'] == $value['to']) {
        $query->andWhere($query->getRootAlias() . '.created_at like ?', $value['to'] . '%');
      }
      else {
        if (!empty($value['from'])) {
          $query->andWhere($query->getRootAlias() . '.created_at >= ?', date('Y-m-d 00:00:00', strtotime($value['from'])));
        }
        if (!empty($value['to'])) {
          $query->andWhere($query->getRootAlias() . '.created_at <= ?', date('Y-m-d 23:59:59', strtotime($value['to'])));
        }
      }
      return $query;
    }
  }
}