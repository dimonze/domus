<?php

/**
 * qa module configuration.
 *
 * @package    domus
 * @subpackage qa
 * @author     Garin Studio
 * @version    SVN: $Id: configuration.php 12474 2008-10-31 10:41:27Z fabien $
 */
class qaGeneratorConfiguration extends BaseQaGeneratorConfiguration
{
  public function getFilterDefaults() {
    return array('post_type' => 'qa');
  }
}
