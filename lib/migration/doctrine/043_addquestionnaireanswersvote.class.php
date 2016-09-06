<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class AddQuestionnaireAnswersVoteMigration extends Doctrine_Migration
{
	public function up()
	{
		$this->createTable('questionnaire_answers_vote', array(
      'id' =>
        array(
          'type' => 'integer',
          'length' => 20,
          'autoincrement' => true,
          'primary' => true,
        ),
      'post_id' =>
        array(
          'type' => 'integer',
          'unsigned' => true,
          'notnull' => true,
          'length' => 3,
        ),
      'answer_id' =>
        array(
          'type' => 'integer',
          'unsigned' => true,
          'notnull' => true,
          'length' => 3,
        ),
      'user_id' =>
        array(
          'type' => 'integer',
          'unsigned' => true,
          'notnull' => true,
          'length' => 4,
        ),
      ), array(
        'indexes' =>
          array(
            'uservote' =>
              array(
                'fields' =>
                  array(
                    0 => 'user_id',
                    1 => 'post_id',
                  ),
                'type' => 'unique',
              ),
          ),
        'primary' =>
          array(
            0 => 'id',
          ),
    ));

    $this->createForeignKey('questionnaire_answers_vote', array(
      'local' => 'answer_id',
      'foreign' => 'id',
      'foreignTable' => 'questionnaire_answers',
      'onUpdate' => NULL,
      'onDelete' => 'CASCADE',
      'name' => 'questionnaire_answers_vote_answer_id',
    ));
    $this->createForeignKey('questionnaire_answers_vote', array(
      'local' => 'post_id',
      'foreign' => 'id',
      'foreignTable' => 'post',
      'onUpdate' => NULL,
      'onDelete' => 'CASCADE',
      'name' => 'questionnaire_answers_vote_post_id',
    ));
    $this->createForeignKey('questionnaire_answers_vote', array(
      'local' => 'user_id',
      'foreign' => 'id',
      'foreignTable' => 'user',
      'name' => 'questionnaire_answers_vote_user_id',
    ));
	}

	public function down()
	{
    $this->dropForeignKey('questionnaire_answers_vote', 'questionnaire_answers_vote_user_id');
    $this->dropForeignKey('questionnaire_answers_vote', 'questionnaire_answers_vote_post_id');
    $this->dropForeignKey('questionnaire_answers_vote', 'questionnaire_answers_vote_answer_id');
		$this->dropTable('questionnaire_answers_vote');
	}
}