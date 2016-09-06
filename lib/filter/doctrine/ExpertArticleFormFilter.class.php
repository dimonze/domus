<?php

/**
 * ExpertArticle filter form.
 *
 * @package    filters
 * @subpackage ExpertArticle *
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 11675 2008-09-19 15:21:38Z fabien $
 */
class ExpertArticleFormFilter extends BaseExpertArticleFormFilter
{
  public function configure()
  {        
    $this->setWidgets(array(
      'created_at' => new sfWidgetFormFilterDate(array(
        'from_date' => new sfWidgetFormDate(),
        'to_date' => new sfWidgetFormDate(),
        'with_empty' => false
      )),
      'status' => new sfWidgetFormChoice(array(
        'choices' => array(
          '' => '',
          'publish' => 'Опубликована',
          'not_publish' => 'Неопубликована'
        )
      )),
    ));

    $this->widgetSchema->setLabels(array(
      'created_at'  => 'Дата создания',
      'status'      => 'Статус'
    ));
  }

}