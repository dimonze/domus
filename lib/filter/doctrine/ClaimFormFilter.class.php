<?php

/**
 * Claim filter form.
 *
 * @package    filters
 * @subpackage Claim *
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 11675 2008-09-19 15:21:38Z fabien $
 */
class ClaimFormFilter extends BaseClaimFormFilter
{
  public function configure()
  {
    $this->widgetSchema['status'] = new sfWidgetFormChoice(array('choices' => Claim::$status));
  }
}