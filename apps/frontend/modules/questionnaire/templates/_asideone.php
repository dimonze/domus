<?php
if(isset($not_voted)) {
  if($not_voted) include_partial('questionnaire/aside_one_not_voted_questionnaire', array('questionnaire' => $questionnaire));
  else include_partial('questionnaire/aside_one_voted_questionnaire', array('questionnaire' => $questionnaire));
}