<div class="rc-box polls">
  <div class="rc t"><div></div></div>
  <div class="content">
    <h2><?= link_to('<span class="png24"></span>Опросы', '@questionnaire')?></h2>
    <?php $total_votes = $questionnaire->countQuestionaireVotes() ?>
    <h4><?= $questionnaire->title ?></h4>
    <?php foreach ($questionnaire->Answers as $answer): ?>
      <div class="reslult">
        <div class="pool-result-name"><?= $answer->title ?></div>
        <div class="graph" style="width: <?= $total_votes ? round($answer->vote / $total_votes * 100) : '0' ?>%;">
          <div class="grph-r png24"></div>
          <span class="graph-val"><?= $answer->vote ?> (<?= $total_votes ? round($answer->vote / $total_votes * 100) : '0' ?>%)</span>
        </div>
      </div>
    <?php endforeach ?>
    </div>
    <a href="<?= url_for(@questionnaire) ?>" class="bottom-link">Все опросы</a>
  <div class="rc b"><div></div></div>
</div>
