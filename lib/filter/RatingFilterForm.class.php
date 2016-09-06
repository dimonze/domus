<?php

/**
 * RatingFilterForm
 *
 * @package    domus
 * @subpackage filters
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class RatingFilterForm extends sfForm
{
  private $_type = null;

  public function __construct($type, $defaults = array(), $options = array(), $CSRFSecret = null)
  {
    $this->_type = $type;
    parent::__construct($defaults, $options, $CSRFSecret);
  }

  public function configure()
  {
    $types = array('' => 'Специализация');
    foreach (sfConfig::get('app_speciality_types', array()) as $i => $type) {
      $types[$type] = $type;
    }

    $sort_options = array(
      'rating-desc' => 'рейтингу ↓',
      'rating-asc'  => 'рейтингу ↑',
    );

    if ($this->_type == 'realtor') {
      $sort_options['name-asc']  = 'имени ↑';
      $sort_options['name-desc'] = 'имени ↓';
    }
    elseif ($this->_type == 'company') {
      $sort_options['companyname-asc']  = 'названию ↑';
      $sort_options['companyname-desc'] = 'названию ↓';
    }

    $this->setWidgets(array(
      'speciality' => new sfWidgetFormSelect(array('choices' => $types)),
      'region_id'  => new sfWidgetFormDoctrineChoice(array(
          'model'     => 'Region',
          'add_empty' => 'Выберите регион',
        )),
      'sort'       => new sfWidgetFormSelect(array(
          'choices'   => $sort_options,
          'default'   => 'rating-desc',
        )),
    ));

    $this->setValidators(array(
      'speciality' => new sfValidatorChoice(array(
          'required' => false,
          'choices'  => array_keys($types)
        )),
      'region_id'  => new sfValidatorDoctrineChoice(array(
          'required' => false,
          'model'    => 'Region'
        )),
      'sort'       => new sfValidatorChoice(array(
          'required' => false,
          'choices'  => array_keys($sort_options)
        )),
    ));

    $this->widgetSchema->setNameFormat('filter[%s]');
  }

  public function getQuery()
  {
    if (!$this->isValid()) {
      return null;
    }

    $query =  Doctrine::getTable('User')->createQuery('u')
      ->select('u.*, i.*')
      ->leftJoin('u.Regions r')
      ->leftJoin('u.Info i')
      ->leftJoin('u.Settings s with s.name = ? and s.value is null', 'show_rating');

    if ($this->_type == 'company'){
      $query->andWhere('u.type = ?', 'company');
    }
    else {
      $query->andWhereIn('u.type', array('employee', 'realtor'));
    }

    if ($region_id = $this->getValue('region_id')) {
      $query->andWhere('r.region_id = ?', $region_id);
    }

    $query->andWhere('u.photo IS NOT NULL');
    if ($speciality = $this->getValue('speciality')) {
      $query->andWhere(
        'i.specialities like ? or i.specialities like ?',
        array("%$speciality,%", "%$speciality")
      );
    }
    $query->andWhere('u.inactive IS NULL OR u.inactive = ?', 0)
          ->andWhere('u.group_id != ? OR u.group_id IS NULL', UserGroup::PARTNERS_ID)
          ->groupBy('u.id')
          ->having('count(s.user_id) = ?', 0);

    $sort = explode('-', $this->getValue('sort') ? $this->getValue('sort') : 'rating-desc');
    $sort_order = $sort[1];
    switch ($sort[0]) {
      case 'rating':
        $query->orderBy('u.rating ' . $sort_order);
        break;
      case 'name':
        $query->orderBy('u.name ' . $sort_order);
        break;
      case 'companyname':
        $order_by = 'u.company_name ' . $sort_order;
        break;
      default:
        $query->orderBy('u.rating desc');
        break;
    }

    return $query;
  }
}