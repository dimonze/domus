<?php $total_votes = $questionnaire->countQuestionaireVotes() ?>
<div class="pool-item">
  <h4><?= $questionnaire->title ?></h4>
  <?php foreach($questionnaire->Answers as $answer): ?>
  <div class="reslult">
    <div class="pool-result-name"><?= $answer->title ?></div>
    <div class="graph" style="width: <?= $total_votes ? round($answer->vote / $total_votes  * 100) : '0' ?>%;">
      <div class="grph-r png24"></div>
      <span class="graph-val"><?= $answer->vote ?> (<?= $total_votes ? round($answer->vote / $total_votes  * 100) : '0' ?>%)</span>
    </div>
  </div>
  <?php endforeach ?>
</div>