<h1>Статистика объявлений</h1>
<div class="contentStatistics">
  <form class="statParams cf" action="">
    <fieldset>
      <div class="row">
        <?= $filterForm['type']->renderLabel() ?>
        <?= $filterForm['type']->render() ?>
      </div>

      <div class="row">
        <?= $filterForm['region']->renderLabel() ?>
        <?= $filterForm['region']->render() ?>
      </div>
    </fieldset>
    <fieldset>
      <h4>Отчетные периоды:</h4>
      <div class="row">
        c <?= $filterForm['date_from']->render() ?> по <?= $filterForm['date_to']->render() ?>
      </div>
      <h4>Быстрый выбор:</h4>
      <div id="fast_switch">
        <div class="row">за 
          <a href="#" rel="<?= date('d.m.Y')?>">сегодня</a>, 
          <a href="#" rel="<?= date('d.m.Y', strtotime('-1 day'))?>">вчера</a>, 
          <span rel="<?= date('d.m.Y', strtotime('-1 week'))?>;<?= date('d.m.Y')?>">неделю</span>, 
          <a href="#" rel="<?= date('d.m.Y', strtotime('-1 month'))?>;<?= date('d.m.Y')?>">месяц</a>, 
          <a href="#" rel="<?= date('d.m.Y', strtotime('-3 month'))?>;<?= date('d.m.Y')?>">квартал</a>, 
          <a href="#" rel="<?= date('d.m.Y', strtotime('-1 year'))?>;<?= date('d.m.Y')?>">год</a>
        </div>
        <div class="row">за следующий 
          <a href="#" rel="<?= date('d.m.Y', strtotime('+1 day'))?>">день</a>, 
          <a href="#" rel="<?= date('d.m.Y')?>;<?= date('d.m.Y', strtotime('+1 week'))?>">неделю</a>, 
          <a href="#" rel="<?= date('d.m.Y')?>;<?= date('d.m.Y', strtotime('+1 month'))?>">месяц</a>,           
          <a href="#" rel="<?= date('d.m.Y')?>;<?= date('d.m.Y', strtotime('+1 year'))?>">год</a>
        </div>
      </div>
    </fieldset>
    <fieldset class="checkboxes">
      <div>
        <div class="row">
          <?= $filterForm['usertype1']->render() ?>
          <?= $filterForm['usertype1']->renderLabel() ?>
        </div>
        <div class="row">
          <?= $filterForm['usertype2']->render() ?>
          <?= $filterForm['usertype2']->renderLabel() ?>
        </div>
        <div class="row">
          <?= $filterForm['usertype3']->render() ?>
          <?= $filterForm['usertype3']->renderLabel() ?>
        </div>
      </div>
      <div class="short">
        <div class="row">
          <?= $filterForm['usertype4']->render() ?>
          <?= $filterForm['usertype4']->renderLabel() ?>
        </div>
        <div class="row">
          <?= $filterForm['usertype5']->render() ?>
          <?= $filterForm['usertype5']->renderLabel() ?>
        </div>
      </div>
    </fieldset>
    <div class="buttons">
      <input type="submit" value="Построить диаграмму"/>
    </div>
  </form>

  <div id="bars">

  </div>

  <h2>Сводная таблица</h2>
  <table class="cute-table">
    <thead>
    <tr>
      <td>Дата</td>
      <td>Активные</td>
      <td>Добавленные</td>
      <td>Ушедшие</td>
    </tr>
    </thead>
    <tbody>   
    </tbody>
  </table>
  <p>&nbsp;</p>
</div>