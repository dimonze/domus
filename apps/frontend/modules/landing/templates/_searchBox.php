<?php if (!empty($lpages)): ?>
  <div class="grey-round-box grey-adv-box" id="searchGreyBlock">
    <div class="tl rc"></div>
    <div class="tr rc"></div>
    <div class="content lpages">
      <?php foreach ($lpages as $page): ?>
        <p>
          <?= link_to($page['attrs']['h1'], '@search_landing?type=' . Lot::getRoutingType($page['attrs']['type']) . '&slug=' . $page['attrs']['url'] ) ?>
        </p>
      <?php endforeach ?>
    </div>
    <div class="bl rc"></div>
    <div class="br rc"></div>
  </div>
<?php endif ?>