<?php

require_once dirname(__FILE__).'/../lib/lot_viewsGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/lot_viewsGeneratorHelper.class.php';

/**
 * lot_views actions.
 *
 * @package    domus
 * @subpackage lot_views
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12474 2008-10-31 10:41:27Z fabien $
 */
class lot_viewsActions extends autoLot_viewsActions
{
  protected function buildQuery() {
    return parent::buildQuery()
      ->groupBy('lot_id')
      ->addSelect('COUNT(lot_id) as nb')
      ->addSelect('lot_type');
  }
}
