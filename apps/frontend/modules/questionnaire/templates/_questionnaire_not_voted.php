<div class="pool-item">
  <form name="questionnaire_<?= $questionnaire->id ?>" action="<?= url_for('@questionnaire_vote?id=' . $questionnaire->id) ?>">
    <h4><?= $questionnaire->title ?></h4>
    <?php foreach ($questionnaire->Answers as $answer): ?>
      <div><input type="radio" name="answer" id="poll_answer_<?= $answer->id ?>" value="<?= $answer->id ?>" /><label for="poll_answer_<?= $answer->id ?>"><?= $answer->title ?></label></div>
    <? endforeach ?>
    <div class="row2">
      <a href="#" class="green-button questionnaire_vote">Проголосовать<span></span></a>
    </div>
  </form>
</div>