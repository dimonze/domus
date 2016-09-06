<?php

/**
 * comments actions.
 *
 * @package    domus
 * @subpackage comments
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class commentsActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $this->forward('@homepage');
  }

  public function executeDelete (sfWebRequest $request)
  {
    $this->forward404Unless($this->getUser()->hasCredential('moder-portal_comments'));
    $this->forward404Unless($request->isXmlHttpRequest());
    if (!$request->hasParameter('id')){
      return $this->renderText(json_encode(array('complite' => 'false')));
    }

    $comment = Doctrine::getTable('PostComment')->find($request->getParameter('id'));
    if (!$comment){
      return $this->renderText(json_encode(array('complite' => 'false')));
    }

    $comment->deleted = true;
    $comment->save();
    return $this->renderText(json_encode(array('complite' => 'delete')));
  }

  public function executeGetform(sfWebRequest $request)
  {
    if (!$request->isXmlHttpRequest()) {
      return false;
    }    
    if (!$request->hasParameter('post_type') || !$request->hasParameter('post_id')) {
      return false;
    }
    $post_type = $request->getParameter('post_type');
    if ($post_type == 'blog') {
      if ($this->getUser()->isAuthenticated()) {
        $form = new BlogPostCommentForm();
      }
    }
    else {
      $form = new PostCommentForm();
    }
    $this->form       = (!empty($form)) ? $form : false;
    $this->parent_id  = $request->getParameter('parent_id');
    $this->post_type  = $request->getParameter('post_type');
    $this->post_id    = $request->getParameter('post_id');
    
    $this->setLayout(false);
  }

  public function executeAdd(sfWebRequest $request)
  {
    $user = $this->getUser();
    if(!$request->hasParameter('post_type')) {
      return false;
    }    
    $post_type  = $request->getParameter('post_type');
    if ($post_type == 'blog' && $user->isAuthenticated()) {
      $form = new BlogPostCommentForm();
    }
    else if ($post_type != 'blog'){
      $form = new PostCommentForm();
    }
    else {
      return $this->renderText(json_encode(array('errors'  => true)));
    }
    if ($request->isMethod('post') && $request->hasParameter('comment')) {
      $data = $request->getParameter('comment');      
      if ($user->isAuthenticated()) {
        $data['user_id'] = $user->id;
      }
      if (!empty($data['parent_id']) && null == $data['parent_id']) {
        unset($data['parent_id']);
      }
      
      $form->bind($data);
      if ($form->isValid()) {        
        $form->save();
        $comment = $form->getObject();

        if ($post_type != 'blog') {
          //send PM
          $pm = new PM(null, true);
          $pm->sendCommentAsPm($comment);
        }
        
        return $this->renderText(json_encode(array('save' =>  'success')));
      }
      else {                
        return $this->renderText(json_encode($form->getErrorsArray()));
      }
    }
    $this->setLayout(false);
    return false;
  }
}
