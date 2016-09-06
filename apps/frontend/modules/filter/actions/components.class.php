<?php
/**
 * @package    2photo
 * @subpackage component
 * @author     Garin Studio
 * @version    SVN: $Id: components.class.php 801 2010-05-12 07:29:01Z s1l3nt $
 */

class filterComponents extends sfComponents
{
  public function executeShow(sfWebRequest $request)
  {
    $user = $this->getUser();
    $this->filters = Doctrine::getTable('ModeratorFilters')
      ->getFiltersForUser($user->id);
  }
}