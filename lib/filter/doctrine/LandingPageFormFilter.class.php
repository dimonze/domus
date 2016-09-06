<?php

/**
 * LandingPage filter form.
 *
 * @package    domus
 * @subpackage filter
 * @author     Garin Studio
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class LandingPageFormFilter extends BaseLandingPageFormFilter
{
  public function configure()
  {
    $types = array('' => '') + Lot::$type_ru;
    
    $this->getWidget('type')->setOption('choices', $types);
    $this->getValidator('type')->setOption('choices', array_keys($types));
    
    $this->getWidget('title')->setOption('with_empty', false);
    $this->getWidget('h1')->setOption('with_empty', false);
    
    $this->useFields( array( 'region_id', 'type', 'url', 'title', 'h1'  ) );
  }
}
