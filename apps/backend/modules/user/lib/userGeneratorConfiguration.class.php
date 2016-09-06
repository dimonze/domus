<?php

/**
 * user module configuration.
 *
 * @package    domus
 * @subpackage user
 * @author     Garin Studio
 * @version    SVN: $Id: configuration.php 12474 2008-10-31 10:41:27Z fabien $
 */
class userGeneratorConfiguration extends BaseUserGeneratorConfiguration
{
  public function getPagerMaxPerPage()
  {
    $user = sfContext::getInstance()->getUser();
    $filters = $user->getAttribute('user.filters', $this->getFilterDefaults(), 'admin_module');
    if (!empty($filters['per-page'])) {
      return $filters['per-page'];
    }
    return 10;
  }

}
