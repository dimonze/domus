<form action="<?= url_for('user/register') ?>" method="post" class="ajax-validate register-form">
  <fieldset>
    <legend><span class="prependClose">Регистрация</span></legend>

    <div class="user-type">
      <label>Вы регистрируетесь как</label>
      <ul>
        <?php $user_types = User::$types ?>
        <?php unset($user_types['source']) ?>
        <?php foreach ($user_types as $key => $name): ?>
          <li>
            <label class="popupLabel radio">
              <?php if ($key == 'company'): ?>
                <?= radiobutton_tag('user[type]', $key, false, 'show=.special-' . $key . ' hide=.special checked=checked') ?>
              <?php else: ?>
                <?= radiobutton_tag('user[type]', $key, false, 'show=.special-' . $key . ' hide=.special') ?>
              <?php endif ?>
              <?php if ($key == 'company'): ?>
                <span class="selected"><?= $name ?></span>
              <?php else: ?>
                <span><?= $name ?></span>
              <?php endif ?>
            </label>
          </li>
        <?php endforeach ?>
      </ul>
        <span class="desc" name="company_info" style="display:none;"><var class="t"></var><var class="b"></var><span>
          <em><strong>Вы</strong> &mdash; представитель агентства недвижимости или строительной компании.</em>
          <em><strong>Вы можете:</strong> разместить информацию о компании, добавить неограниченное количество объявлений, пригласить на сайт сотрудников компании, воспользоваться дополнительными рекламными возможностями.</em>
        </span></span>
        <span class="desc" name="employee_info" style="display:none;"><var class="t"></var><var class="b"></var><span>
          <em><strong>Вы</strong> &mdash; сотрудник компании и получили письмо с кодом от представителя Вашей фирмы, который зарегистрировался как &quot;Компания&quot;.</em>
          <em><strong>Вы можете:</strong> разместить информацию о себе и добавить неограниченное количество объявлений от имени компании.</em>
        </span></span>
        <span class="desc" name="realtor_info" style="display:none;"><var class="t"></var><var class="b"></var><span>
          <em><strong>Вы</strong> &mdash; человек, самостоятельно оказывающий услуги посредника в сфере недвижимости. </em>
          <em><strong>Вы можете:</strong> разместить информацию о себе и добавить объявления.</em>
        </span></span>
        <span class="desc" name="owner_info" style="display:none;"><var class="t"></var><var class="b"></var><span>
          <em><strong>Вы</strong> &mdash; владелец объектов недвижимости. </em>
          <em><strong>Вы можете:</strong> разместить информацию о себе и добавить объявления.</em>
        </span></span>
    </div>

    <div class="special special-company">
      <div>
        <label for="company_name">Название организации</label>
        <?= $form['company_name'] ?>
      </div>
    </div>

    <div class="special special-employee">
      <div>
        <label for="invite">Код регистрации</label>
        <?= $form['invite'] ?>
        <p>выдается администратором агентства</p>
      </div>
    </div>

    <div>
      <div>
        <label class="special special-employee special-realtor  special-owner" style="display: block;">
          Имя, фамилия
        </label>
        <label class="special special-company">Контактное лицо</label>
        <?= $form['name'] ?>
      </div>
    </div>

    <div>
      <div>
        <label for="codeCity">Телефон</label>
        <?= $form['country_code'] ?>
        <?= $form['area_code'] ?>
        <?= $form['phone'] ?>
      </div>
    </div>

    <div>
      <div>
        <label for="email">E-mail</label>
        <?= $form['email'] ?>
      </div>
    </div>
    <div>
      <div>
        <label>Пароль<br />
        <span>(не менее 6-ти символов)</span></label>
        <?= $form['password'] ?>
      </div>
    </div>

    <div style="padding-top: 0;">
      <div>
        <label for="password_again">Подтвердите пароль</label>
        <?= $form['password_again'] ?>
      </div>
    </div>

    <div>
      <div>
        <label for="captcha">Все символы с картинки</label>
        <?= preg_replace('/onclick=".+"/iU', '', (string) $form['captcha']) ?>
        <p><a href="#" class="update_captcha">обновить картинку</a></p>
      </div>
    </div>

    <div>
      <div>
        <p style="padding-left: 5px !important;">Все поля обязательны для заполнения</p>
      </div>
    </div>
    

   <div style="height: 50px;">
      <!--<?= link_to('Авторизироваться', 'user/login?forward=' . $sf_params->get('forward'),
                  'class=popup authlink rel=auth') ?>-->
      <input class="popupSubmit send" type="submit" value="Зарегистрироваться" />
    </div>
  </fieldset>
</form>
