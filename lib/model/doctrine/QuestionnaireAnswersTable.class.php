<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class QuestionnaireAnswersTable extends Doctrine_Table
{

  public function getAnswer($questionnaire_id, $answer_id) {
    if (!($questionnaire_id || $answer_id))
      return false;
    $q = $this->createQuery()
        ->where('id = ?', $answer_id)
        ->andWhere('post_id = ?', $questionnaire_id)
        ->fetchOne();

    $r = count($q) ? $q : false;
    return $r;
  }

}