<?php

/**
 * SpecialTargeting form.
 *
 * @package    form
 * @subpackage SpecialTargeting
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class SpecialTargetingForm extends BaseSpecialTargetingForm
{
  public function configure()
  {
    $this->setWidget('text', new sfWidgetFormTextareaTinyMCE(
      array(
        'width' => 600,
        'height'=> 350,
        'theme' => 'advanced',
        'config' => BaseForm::$TinyMCEConfig
      ),
      array(
        'class' => 'tiny_mce_c1'
      )
    ));
    unset($this['slug']);
  }
}