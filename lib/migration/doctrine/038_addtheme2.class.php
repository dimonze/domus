<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class AddTheme2Migration extends Doctrine_Migration
{
	public function up()
	{
    $themes = array(
      'Коммерческая недвижимость',
      'Загородная недвижимость',
      'Аренда недвижимости',
      'Юридическое сопровождение',
      'Инвестиции в недвижимость',
      'Строительство',
      'Ипотека'
    );
    foreach ($themes as $value){      
      $theme = new Theme();
      $theme->title = $value;
      $theme->save();
    }
	}

	public function down()
	{	}
}