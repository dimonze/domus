<div class="sf_admin_form_row sf_admin_text sf_admin_form_field_answer_<?= $id ?>">
  <div>
    <label for="questionnaire_answer_<?= $id ?>"><a class="del-answer" href="#">удалить</a></label>
    <div class="content"><table>
        <tbody><tr>
            <th><label for="questionnaire_answer_<?= $id ?>_title">Ответ</label></th>
            <td><input type="text" id="questionnaire_answer_<?= $id ?>_title" value="" name="questionnaire[answer_<?= $id ?>][title]"></td>
          </tr>
          <tr>
            <th><label for="questionnaire_answer_<?= $id ?>_vote">Число голосов</label></th>
            <td><input type="text" id="questionnaire_answer_91_vote" value="0" name="questionnaire[answer_<?= $id ?>][vote]"><input type="hidden" id="questionnaire_answer_<?= $id ?>_id" value="<?= $id ?>" name="questionnaire[answer_<?= $id ?>][id]"></td>
          </tr>
        </tbody></table></div>
  </div>
</div>