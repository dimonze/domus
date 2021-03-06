<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class AlterQuestionnaireAnswers067Migration extends Doctrine_Migration
{

  public function up() {
    $this->changeColumn('questionnaire_answers', 'title', 'string', array(
      'notnull' => true,
      'unique' => false,
      'length' => 255
    ));

    $this->removeIndex('questionnaire_answers', 'title_index');
    $this->removeIndex('questionnaire_answers', 'title');
  }

  public function down() {
    $this->changeColumn('questionnaire_answers', 'title', 'string', array(
      'fixed' => 1,
      'unique' => true,
      'notnull' => true,
      'length' => 200
    ));
  }

}