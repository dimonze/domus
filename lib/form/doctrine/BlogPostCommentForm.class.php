<?php

/**
 * BlogPostComment form.
 *
 * @package    form
 * @subpackage BlogPostComment
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class BlogPostCommentForm extends BaseBlogPostCommentForm implements AjaxFormInterface
{
  public function configure()
  {
    $this->setWidgets(array(
      'user_id'    => new sfWidgetFormInputHidden(),
      'post_id'    => new sfWidgetFormInputHidden(),
      'parent_id'  => new sfWidgetFormInputHidden(),
      'body'       => new sfWidgetFormTextarea(array('label'  =>  'Текст'))
    ));

    $this->setValidators(array(
      'user_id'    => new sfValidatorDoctrineChoice(array('model' => 'User')),
      'post_id'    => new sfValidatorDoctrineChoice(array('model' => 'BlogPost')),
      'parent_id'  => new sfValidatorDoctrineChoice(array('model' => 'BlogPostComment', 'required' => false)),
      'body'       => new sfValidatorString(
        array('max_length' => 1000),
        array('required'  =>  'Поле обязательно для заполнения.')),
    ));

    $this->widgetSchema->setNameFormat('comment[%s]');
    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);
  }

  public function save($con = null) {
    parent::save($con);
    if (! ($parent_node = Doctrine::getTable('BlogPostComment')->find($this->getValue('parent_id')))) {
      $treeObject = Doctrine::getTable('BlogPostComment')->getTree();
      $parent_node = $treeObject->fetchRoot($this->getObject()->post_id);      
    }
    if($parent_node) $this->getObject()->getNode()->insertAsLastChildOf($parent_node);
  }

  public function getErrorsArray()
  {
    return AjaxForm::getErrorsArray($this);
  }
}