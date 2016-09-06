<?php

require_once dirname(__FILE__).'/../lib/post_authorGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/post_authorGeneratorHelper.class.php';

/**
 * post_author actions.
 *
 * @package    domus
 * @subpackage post_author
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12474 2008-10-31 10:41:27Z fabien $
 */
class post_authorActions extends autoPost_authorActions
{
  public function executeFilter(sfWebRequest $request) {
    $user_filters = $request->getParameterHolder()->get('post_author_filters', null);
    $deleted_at = array(
          'from' => array(
              'year' => '',
              'month' => '',
              'day' => ''
          ),
          'to' => array(
              'year' => '',
              'month' => '',
              'day' => ''
          )
      );
        
    if(isset($user_filters['deleted']) && $user_filters['deleted']){
      $deleted_at = array(
          'from' => array(
              'year' => 1900,
              'month' => 1,
              'day' => 1
          ),
          'to' => array(
              'year' => date('Y'),
              'month' => date('m'),
              'day' => date('d')
          )
      );
    }
    
    $user_filters['deleted_at'] = $deleted_at;
    $request->getParameterHolder()->set('post_author_filters', $user_filters);
    
    parent::executeFilter($request);
  }
}
