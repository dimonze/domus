<?php

/**
 * questionnaire module configuration.
 *
 * @package    domus
 * @subpackage questionnaire
 * @author     Garin Studio
 * @version    SVN: $Id: configuration.php 12474 2008-10-31 10:41:27Z fabien $
 */
class questionnaireGeneratorConfiguration extends BaseQuestionnaireGeneratorConfiguration
{
  public function getFilterDefaults() {
    return array('post_type' => 'questionnaire');
  }
}
