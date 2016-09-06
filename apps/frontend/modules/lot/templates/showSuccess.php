<?php use_javascript('jquery.scrollable.js') ?>
<?php $sf_response->addMeta('name', lot_title($lot)) ?>

<?php slot('facebook') ?>
  <meta property="og:image" content="<?= lot_image($lot, 320, 240) ?>" />
<?php end_slot() ?>

<?php if ($lot->region_id == 77 && in_array($lot->type, array('apartament-sale'))): ?>
  <?php slot('estate_on_duty') ?>
    <?php include_partial(sprintf('side_services/show_lot_%s_%s_line', $lot->type, $lot->region_id)) ?>
  <?php end_slot() ?>
<?php endif; ?>

<?php if($bc = breadcrumbs($lot)): ?>
  <?php slot('breadcrumbs'); ?>
    <div class="breadcrumbs">
      <?= $bc ?>
    </div>
  <?php end_slot(); ?>
<?php endif; ?>
<div class="contentLeft">
  <?php include_partial('lot/moderator-tools', array('lot' => $lot)) ?>
  <?php cached_component('banner', 'nbOneSpecial', array('banner_id' => $lot->nb_banner_id), 'show_nb_special_' . $lot->nb_banner_id) ?>
  <?php OpenX::updateStat($lot->nb_banner_id); ?>

  <div class="productOptionsBox">
    <div class="priceBox">
      <div class="text_01">
        <?= $lot->getPriceFormated() ?>
        <?php if($lot->type != 'cottage-sale') echo $lot->getPriceHelp(); ?>
      </div>
      <?= $lot->getPriceFormated('USD') ?>
      <?= $lot->getPriceFormated('EUR') ?>

      <?php 
        $price = ($lot->getPriceFormated() / 12);
      ?>
      <a href="#" class="advert__button">
        <div class="advert__button-block">
          <img src="#" alt="" class="advert__button-img">
          <p class="advert__button-text">
            Ипотека от банка МКБ<br>
            Ежемесячный платеж от <span><?= $price ?></span> руб.
          </p>
        </div>
      </a>

      <?php if($lot->type == 'cottage-sale') echo '<span id="cottage-price-help">'.$lot->getPriceHelp().'</span>'; ?>
      <?php if (!in_array($lot->region_id, array(77,78))): ?>
        <?= link_to_banners($lot->type, $lot->region_id, 'class=nice_button'); ?>
      <?php endif; ?>
    </div>

    <?php foreach ($lot->LotInfoArray as $group): ?>
      <?php if (count($group)): ?>
      <ul class="productOptionsList">
        <?php foreach($group as $lotInfo): ?>
          <li>
            <?php if (isset($lotInfo['id']) && in_array($lotInfo['id'], array(68,69))) continue; ?>
            <?php if (!isset($lotInfo['id']) && isset($lotInfo['name'])) continue; ?>
            <span class="def"><span><?= $lotInfo['name'] ?>:</span></span> <span class="val"><span><?= $lotInfo['value'] ?>
            <?php if ($lotInfo['help']): ?>
              <i><?= $lotInfo['help'] ?></i>
            <?php endif ?>
            </span></span>
          </li>
        <?php endforeach ?>
      </ul>
      <?php endif ?>
    <?php endforeach ?>
  </div>

  <div class="productImagesBox">
    <?php if (count($images = lot_images($lot))): ?>
      <div class="big-img-card">
        <table cellpadding="0" cellspacing="0">
          <tr>
            <td>
              <?= image_tag(
                lot_image($lot, 320, 240),
                array('alt' => lot_title($lot), 'class' => 'bigImage')
              ) ?>
            </td>
          </tr>
        </table>
      </div>

      <div class="scrollImages" style="<?= count($images) > 1 ? '' : 'display: none' ?>">
        <a href="#" class="scrollLeft" title="назад">&nbsp;</a>
        <a href="#" class="scrollRight" title="вперед">&nbsp;</a>

        <div class="scrollBox">
          <table cellpadding="0" cellspacing="0">
            <tr>
              <?php foreach ($images as $id => $image): ?>
                  <td>
                    <?= image_tag($image['thumb'], array(
                      'alt'         => lot_title($lot) . '. Фото-' . ++$id,
                      'data-medium' => $image['medium'],
                      'data-big'    => $image['big'],
                    )) ?>
                  </td>
              <?php endforeach ?>
            </tr>
          </table>
        </div>
        <div class="clearBoth"></div>
      </div>

    <?php else: ?>
      <?= image_tag_s('no-photo', 'class=bigImage') ?>

    <?php endif ?>

    <?php $user = $lot->User ?>
    <div class="contactsSeller">

      <h2>Контактная информация:</h2>

      <?php if ($lot->active): ?>

        <?php if (!$user->is_inner && $user->photo): ?>
          <?= image_tag(photo($user, 80, 60), 'class=logoSeller') ?>
        <?php endif ?>

        <ul class="contactsList">
          <?php if ($user->is_nb_parser): ?>
            <?php if ($lot->organization_contact_name): ?>
              <li><?= $lot->organization_contact_name ?></li>
            <?php endif ?>
            <?php if($lot->organization_link || $lot->nb_more_info_link || $lot->nb_banner_id): 
                if(!empty($lot->organization_link) && strpos($lot->organization_link, 'http://') !== 0) $lot->organization_link = "http://{$lot->organization_link}";
                if(!empty($lot->nb_more_info_link) && strpos($lot->nb_more_info_link, 'http://') !== 0) $lot->nb_more_info_link = "http://{$lot->nb_more_info_link}";
              ?>
              <li><br/>
                <noindex>
                  <?php if (0 === strpos($lot->type,'apartament')): ?>
                    <?= link_to('Подробная информация о квартире/комнате', $lot->organization_link, array( 'target' => '_blank' )) ?>
                  <?php elseif (0 === strpos($lot->type,'house')): ?>
                    <?= link_to ('Подробная информация о доме/участке', $lot->organization_link, array( 'target' => '_blank' )) ?>
                  <?php elseif (0 === strpos($lot->type,'commercial')): ?>
                    <?= link_to('Подробная информация о недвижимости', $lot->organization_link, array( 'target' => '_blank' )) ?>
                  <?php elseif (in_array($lot->type, Lot::$_subdomains) && ($lot->nb_more_info_link || $lot->nb_banner_id)): ?>
                      <?php if ($lot->nb_banner_id): ?>
                        <?= link_to(nb_button_with_jk($lot), OpenX::generateBannerLink($lot->nb_banner_id), array('class' => 'detailed','target' => '_blank')) ?>
                      <?php else: ?>
                        <?= link_to(nb_button_with_jk($lot), $lot->nb_more_info_link, array('class' => 'detailed','target' => '_blank')) ?>
                      <?php endif ?>
                  <?php endif ?>
                </noindex>
              </li>
            <?php endif ?>


          <?php elseif ($user->is_inner): ?>
            <?php if ($lot->organization_contact_name): ?>
              <li><?= $lot->organization_contact_name ?></li>
            <?php endif ?>

          <?php elseif($user->is_partner): ?>
            <?php if ($lot->organization_contact_name): ?>
                <li><?= $lot->organization_contact_name ?></li>
              <?php else: ?>
                <li><?= $user->name ?></li>
              <?php endif ?>
          <?php else: ?>
            <?php if ($user->company_name): ?>
              <li><?= link_to($user->company_name, 'user_card', $user) ?></li>

              <?php if(($lot->organization_link || $lot->nb_more_info_link || $lot->nb_banner_id) && !$user->is_partner): 
                  if(!empty($lot->organization_link) && strpos($lot->organization_link, 'http://') !== 0) $lot->organization_link = "http://{$lot->organization_link}";
                  if(!empty($lot->nb_more_info_link) && strpos($lot->nb_more_info_link, 'http://') !== 0) $lot->nb_more_info_link = "http://{$lot->nb_more_info_link}";
                ?>
                <li><br/>
                  <noindex>
                    <?php if (0 === strpos($lot->type,'apartament')): ?>
                      <?= link_to('Подробная информация о квартире/комнате', $lot->organization_link, array( 'target' => '_blank' )) ?>
                    <?php elseif (0 === strpos($lot->type,'house')): ?>
                      <?= link_to ('Подробная информация о доме/участке', $lot->organization_link, array( 'target' => '_blank' )) ?>
                    <?php elseif (0 === strpos($lot->type,'commercial')): ?>
                      <?= link_to('Подробная информация о недвижимости', $lot->organization_link, array( 'target' => '_blank' )) ?>
                    <?php elseif (in_array($lot->type, Lot::$_subdomains) && ($lot->nb_more_info_link || $lot->nb_banner_id)): ?>
                      <?php if ($lot->nb_banner_id): ?>
                        <?= link_to(nb_button_with_jk($lot), OpenX::generateBannerLink($lot->nb_banner_id), array('class' => 'detailed','target' => '_blank')) ?>
                      <?php else: ?>
                        <?= link_to(nb_button_with_jk($lot), $lot->nb_more_info_link, array('class' => 'detailed','target' => '_blank')) ?>
                      <?php endif ?>
                   <?php endif ?>
                  </noindex>
                </li>
              <?php endif ?>

            <?php else: ?>
              <li>
                <?= link_to($user->name, 'user_card', $user) ?>
                <?php if ('realtor' == $user->type): ?>
                  <?php $type = array('объявление', 'объявления', 'объявлений') ?>
                  <?php $lots_string = ending($user->active_count, $type[0], $type[1], $type[2]) ?>
                  <span>(<?= $user->active_count . '&nbsp;' . $lots_string ?>)</span>
                <?php endif ?>
              </li>
              <?php if ($lot->organization_contact_name): ?>
                <li><?= $lot->organization_contact_name ?></li>
              <?php endif ?>
            <?php endif ?>
          <?php endif ?>

          <?php if ($lot->organization_contact_phone || !$user->is_nb_parser): ?>
            <li class="tel">
              <noindex>
                <?= link_to('Показать телефон', 'lot/phone?id=' . $lot->id, 'class=post-load inner') ?>
              </noindex>
              <var></var>
            </li>
          <?php endif ?>
          <?php if (!$user->is_partner): ?>
          <li class="q">
            <?php $names = sfConfig::get('app_lot_types') ?>
            <?php if ($sf_user->isAuthenticated()): ?>
            <?= link_to('Задать вопрос о ' . $names[$sf_params->get('type')]['pm-ask'],
                'pm/add?question=1&to=' . $user->id,
              'class=popup inner rel=reg') ?>
            <?php else: ?>
            <?= link_to('Задать вопрос о ' . $names[$sf_params->get('type')]['pm-ask'],
                'pm/addnologin?to=' . $user->id,
              'class=popup inner rel=reg') ?>
            <?php endif ?>
            <var></var>
          </li>
          <?php endif?>
        </ul>


      <?php else: ?>
        <ul class="contactsList">
          <li>Показ объявления остановлен</li>
        </ul>

      <?php endif ?>

      <ul class="contactsList">
        <li class="cl_01">
          <span class="rate">Рейтинг объявления: <?= $lot->rating ?></span>
          <?= date('d.m.Y', strtotime($lot->updated_at)) ?>
        </li>

        <?php if ($lot->active && !$user->is_nb_parser): ?>
          <?php if($user->is_partner): ?>
            <?php if ($user->company_name): ?>
              <li class="lot-partner"><?= link_to('Объявление от партнера ' . $user->company_name, 'user_card', $user) ?></li>
            <?php else: ?>
              <li class="lot-partner"><?= link_to('Объявление от партнера', 'user_card', $user) ?></li>
            <?php endif ?>
          <?php endif ?>
        <?php endif ?>
      </ul>
    </div>

  </div>
  <div class="clearBoth"></div>

  <p><?= get_full_description($lot) ?></p>

  <?php include_partial('banner/block2-inline-spec') ?>

  <?php if ($lot->type == 'new_building-sale') include_partial('flats_show', array('lot' => $lot)) ?>
  <?php if ($lot->type == 'cottage-sale') include_partial('cottages_show', array('lot' => $lot)) ?>

  <?php if (sfConfig::get('lot_noindex')): ?>
    <noindex>
  <?php endif ?>
  <?php
    include_component(
      'lot', 'actions',
      array('lot' => $lot, 'class' => 'productMenu', 'actions' => array('favourite', 'print', 'compare', 'notify', 'claim')))
  ?>
  <?php if (sfConfig::get('lot_noindex')): ?>
    </noindex>
  <?php endif ?>
  <?php include_partial('lot/moderator-tools', array('lot' => $lot)) ?>

  <?php if ($lot->latitude && $lot->longitude): ?>
    <div class="mapBox">
      <div class="gmapstreetview" latitude="<?= $lot->latitude ?>" longitude="<?= $lot->longitude ?>" style="width:618px;height:294px"></div><hr />
      <div class="gmap" latitude="<?= $lot->latitude ?>" longitude="<?= $lot->longitude ?>"></div>
      <div class="boxBack_01">
        <noindex>
          <a href="#" class="collapse">Свернуть</a>
        </noindex>
        <div class="clearBoth"></div>
      </div>
    </div>
  <?php endif ?>
  <?php include_component('lot', 'list', array('lot' => $lot, 'type' => $lot->type, 'is_search' => true)) ?>
  <?php if ($lot->type == 'new_building-sale'): ?>
    <?php include_component('lot', 'similarNodes', array('lot' => $lot, 'type' => $lot->type, 'check_geo' => true, 'is_search' => true)) ?>
    <?php include_partial('banner/block2-inline-spec') ?>
    <?php include_component('lot', 'similarNodes', array('lot' => $lot, 'type' => $lot->type, 'check_price' => true, 'is_search' => true)) ?>
  <?php endif ?>
  <?php include_component('lot', 'similar', array('lot' => $lot, 'type' => $lot->type, 'is_search' => true, 'check_geo' => true, 'check_regular_price' => true)) ?>
  <?php include_partial('banner/block5-down-spec') ?>
</div>


<div class="contentRight">
  <?php include_partial('banner/search-right') ?>
  <a class="green-button" href="<?= lot_search_url($lot) ?>">
    <span>Показать похожие объявления &gt;</span>
  </a>

  <?php if (sfConfig::get('lot_noindex')): ?>
    <noindex>
  <?php endif ?>
  <?php include_partial('banner/block3-right-spec') ?>
  <?php include_partial('banner/lot-right') ?>
  <?php if (sfConfig::get('lot_noindex')): ?>
    </noindex>
  <?php endif ?>
</div>
