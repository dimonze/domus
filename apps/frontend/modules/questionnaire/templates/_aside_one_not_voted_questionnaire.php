<div class="rc-box polls">
  <div class="rc t"><div></div></div>
  <div class="content">
    <h2><?= link_to('<span class="png24"></span>Опросы', '@questionnaire')?></h2>
    <form name="questionnaire_<?= $questionnaire->id ?>" action="<?= url_for('@questionnaire_vote?id=' . $questionnaire->id) ?>">
      <h4><?= $questionnaire->title ?></h4>
      <?php foreach ($questionnaire->Answers as $answer): ?>
        <div><input type="radio" name="answer" id="poll_answer_<?= $answer->id ?>" value="<?= $answer->id ?>" /><label for="poll_answer_<?= $answer->id ?>"><?= $answer->title ?></label></div>
      <? endforeach ?>
      <div class="row2">
        <a class="green-button questionnaire_vote" href="#">Проголосовать<span></span></a>
      </div>
    </form>
  </div>
  <a href="<?= url_for('@questionnaire') ?>" class="bottom-link">Все опросы</a>
  <div class="rc b"><div></div></div>
</div>