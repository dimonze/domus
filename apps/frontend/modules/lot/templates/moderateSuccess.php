<?php $has_actions = $sf_user->hasCredential(array('moder-actions', 'moder-delete'), false) ?>

<div class="contentLeft">
<!--  <div name="moder-filters">
    <span>Сохранённые фильтры</span>
    <?php //echo include_component('filter', 'show') ?>
  </div>-->
  <?= form_tag('lot/moderate', 'method=post class="searchForm moderate"') ?>
  <table class="moderate-tbl">
  	<tbody><tr>
  		<td>
  		  <label>Тип</label>
  		</td><!-- 1.1 -->
  		<td>
  		  <?= $filterForm['type'] ?>
  		</td><!-- 1.2 -->
  		<td>
  		  <label>Регион</label>
  		</td><!-- 1.3 -->
  		<td>
  		  <?= $filterForm['region_id']?>
  		</td><!-- 1.4 -->
  		<td class="moderate-chk-b" rowspan="4">
  		  <div>
  		    <?= $filterForm['usertype1'] ?> <?= $filterForm['usertype1']->renderLabel() ?>
        </div>
        <div>
          <?= $filterForm['usertype2'] ?> <?= $filterForm['usertype2']->renderLabel() ?>
        </div>
        <div>
          <?= $filterForm['usertype3'] ?> <?= $filterForm['usertype3']->renderLabel() ?>
        </div>
        <div>
          <?= $filterForm['usertype4'] ?> <?= $filterForm['usertype4']->renderLabel() ?>
        </div>
        <div>
          <?= $filterForm['usertype5'] ?> <?= $filterForm['usertype5']->renderLabel() ?>
        </div>
  		</td><!-- 1.5 checkbox -->
  	</tr><!-- 1 -->
  	<tr>
  		<td>
  		  <label>id</label>
  		</td><!-- 2.1 -->
  		<td>
  		  <?= $filterForm['id'] ?>
  		</td><!-- 2.2 -->
  		<td>
  		  <label>Адрес</label>
  		</td><!-- 2.3 -->
  		<td>
  		  <?= $filterForm['address'] ?>
  		</td><!-- 2.4 -->
  		<!-- 2.5 checkbox -->
  	</tr><!-- 2 -->
  	<tr>
  		<td rowspan="2">
  		  <label>Дата начала</label>
  		</td><!-- 3.1 -->
  		<td>
  		  <div class="dbugger"><?= $filterForm['created_at_from'] ?></div>
  		</td><!-- 3.2 -->
  		<td rowspan="2">
  		  <label>Дата окончания</label>
  		</td><!-- 3.3 -->
  		<td>
  		  <div class="dbugger"><?= $filterForm['active_till_from'] ?></div>
  		</td><!-- 3.4 -->
  		<!-- 3.5 checkbox -->
  	</tr><!-- 3 -->
  	<tr>
  		<!-- 4.1 Дата начала -->
  		<td>
  		  <div class="dbugger"><?= $filterForm['created_at_to'] ?></div>
  		</td><!-- 4.2 -->
  		<!-- 4.3 Дата окончания -->
  		<td>
  		  <div class="dbugger"><?= $filterForm['active_till_to'] ?></div>
  		</td><!-- 4.4 -->
  		<!-- 4.5 checkbox -->
  	</tr><!-- 4 -->
  	<tr>
  		<td>
  		  <label>Логин</label>
  		</td><!-- 5.1 -->
  		<td>
  		  <?= $filterForm['email'] ?>
  		</td><!-- 5.2 -->
  		<td>
  		  <label>Цена</label>
  		</td><!-- 5.3 -->
  		<td class="from-to-inline">
  		  <div>
          <?= $filterForm['price_from'] ?>
          <?= $filterForm['price_to'] ?>
        </div>
  		</td><!-- 5.4 -->
  		<td>
  		  <?= $filterForm['status'] ?>
  		</td><!-- 5.5 -->
  	</tr><!-- 5 -->
  	<tr>
  		<td>
  		  <label>ФИО</label>
  		</td><!-- 6.1 -->
  		<td>
  		  <?= $filterForm['username'] ?>
  		</td><!-- 6.2 -->
  		<td>
  		  <label>Комнат</label>
  		</td><!-- 6.3 -->
  		<td class="from-to-inline">
  		  <div><?= $filterForm['num_rooms'] ?></div>
  		</td><!-- 6.4 -->
  		<td>
  		  <?= $filterForm['per-page'] ?>
  		</td><!-- 6.5 -->
  	</tr><!-- 6 -->
  	<tr>
  		<td>
  		  <label>Телефон</label>
  		</td><!-- 7.1 /-->
  		<td>
  		  <?= $filterForm['phone'] ?>
  		</td><!-- 7.2 /-->
  		<td>
  		  <label>Площадь</label>
  		</td><!-- 7.3 -->
  		<td class="from-to-inline">
  		  <div>
          <?= $filterForm['area_from'] ?>
          <?= $filterForm['area_to'] ?>
        </div>
  		</td><!-- 7.4 -->
  		<td>
  		  <?= $filterForm['sort'] ?>
  		</td><!-- 7.5 -->
  	</tr><!-- 7 -->
  	<tr>
  		<td>
        <label>Описание</label>
  		</td><!-- 8.1 -->
  		<td>
        <?= $filterForm['description'] ?>
  		</td><!-- 8.2 -->
  		<td>
  		  <label>Участок</label>
  		</td><!-- 8.3 -->
  		<td class="from-to-inline">
        <div>
          <?= $filterForm['area_country_from'] ?>
          <?= $filterForm['area_country_to'] ?>
        </div>
      </td><!-- 8.4 -->
  		<td>
        <div class="search-map-wrp">
          <?= $filterForm['map_search'] ?><?= $filterForm['map_search']->renderLabel()?>
        </div>
      </td><!-- 8.5 -->
  	</tr><!-- 8 -->
  	<tr>
  		<td></td><!-- 9.1 -->
  		<td></td><!-- 9.2 -->
  		<td>
  		</td><!-- 9.3 -->
  		<td>
  		</td><!-- 9.4 -->
  		<td>
        <?= $filterForm['coords'] ?>
        <?= $filterForm['region_center_coords'] ?>
        <div class="buttons">
          <span class="formButton"><input type="submit" value="Найти"></span>
        </div>
  		</td><!-- 9.5 -->
  	</tr><!-- 9 -->
  </tbody></table>
  </form>
  <!--searchForm end -->
  <div class="compareBox">
    <div class="mapBox">
      <div id="region_coords" style="display: none;" rel="
        <?= $sf_user->current_region->latitude ?>,
        <?= $sf_user->current_region->longitude ?>,
        <?= $sf_user->current_region->zoom ?>,
        <?= $sf_user->current_region->id ?>">
      </div>
      <div id="gmap" style="display: none;"></div>
      <div class="boxBack_01">
        <a href="#" class="collapse collapse-full-text">Развернуть карту</a>
        <div class="clearBoth"></div>
      </div>
    </div>
    <div class="mapPopup" style="display:none;"></div>
  </div>
</div>
<!--contentLeft end -->

<?php include_component('menu', 'user') ?>
<div class="clearBoth"></div>
  <?php if (!$pager->getNbResults()): ?>
    <p>Объявлений не найдено.</p>
  <?php else: ?>
    <h4>
      Найдено
      <?= $pager->getNbResults()?>
      объявлен<?= ending($pager->getNbResults(), 'ие', 'ия', 'ий') ?>
    </h4>
    <?php include_partial('global/pagination', array('pager' => $pager)) ?>
    <?php echo form_tag('lot/moderate', 'method=post class=resultForm') ?>
      <fieldset>
        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="resultTable">
          <thead>
            <tr>
              <th class="rt_01">
                <?php if ($has_actions): ?>
                  <?= input_tag('check_all', null, 'type=checkbox') ?>
                <?php endif ?>
              </th>
              <th>id</th>
              <th>Тип</th>
              <th>Предложение</th>
              <th>Адрес</th>
              <?php if (isset($filterForm['type'])): ?>
                <?php if ($filterForm['type']->getValue() == 'apartament-sale' || $filterForm['type']->getValue() == 'apartament-rent'): ?>
                  <th>Число комнат</th>
                <?php endif ?>
              <?php endif ?>
              <?php if (isset($filterForm['type']) && $filterForm['type']->getValue() != ''): ?>
                <th>Площадь помещения</th>
                <?php if ($filterForm['type']->getValue() == 'house-sale'
                  || $filterForm['type']->getValue() == 'house-rent'): ?>
                  <th>Площадь участка</th>
                <?php endif ?>
              <?php endif ?>
              <th>Цена</th>
              <th>Логин</th>
              <th>Размещено</th>
              <th>Обновлено</th>
              <th>Закончится</th>
              <th>&nbsp;</th>
              <th>&nbsp;</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($pager->getResults() as $lot): ?>
            <tr class="row-<?= $lot->status ?>" latitude="<?= $lot->latitude ?>" longitude="<?= $lot->longitude ?>" rel="<?= $lot->id ?>">
              <td class="rt_01">
                <?php if ($has_actions): ?>
                  <?= input_tag('id[]', $lot->id,
                      'type=checkbox rel=user_id:' . $lot->User->id
                  ) ?>
                <?php endif ?>
              </td>
              <td><?= $lot->id ?></td>
              <td><?= $lot->type_text ?></td>
              <td>
                <?php echo $suggestion = $lot->fieldsArray ?>
              </td>
              <td>
                <?php if (in_array($lot->status, array('active', 'inactive')) && !$lot->deleted_at): ?>
                  <?= link_to($lot->address_full, prepare_show_lot_url($lot)) ?>
                <?php else: ?>
                  <?= $lot->address_full ?>
                <?php endif ?>
              </td>
                <?php if (isset($filterForm['type']) && $filterForm['type']->getValue() != ''): ?>
                  <?php if ($filterForm['type']->getValue() == 'apartament-sale' || $filterForm['type']->getValue() == 'apartament-rent'): ?>
                    <td><?php echo preg_replace('/\D+/', '', $suggestion)?></td>
                  <?php endif ?>
                <?php endif ?>
              <?php if (isset($filterForm['type']) && $filterForm['type']->getValue() != ''): ?>
                <td>
                  <?php foreach ($lot->LotInfo as $lot_info): ?>
                    <?php if ($filterForm['type']->getValue() == 'apartament-sale' || $filterForm['type']->getValue() == 'apartament-rent'): ?>
                      <?php if ($lot_info->field_id == 1): ?>
                        <?= $lot_info->value ?>
                      <?php endif ?>
                    <?php endif ?>
                    <?php if ($filterForm['type']->getValue() == 'house-sale' || $filterForm['type']->getValue() == 'house-rent'): ?>
                      <?php if ($lot_info->field_id == 26): ?>
                        <?= $lot_info->value ?>
                      <?php endif ?>
                    <?php endif ?>
                    <?php if ($filterForm['type']->getValue() == 'commercial-sale' || $filterForm['type']->getValue() == 'commercial-rent'): ?>
                      <?php if ($lot_info->field_id == 46): ?>
                        <?= $lot_info->value ?>
                      <?php endif ?>
                    <?php endif ?>
                  <?php endforeach ?>
                </td>
                  <?php if ($filterForm['type']->getValue() == 'house-sale'
                    || $filterForm['type']->getValue() == 'house-rent'): ?>
                    <td>
                      <?php foreach ($lot->LotInfo as $lot_info): ?>
                        <?php if ($lot_info->field_id == 27): ?>
                          <?= $lot_info->value ?>
                        <?php endif ?>
                      <?php endforeach ?>
                    </td>
                  <?php endif ?>
              <?php endif ?>
              <td>
                <?= $lot->getPriceFormated() ?>
                <?= $lot->getPriceHelp() ?>
              </td>
              <td>
                <?= link_to(substr($lot->User->email, 0, 7) . '&hellip;',
                      '@user_card?id=' . $lot->User->id,
                      sprintf('title=%s - %s - %s', $lot->User->email, $lot->User->name, $lot->User->company_name)
                    ) ?>
              </td>
              <td><?= date('d.m.Y', strtotime($lot->created_at)) ?></td>
              <td><?= date('d.m.Y', strtotime($lot->updated_at)) ?></td>
              <td><?= date('d.m.Y', strtotime($lot->active_till)) ?></td>
              <td class="notify">
              <?php if (null != $lot->moderator_message): ?>
                <?php echo link_to ('<strong>!</strong>', 'pm/get-message?id='.$lot->moderator_message, 'class=inner popup title="Отправлено сообщение" rel=notify') ?>
              <?php endif; ?>
              </td>
              <td class="actions">
                <ul class="actionList">
                  <?php if ($sf_user->hasCredential('moder-actions')): ?>
                    <li>
                      <?= link_to('отправить сообщение', '/user/pm/add?to='.$lot->User->id, 'class="popup send-mess" title="Отправить сообщение" rel=reg') ?>
                    </li>
                    <?php if (!$lot->deleted_at): ?>
                      <li>
                        <?= link_to('редактировать', 'lot/edit?id='.$lot->id, 'class=edit title=Редактировать') ?>
                      </li>
                      <?php if ($lot->active): ?>
                        <li>
                          <?= link_to('остановить показ', 'lot/restrict?id='.$lot->id, 'class=none title=Остановить показ') ?>
                        </li>
                        <li>
                          <?= link_to('остановить показ и отправить соощение', 'lot/restrict?id=' . $lot->id , 'name="stop-active-pm" class="popup send-mess-rep" title=Остановить показ и отправить сообщение rel="user_id:' . $lot->User->id . ', lot_id:' . $lot->id . '"') ?>
                        </li>
                      <?php else: ?>
                        <li>
                          <?= link_to('активировать', 'lot/setactive?id='.$lot->id, 'class=activate title=Активировать') ?>
                        </li>
                        <?php if ($lot->on_moderation): ?>
                          <li>
                            <?= link_to('остановить показ', 'lot/restrict?id='.$lot->id, 'class=none title=Остановить показ') ?>
                          </li>
                          <li>
                            <?= link_to('остановить показ и отправить соощение', 'lot/restrict?id=' . $lot->id , 'name="stop-active-pm" class="popup send-mess-rep" title=Остановить показ и отправить сообщение rel="user_id:' . $lot->User->id . ', lot_id:' . $lot->id . '"') ?>
                          </li>
                        <?php endif ?>
                      <?php endif ?>
                    <?php endif ?>
                  <?php endif ?>

                  <?php if ($sf_user->hasCredential('moder-delete')): ?>
                    <?php if (!$lot->deleted_at): ?>
                      <li>
                        <?= link_to('удалить', 'lot/delete?id='.$lot->id, 'class=delit title=Удалить') ?>
                      </li>
                    <?php else: ?>
                      <li>
                        <?= link_to('восстановить', 'lot/restore?id='.$lot->id, 'class=none title=Восстановить') ?>
                      </li>
                    <?php endif ?>
                  <?php endif ?>
                </ul>
              </td>
            </tr>
          <?php endforeach ?>
          </tbody>
        </table>

        <?php if ($has_actions): ?>
          <select name="do" class="select_01">
            <option value="">Выберите действие</option>
            <?php if ($sf_user->hasCredential('moder-actions')): ?>
              <option value="activate">Активировать</option>
              <option value="restrict">Запретить</option>
              <option value="restrict_send_message">Запретить и отправить сообщение</option>
            <?php endif ?>
            <?php if ($sf_user->hasCredential('moder-delete')): ?>
              <option value="delete">Удалить</option>
            <?php endif ?>
          </select>
          <?= input_tag('submit', 'OK', 'type=submit class=resultSubmit') ?>
          <br /><br />
        <?php endif ?>

      </fieldset>
    </form>

    <div class="clearBoth"></div>
    <?php include_partial('global/pagination', array('pager' => $pager)) ?>

  <?php endif ?>
