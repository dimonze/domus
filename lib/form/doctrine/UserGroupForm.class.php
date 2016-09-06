<?php

/**
 * UserGroup form.
 *
 * @package    form
 * @subpackage UserGroup
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class UserGroupForm extends BaseUserGroupForm
{
  public function configure()
  {
    $this->widgetSchema['credentials'] = new sfWidgetFormChoice(
      array(
        'choices'  => UserGroup::$credentials_named,
        'multiple' => true,
        'expanded' => true,
      ),
      array(
        'class' => 'group-credentials'
    ));
    
    $this->validatorSchema['credentials'] = new sfValidatorChoice(array(
      'choices'  => UserGroup::$credentials,
      'multiple' => true,
    ));
  }
}