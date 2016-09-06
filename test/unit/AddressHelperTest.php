<?php
require_once dirname(__FILE__).'/../bootstrap/unit.php';
 
$t = new lime_test(5);

$configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'test', true);
new sfDatabaseManager($configuration);

/* Первый тест */
$address_1 = 'Россия, Московская область, Одинцовский район, Одинцово, улица Ленина, 5';
$addr_1 = new AddressHelper($address_1);

$result_1 = array(
        'country' => 'Россия',
        'region' => '50',
	'region_node' => array('164289'),
        'city_region' => '164289',
        'street'      => 'Ленина',
        'address'     => array(
          'house'      => '5',
          'building'   => '',
          'structure'  => null
            )
        );
$res = $addr_1->result;

$t->is_deeply($res, $result_1);

/* Второй тест */
$address_2 = 'Москва, м. Выхино, м. Кузьминки, Шипиловская улица, д. 20а/7';
$addr_2 = new AddressHelper($address_2);

$result_2 = array(
        'region' => '77',
	'region_node' => array('2295', '163199', '163225'),
        'city_region' => '2295',
        'street'      => 'Шипиловская',
        'address'     => array(
          'house'      => '20а',
          'building'   => '7',
          'structure'  => null
            )
        );
$res = $addr_2->result;

$t->is_deeply($res, $result_2);

/* Третий тест */
$address_3 = 'Россия, Московская область, Одинцовский район, поселок Абонентного Ящика 001, улица Ленина, 55а';
$addr_3 = new AddressHelper($address_3);

$result_3 = array(
        'country' => 'Россия',
        'region' => '50',
	'region_node' => array('1072'),
        'city_region' => '76114',
        'street'      => 'Ленина',
        'address'     => array(
          'house'      => '55а',
          'building'   => '',
          'structure'  => null
            )
        );
$res = $addr_3->result;

$t->is_deeply($res, $result_3);

/* Четвертый тест */
$address_4 = 'Россия, Ленинградская область, Всеволожский район, деревня Новое Девяткино';
$addr_4 = new AddressHelper($address_4);

$result_4 = array(
        'country' => 'Россия',
        'region' => '47',
	'region_node' => array('944'),
        'city_region' => '64816',
        'street'      => null,
        'address'     => array(
          'house'      => null,
          'building'   => null,
          'structure'  => null
            )
        );
$res = $addr_4->result;

$t->is_deeply($res, $result_4);

/* Пятый тест — несуществующий хутор */
$address_4 = 'Россия, Ленинградская область, Всеволожский район, хутор Наладар, ул. Нерда, 85';
$addr_4 = new AddressHelper($address_4);

$result_4 = array(
        'country' => 'Россия',
        'region' => '47',
	'region_node' => array('944'),
        'city_region' => 'Наладар',
        'street'      => 'Нерда',
        'address'     => array(
          'house'      => '85',
          'building'   => '',
          'structure'  => null
            )
        );
$res = $addr_4->result;

$t->is_deeply($res, $result_4);
?>
