<?php

/**
 * Cottage form.
 *
 * @package    domus
 * @subpackage form
 * @author     Garin Studio
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class TownhouseForm extends CottageForm
{
  public function configure()
  {
    parent::configure();
    $this->setDefault('type', 'townhouse');
  }
}
