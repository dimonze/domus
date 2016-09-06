<?php
/**
 * Fetch lots from http://www.mirkvartir.ru/
 *
 * @package    domus
 * @subpackage task
 */
class fetchMirkvartirTask extends sfBaseTask
{
  protected
    $config = null;

  private
    $price1 = null,
    $price2 = null,

    $settings = array(
      'apartament-sale' => array(
        0 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%9C%D0%BE%D1%81%D0%BA%D0%B2%D0%B0/?search=rooms&priceFrom=900&priceTo=1700&pp=100&',
          'data'  => array('region_id' => 77, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in msk'
        ),
        1 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%9C%D0%BE%D1%81%D0%BA%D0%B2%D0%B0/?search=rooms&priceFrom=1701&priceTo=1900&pp=100&',
          'data'  => array('region_id' => 77, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in msk'
        ),
        2 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%9C%D0%BE%D1%81%D0%BA%D0%B2%D0%B0/?search=rooms&priceFrom=1901&priceTo=2000&pp=100&',
          'data'  => array('region_id' => 77, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in msk'
        ),
        3 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%9C%D0%BE%D1%81%D0%BA%D0%B2%D0%B0/?search=rooms&priceFrom=2001&priceTo=2150&pp=100&',
          'data'  => array('region_id' => 77, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in msk'
        ),
        4 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%9C%D0%BE%D1%81%D0%BA%D0%B2%D0%B0/?search=rooms&priceFrom=2151&priceTo=2250&pp=100&',
          'data'  => array('region_id' => 77, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in msk'
        ),
        5 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%9C%D0%BE%D1%81%D0%BA%D0%B2%D0%B0/?search=rooms&priceFrom=2251&priceTo=2350&pp=100&',
          'data'  => array('region_id' => 77, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in msk'
        ),
        6 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%9C%D0%BE%D1%81%D0%BA%D0%B2%D0%B0/?search=rooms&priceFrom=2351&priceTo=2500&pp=100&',
          'data'  => array('region_id' => 77, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in msk'
        ),
        7 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%9C%D0%BE%D1%81%D0%BA%D0%B2%D0%B0/?search=rooms&priceFrom=2501&priceTo=2700&pp=100&',
          'data'  => array('region_id' => 77, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in msk'
        ),
        8 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%9C%D0%BE%D1%81%D0%BA%D0%B2%D0%B0/?search=rooms&priceFrom=2701&priceTo=3000&pp=100&',
          'data'  => array('region_id' => 77, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in msk'
        ),
        9 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%9C%D0%BE%D1%81%D0%BA%D0%B2%D0%B0/?search=rooms&priceFrom=3001&priceTo=4000&pp=100&',
          'data'  => array('region_id' => 77, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in msk'
        ),
        10 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%9C%D0%BE%D1%81%D0%BA%D0%B2%D0%B0/?search=rooms&priceFrom=4001&pp=100&',
          'data'  => array('region_id' => 77, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in msk'
        ),
        11 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%9C%D0%BE%D1%81%D0%BA%D0%BE%D0%B2%D1%81%D0%BA%D0%B0%D1%8F+%D0%BE%D0%B1%D0%BB%D0%B0%D1%81%D1%82%D1%8C/?search=rooms&priceFrom=300&priceTo=750&pp=100&',
          'data'  => array('region_id' => 50, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in mo'
        ),
        12 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%9C%D0%BE%D1%81%D0%BA%D0%BE%D0%B2%D1%81%D0%BA%D0%B0%D1%8F+%D0%BE%D0%B1%D0%BB%D0%B0%D1%81%D1%82%D1%8C/?search=rooms&priceFrom=751&priceTo=850&pp=100&',
          'data'  => array('region_id' => 50, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in mo'
        ),
        13 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%9C%D0%BE%D1%81%D0%BA%D0%BE%D0%B2%D1%81%D0%BA%D0%B0%D1%8F+%D0%BE%D0%B1%D0%BB%D0%B0%D1%81%D1%82%D1%8C/?search=rooms&priceFrom=851&priceTo=900&pp=100&',
          'data'  => array('region_id' => 50, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in mo'
        ),
        14 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%9C%D0%BE%D1%81%D0%BA%D0%BE%D0%B2%D1%81%D0%BA%D0%B0%D1%8F+%D0%BE%D0%B1%D0%BB%D0%B0%D1%81%D1%82%D1%8C/?search=rooms&priceFrom=901&priceTo=1000&pp=100&',
          'data'  => array('region_id' => 50, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in mo'
        ),
        15 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%9C%D0%BE%D1%81%D0%BA%D0%BE%D0%B2%D1%81%D0%BA%D0%B0%D1%8F+%D0%BE%D0%B1%D0%BB%D0%B0%D1%81%D1%82%D1%8C/?search=rooms&priceFrom=1001&priceTo=1150&pp=100&',
          'data'  => array('region_id' => 50, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in mo'
        ),
        16 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%9C%D0%BE%D1%81%D0%BA%D0%BE%D0%B2%D1%81%D0%BA%D0%B0%D1%8F+%D0%BE%D0%B1%D0%BB%D0%B0%D1%81%D1%82%D1%8C/?search=rooms&priceFrom=1151&priceTo=1250&pp=100&',
          'data'  => array('region_id' => 50, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in mo'
        ),
        17 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%9C%D0%BE%D1%81%D0%BA%D0%BE%D0%B2%D1%81%D0%BA%D0%B0%D1%8F+%D0%BE%D0%B1%D0%BB%D0%B0%D1%81%D1%82%D1%8C/?search=rooms&priceFrom=1251&priceTo=1350&pp=100&',
          'data'  => array('region_id' => 50, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in mo'
        ),
        18 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%9C%D0%BE%D1%81%D0%BA%D0%BE%D0%B2%D1%81%D0%BA%D0%B0%D1%8F+%D0%BE%D0%B1%D0%BB%D0%B0%D1%81%D1%82%D1%8C/?search=rooms&priceFrom=1351&priceTo=1450&pp=100&',
          'data'  => array('region_id' => 50, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in mo'
        ),
        19 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%9C%D0%BE%D1%81%D0%BA%D0%BE%D0%B2%D1%81%D0%BA%D0%B0%D1%8F+%D0%BE%D0%B1%D0%BB%D0%B0%D1%81%D1%82%D1%8C/?search=rooms&priceFrom=1451&priceTo=1600&pp=100&',
          'data'  => array('region_id' => 50, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in mo'
        ),
        20 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%9C%D0%BE%D1%81%D0%BA%D0%BE%D0%B2%D1%81%D0%BA%D0%B0%D1%8F+%D0%BE%D0%B1%D0%BB%D0%B0%D1%81%D1%82%D1%8C/?search=rooms&priceFrom=1601&priceTo=2000&pp=100&',
          'data'  => array('region_id' => 50, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in mo'
        ),
        21 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%9C%D0%BE%D1%81%D0%BA%D0%BE%D0%B2%D1%81%D0%BA%D0%B0%D1%8F+%D0%BE%D0%B1%D0%BB%D0%B0%D1%81%D1%82%D1%8C/?search=rooms&priceFrom=2001&pp=100&',
          'data'  => array('region_id' => 50, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in mo'
        ),
        22 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%9D%D0%B8%D0%B6%D0%B5%D0%B3%D0%BE%D1%80%D0%BE%D0%B4%D1%81%D0%BA%D0%B0%D1%8F+%D0%BE%D0%B1%D0%BB%D0%B0%D1%81%D1%82%D1%8C/?search=rooms&priceFrom=300&priceTo=600&pp=100&',
          'data'  => array('region_id' => 52, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in nn'
        ),
        23 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%9D%D0%B8%D0%B6%D0%B5%D0%B3%D0%BE%D1%80%D0%BE%D0%B4%D1%81%D0%BA%D0%B0%D1%8F+%D0%BE%D0%B1%D0%BB%D0%B0%D1%81%D1%82%D1%8C/?search=rooms&priceFrom=601&priceTo=700&pp=100&',
          'data'  => array('region_id' => 52, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in nn'
        ),
        24 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%9D%D0%B8%D0%B6%D0%B5%D0%B3%D0%BE%D1%80%D0%BE%D0%B4%D1%81%D0%BA%D0%B0%D1%8F+%D0%BE%D0%B1%D0%BB%D0%B0%D1%81%D1%82%D1%8C/?search=rooms&priceFrom=701&priceTo=800&pp=100&',
          'data'  => array('region_id' => 52, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in nn'
        ),
        25 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%9D%D0%B8%D0%B6%D0%B5%D0%B3%D0%BE%D1%80%D0%BE%D0%B4%D1%81%D0%BA%D0%B0%D1%8F+%D0%BE%D0%B1%D0%BB%D0%B0%D1%81%D1%82%D1%8C/?search=rooms&priceFrom=801&pp=100&',
          'data'  => array('region_id' => 52, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in nn'
        ),
        26 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%A1%D0%B0%D0%BD%D0%BA%D1%82-%D0%9F%D0%B5%D1%82%D0%B5%D1%80%D0%B1%D1%83%D1%80%D0%B3/?search=rooms&priceFrom=900&priceTo=1700&pp=100&',
          'data'  => array('region_id' => 78, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in spb'
        ),
        27 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%A1%D0%B0%D0%BD%D0%BA%D1%82-%D0%9F%D0%B5%D1%82%D0%B5%D1%80%D0%B1%D1%83%D1%80%D0%B3/?search=rooms&priceFrom=1701&priceTo=1900&pp=100&',
          'data'  => array('region_id' => 78, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in spb'
        ),
        28 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%A1%D0%B0%D0%BD%D0%BA%D1%82-%D0%9F%D0%B5%D1%82%D0%B5%D1%80%D0%B1%D1%83%D1%80%D0%B3/?search=rooms&priceFrom=1901&priceTo=2000&pp=100&',
          'data'  => array('region_id' => 78, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in spb'
        ),
        29 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%A1%D0%B0%D0%BD%D0%BA%D1%82-%D0%9F%D0%B5%D1%82%D0%B5%D1%80%D0%B1%D1%83%D1%80%D0%B3/?search=rooms&priceFrom=2001&priceTo=2150&pp=100&',
          'data'  => array('region_id' => 78, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in spb'
        ),
        30 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%A1%D0%B0%D0%BD%D0%BA%D1%82-%D0%9F%D0%B5%D1%82%D0%B5%D1%80%D0%B1%D1%83%D1%80%D0%B3/?search=rooms&priceFrom=2151&priceTo=2250&pp=100&',
          'data'  => array('region_id' => 78, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in spb'
        ),
        31 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%A1%D0%B0%D0%BD%D0%BA%D1%82-%D0%9F%D0%B5%D1%82%D0%B5%D1%80%D0%B1%D1%83%D1%80%D0%B3/?search=rooms&priceFrom=2251&priceTo=2350&pp=100&',
          'data'  => array('region_id' => 78, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in spb'
        ),
        32 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%A1%D0%B0%D0%BD%D0%BA%D1%82-%D0%9F%D0%B5%D1%82%D0%B5%D1%80%D0%B1%D1%83%D1%80%D0%B3/?search=rooms&priceFrom=2351&priceTo=2500&pp=100&',
          'data'  => array('region_id' => 78, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in spb'
        ),
        33 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%A1%D0%B0%D0%BD%D0%BA%D1%82-%D0%9F%D0%B5%D1%82%D0%B5%D1%80%D0%B1%D1%83%D1%80%D0%B3/?search=rooms&priceFrom=2501&priceTo=2700&pp=100&',
          'data'  => array('region_id' => 78, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in spb'
        ),
        34 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%A1%D0%B0%D0%BD%D0%BA%D1%82-%D0%9F%D0%B5%D1%82%D0%B5%D1%80%D0%B1%D1%83%D1%80%D0%B3/?search=rooms&priceFrom=2701&priceTo=3000&pp=100&',
          'data'  => array('region_id' => 78, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in spb'
        ),
        35 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%A1%D0%B0%D0%BD%D0%BA%D1%82-%D0%9F%D0%B5%D1%82%D0%B5%D1%80%D0%B1%D1%83%D1%80%D0%B3/?search=rooms&priceFrom=3001&priceTo=4000&pp=100&',
          'data'  => array('region_id' => 78, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in spb'
        ),
        36 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%A1%D0%B0%D0%BD%D0%BA%D1%82-%D0%9F%D0%B5%D1%82%D0%B5%D1%80%D0%B1%D1%83%D1%80%D0%B3/?search=rooms&priceFrom=4001&pp=100&',
          'data'  => array('region_id' => 78, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in spb'
        ),
        37 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%9B%D0%B5%D0%BD%D0%B8%D0%BD%D0%B3%D1%80%D0%B0%D0%B4%D1%81%D0%BA%D0%B0%D1%8F+%D0%BE%D0%B1%D0%BB%D0%B0%D1%81%D1%82%D1%8C/?search=rooms&priceFrom=300&priceTo=750&pp=100&',
          'data'  => array('region_id' => 47, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in lo'
        ),
        38 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%9B%D0%B5%D0%BD%D0%B8%D0%BD%D0%B3%D1%80%D0%B0%D0%B4%D1%81%D0%BA%D0%B0%D1%8F+%D0%BE%D0%B1%D0%BB%D0%B0%D1%81%D1%82%D1%8C/?search=rooms&priceFrom=751&priceTo=850&pp=100&',
          'data'  => array('region_id' => 47, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in lo'
        ),
        39 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%9B%D0%B5%D0%BD%D0%B8%D0%BD%D0%B3%D1%80%D0%B0%D0%B4%D1%81%D0%BA%D0%B0%D1%8F+%D0%BE%D0%B1%D0%BB%D0%B0%D1%81%D1%82%D1%8C/?search=rooms&priceFrom=851&priceTo=900&pp=100&',
          'data'  => array('region_id' => 47, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in lo'
        ),
        40 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%9B%D0%B5%D0%BD%D0%B8%D0%BD%D0%B3%D1%80%D0%B0%D0%B4%D1%81%D0%BA%D0%B0%D1%8F+%D0%BE%D0%B1%D0%BB%D0%B0%D1%81%D1%82%D1%8C/?search=rooms&priceFrom=901&priceTo=1000&pp=100&',
          'data'  => array('region_id' => 47, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in lo'
        ),
        41 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%9B%D0%B5%D0%BD%D0%B8%D0%BD%D0%B3%D1%80%D0%B0%D0%B4%D1%81%D0%BA%D0%B0%D1%8F+%D0%BE%D0%B1%D0%BB%D0%B0%D1%81%D1%82%D1%8C/?search=rooms&priceFrom=1001&priceTo=1150&pp=100&',
          'data'  => array('region_id' => 47, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in lo'
        ),
        42 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%9B%D0%B5%D0%BD%D0%B8%D0%BD%D0%B3%D1%80%D0%B0%D0%B4%D1%81%D0%BA%D0%B0%D1%8F+%D0%BE%D0%B1%D0%BB%D0%B0%D1%81%D1%82%D1%8C/?search=rooms&priceFrom=1151&priceTo=1250&pp=100&',
          'data'  => array('region_id' => 47, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in lo'
        ),
        43 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%9B%D0%B5%D0%BD%D0%B8%D0%BD%D0%B3%D1%80%D0%B0%D0%B4%D1%81%D0%BA%D0%B0%D1%8F+%D0%BE%D0%B1%D0%BB%D0%B0%D1%81%D1%82%D1%8C/?search=rooms&priceFrom=1251&priceTo=1350&pp=100&',
          'data'  => array('region_id' => 47, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in lo'
        ),
        44 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%9B%D0%B5%D0%BD%D0%B8%D0%BD%D0%B3%D1%80%D0%B0%D0%B4%D1%81%D0%BA%D0%B0%D1%8F+%D0%BE%D0%B1%D0%BB%D0%B0%D1%81%D1%82%D1%8C/?search=rooms&priceFrom=1351&priceTo=1450&pp=100&',
          'data'  => array('region_id' => 47, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in lo'
        ),
        45 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%9B%D0%B5%D0%BD%D0%B8%D0%BD%D0%B3%D1%80%D0%B0%D0%B4%D1%81%D0%BA%D0%B0%D1%8F+%D0%BE%D0%B1%D0%BB%D0%B0%D1%81%D1%82%D1%8C/?search=rooms&priceFrom=1451&priceTo=1600&pp=100&',
          'data'  => array('region_id' => 47, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in lo'
        ),
        46 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%9B%D0%B5%D0%BD%D0%B8%D0%BD%D0%B3%D1%80%D0%B0%D0%B4%D1%81%D0%BA%D0%B0%D1%8F+%D0%BE%D0%B1%D0%BB%D0%B0%D1%81%D1%82%D1%8C/?search=rooms&priceFrom=1601&priceTo=2000&pp=100&',
          'data'  => array('region_id' => 47, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in lo'
        ),
        47 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%9B%D0%B5%D0%BD%D0%B8%D0%BD%D0%B3%D1%80%D0%B0%D0%B4%D1%81%D0%BA%D0%B0%D1%8F+%D0%BE%D0%B1%D0%BB%D0%B0%D1%81%D1%82%D1%8C/?search=rooms&priceFrom=2001&pp=100&',
          'data'  => array('region_id' => 47, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in lo'
        ),
        48 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%9A%D1%80%D0%B0%D1%81%D0%BD%D0%BE%D0%B4%D0%B0%D1%80%D1%81%D0%BA%D0%B8%D0%B9+%D0%BA%D1%80%D0%B0%D0%B9/?pp=100&',
          'data'  => array('region_id' => 23, 'user_id' => 4, 'type' => 'apartament-sale'),
          'note'  => 'sale flats in kras'
        ),
        49 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%9A%D1%80%D0%B0%D1%81%D0%BD%D0%BE%D0%B4%D0%B0%D1%80%D1%81%D0%BA%D0%B8%D0%B9+%D0%BA%D1%80%D0%B0%D0%B9/?search=rooms&pp=100&',
          'data'  => array('region_id' => 23, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in kras'
        ),
        50 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%92%D0%BE%D0%BB%D0%B3%D0%BE%D0%B3%D1%80%D0%B0%D0%B4%D1%81%D0%BA%D0%B0%D1%8F+%D0%BE%D0%B1%D0%BB%D0%B0%D1%81%D1%82%D1%8C/?pp=100&',
          'data'  => array('region_id' => 34, 'user_id' => 4, 'type' => 'apartament-sale'),
          'note'  => 'sale flats in volg'
        ),
        51 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%92%D0%BE%D0%BB%D0%B3%D0%BE%D0%B3%D1%80%D0%B0%D0%B4%D1%81%D0%BA%D0%B0%D1%8F+%D0%BE%D0%B1%D0%BB%D0%B0%D1%81%D1%82%D1%8C/?search=rooms&pp=100&',
          'data'  => array('region_id' => 34, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in volg'
        ),
        52 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%A0%D0%BE%D1%81%D1%82%D0%BE%D0%B2%D1%81%D0%BA%D0%B0%D1%8F+%D0%BE%D0%B1%D0%BB%D0%B0%D1%81%D1%82%D1%8C/?search=rooms&pp=100&',
          'data'  => array('region_id' => 61, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in rost'
        ),
        53 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%9A%D0%B0%D0%BB%D0%B8%D0%BD%D0%B8%D0%BD%D0%B3%D1%80%D0%B0%D0%B4%D1%81%D0%BA%D0%B0%D1%8F+%D0%BE%D0%B1%D0%BB%D0%B0%D1%81%D1%82%D1%8C/?pp=100&',
          'data'  => array('region_id' => 39, 'user_id' => 4, 'type' => 'apartament-sale'),
          'note'  => 'sale flats in kgd'
        ),
        54 => array(
          'url'   => 'http://www.mirkvartir.ru/%D0%9A%D0%B0%D0%BB%D0%B8%D0%BD%D0%B8%D0%BD%D0%B3%D1%80%D0%B0%D0%B4%D1%81%D0%BA%D0%B0%D1%8F+%D0%BE%D0%B1%D0%BB%D0%B0%D1%81%D1%82%D1%8C/?search=rooms&pp=100&',
          'data'  => array('region_id' => 39, 'user_id' => 4, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'sale rooms in kgd'
        ),
      ),
      'apartament-rent' => array(
        0 => array(
          'url'   => 'http://arenda.mirkvartir.ru/%D0%9C%D0%BE%D1%81%D0%BA%D0%B2%D0%B0/?search=rooms&priceFrom=5000&priceTo=10000&pp=100&',
          'data'  => array('region_id' => 77, 'user_id' => 4, 'type' => 'apartament-rent', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'rent rooms in msk',
        ),
        1 => array(
          'url'   => 'http://arenda.mirkvartir.ru/%D0%9C%D0%BE%D1%81%D0%BA%D0%B2%D0%B0/?search=rooms&priceFrom=10001&priceTo=13000&pp=100&',
          'data'  => array('region_id' => 77, 'user_id' => 4, 'type' => 'apartament-rent', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'rent rooms in msk',
        ),
        2 => array(
          'url'   => 'http://arenda.mirkvartir.ru/%D0%9C%D0%BE%D1%81%D0%BA%D0%B2%D0%B0/?search=rooms&priceFrom=13001&priceTo=15000&pp=100&',
          'data'  => array('region_id' => 77, 'user_id' => 4, 'type' => 'apartament-rent', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'rent rooms in msk',
        ),
        3 => array(
          'url'   => 'http://arenda.mirkvartir.ru/%D0%9C%D0%BE%D1%81%D0%BA%D0%B2%D0%B0/?search=rooms&priceFrom=15001&pp=100&',
          'data'  => array('region_id' => 77, 'user_id' => 4, 'type' => 'apartament-rent', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'rent rooms in msk',
        ),
        4 => array(
          'url'   => 'http://arenda.mirkvartir.ru/%D0%A1%D0%B0%D0%BD%D0%BA%D1%82-%D0%9F%D0%B5%D1%82%D0%B5%D1%80%D0%B1%D1%83%D1%80%D0%B3/?search=rooms&priceFrom=5000&priceTo=10000&pp=100&',
          'data'  => array('region_id' => 78, 'user_id' => 4, 'type' => 'apartament-rent', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'rent rooms in spb',
        ),
        5 => array(
          'url'   => 'http://arenda.mirkvartir.ru/%D0%A1%D0%B0%D0%BD%D0%BA%D1%82-%D0%9F%D0%B5%D1%82%D0%B5%D1%80%D0%B1%D1%83%D1%80%D0%B3/?search=rooms&priceFrom=10001&priceTo=13000&pp=100&',
          'data'  => array('region_id' => 78, 'user_id' => 4, 'type' => 'apartament-rent', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'rent rooms in spb',
        ),
        6 => array(
          'url'   => 'http://arenda.mirkvartir.ru/%D0%A1%D0%B0%D0%BD%D0%BA%D1%82-%D0%9F%D0%B5%D1%82%D0%B5%D1%80%D0%B1%D1%83%D1%80%D0%B3/?search=rooms&priceFrom=13001&priceTo=15000&pp=100&',
          'data'  => array('region_id' => 78, 'user_id' => 4, 'type' => 'apartament-rent', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'rent rooms in spb',
        ),
        7 => array(
          'url'   => 'http://arenda.mirkvartir.ru/%D0%A1%D0%B0%D0%BD%D0%BA%D1%82-%D0%9F%D0%B5%D1%82%D0%B5%D1%80%D0%B1%D1%83%D1%80%D0%B3/?search=rooms&priceFrom=15001&pp=100&',
          'data'  => array('region_id' => 78, 'user_id' => 4, 'type' => 'apartament-rent', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'rent rooms in spb',
        ),
        8 => array(
          'url'   => 'http://arenda.mirkvartir.ru/%D0%A0%D0%BE%D1%81%D1%82%D0%BE%D0%B2%D1%81%D0%BA%D0%B0%D1%8F+%D0%BE%D0%B1%D0%BB%D0%B0%D1%81%D1%82%D1%8C/?search=rooms&pp=100&',
          'data'  => array('region_id' => 61, 'user_id' => 4, 'type' => 'apartament-rent', 'params' => array('Тип предложения' => 'комната')),
          'note'  => 'rent rooms in rost',
        ),
        9 => array(
          'url'   => 'http://arenda.mirkvartir.ru/%D0%9A%D1%80%D0%B0%D1%81%D0%BD%D0%BE%D0%B4%D0%B0%D1%80%D1%81%D0%BA%D0%B8%D0%B9+%D0%BA%D1%80%D0%B0%D0%B9/?pp=100&',
          'data'  => array('region_id' => 23, 'user_id' => 4, 'type' => 'apartament-rent'),
          'note'  => 'rent flats in kras',
        ),
        10 => array(
          'url'   => 'http://arenda.mirkvartir.ru/%D0%9A%D0%B0%D0%BB%D0%B8%D0%BD%D0%B8%D0%BD%D0%B3%D1%80%D0%B0%D0%B4%D1%81%D0%BA%D0%B0%D1%8F+%D0%BE%D0%B1%D0%BB%D0%B0%D1%81%D1%82%D1%8C/?pp=100&',
          'data'  => array('region_id' => 39, 'user_id' => 4, 'type' => 'apartament-rent'),
          'note'  => 'rent flats in kgd',
        ),
        11 => array(
          'url'   => 'http://arenda.mirkvartir.ru/%D0%92%D0%BE%D0%BB%D0%B3%D0%BE%D0%B3%D1%80%D0%B0%D0%B4%D1%81%D0%BA%D0%B0%D1%8F+%D0%BE%D0%B1%D0%BB%D0%B0%D1%81%D1%82%D1%8C/?pp=100&',
          'data'  => array('region_id' => 34, 'user_id' => 4, 'type' => 'apartament-rent'),
          'note'  => 'rent flats in volg',
        ),
      ),
    );


  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->namespace = 'fetch';
    $this->name = 'mirkvartir';
    $this->briefDescription = null;
    $this->detailedDescription = null;

    $this->addOption('type', null, sfCommandOption::PARAMETER_REQUIRED, 'like apartament-sale', 'apartament-sale');
    $this->addOption('worker', null, sfCommandOption::PARAMETER_REQUIRED, 'worker #', null);
    $this->addOption('limit', null, sfCommandOption::PARAMETER_OPTIONAL, 'limit lots count', 150);
    $this->addOption('region_id', null, sfCommandOption::PARAMETER_OPTIONAL, 'region number or 0', 0);
    $this->addOption('price1', null, sfCommandOption::PARAMETER_OPTIONAL, 'start price', 0);
    $this->addOption('price2', null, sfCommandOption::PARAMETER_OPTIONAL, 'end price', 0);
  }


  protected function generateLink($options, $worker)
  {
    if ($options['type'] == 'apartament-sale') {
      if (in_array($options['region_id'], array(23,34,39)))
        return $this->getLinkFromSettings($options, $worker);

      $prefix = 'www';
      $increment = $options['region_id'] == 77 ? 200000 : 500000;
      $max_price = 20000000;
      $division = 1000;
    } elseif ($options['type'] == 'apartament-rent') {
      if (in_array($options['region_id'], array(23,34,39)))
        return $this->getLinkFromSettings($options, $worker);

      $prefix = 'arenda';
      $max_price = 100000;
      $increment = $options['region_id'] == 77 ? 5000 : 10000;
      $division = 1;
    }

    switch ($options['region_id']) {
      case 77:
        $region = 'Москва';
        if ($options['type'] == 'apartament-sale') {
          $increment = 200000;
        } elseif ($options['type'] == 'apartament-rent') {
          $increment = 5000;
        }
        break;

      case 50:
        $region = 'Московская+область';
        break;

      case 78:
        $region = 'Санкт-Петербург';
        if ($options['type'] == 'apartament-sale') {
          $increment = 200000;
        } elseif ($options['type'] == 'apartament-rent') {
          $increment = 5000;
        }
        break;

      case 47:
        $region = 'Ленинградская+область';
        break;

      case 52:
        $region = 'Нижегородская+область';
        if ($options['type'] == 'apartament-rent') $max_price = 50000;
        break;

      case 39:
        $region = 'Калининградская+область';
        if ($options['type'] == 'apartament-rent') $max_price = 50000;
        break;

      case 23:
        $region = 'Краснодарский+край';
        if ($options['type'] == 'apartament-rent') $max_price = 50000;
        break;

      case 34:
        $region = 'Волгоградская+область';
        if ($options['type'] == 'apartament-rent') $max_price = 50000;
        break;

      case 61:
        $region = 'Ростовская+область';
        if ($options['type'] == 'apartament-rent') $max_price = 50000;
        break;
    }

    if (!isset($region, $increment, $max_price, $prefix)) return false;

    $price1 = $worker*$increment;
    $price2 = $price1+($increment-1);

    $minprice = ParseTools::getMinPrice($options['type'], $options['region_id']);
    if ($price2 < $minprice) {
      return false;
    } elseif ($price1 < $minprice) {
      $price1 = $minprice;
    } elseif ($price1 > $max_price) {
      return 8;
    }

    if (!is_null($this->price1)) {
      if ($price1 < $this->price1 || $price1 > $this->price2) return false;
    }

    $link = sprintf('http://%s.mirkvartir.ru/%s/?priceFrom=%d&priceTo=%d&areaFrom=%d&pp=100&',
                    $prefix, urlencode($region), $price1/$division, $price2/$division, ParseTools::MIN_S);

    $settings = array(
      'url'   => $link,
      'data'  => array('region_id' => $options['region_id'], 'user_id' => 4, 'type' => $options['type']),
      'limit' => $options['limit'],
    );

    return $settings;
  }

  protected function getLinkFromSettings($options, $worker)
  {
    if (!isset($this->settings[$options['type']][$worker-1])) return 8;
    if ($this->settings[$options['type']][$worker-1]['data']['region_id'] != $options['region_id']) return false;

    $settings = $this->settings[$options['type']][$worker-1];
    $settings = array_merge(array('limit' => $options['limit']), $settings);
    return $settings;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    if (empty($options['type'])) throw new Exception('Parameter "type" is required');

    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);

    ini_set('memory_limit', '768M');
    $log_options = array(
      'resource'  => 'Mirkvartir',
      'type'      => $options['type'],
      'limit'     => $options['limit'],
    );

    if ($options['region_id'] !== 0) {
       if (!empty($options['price1']) && ctype_digit($options['price1'])) {
        $this->price1 = intval($options['price1']);
        $this->price2 = !empty($options['price2']) && ctype_digit($options['price2']) ? intval($options['price2']) : ($this->price1+5000000);
      }

      for ($i=1; $i<=100; $i++) {
        if (!$settings = $this->generateLink($options, $i)) continue;
        if ($settings == 8) return;

        $this->runFetcher($log_options, $settings);
      }
    }
    else {
      if (empty($options['worker'])) throw new Exception('Parameter "worker" is required');

      if (!empty($this->settings[$options['type']][$options['worker']-1])) {
        $settings = $this->settings[$options['type']][$options['worker']-1];
        $settings = array_merge(array('limit' => $options['limit']), $settings);

        $this->runFetcher($log_options, $settings);
      }
    }
  }


  private function runFetcher($log_options, $settings)
  {
    $log_options['page'] = $settings['url'];
    ParseLogger::initLogger($log_options);

    $fetcher = new Fetcher_Mirkvartir($settings);
    $fetcher->get();

    ParseLogger::writeFinish($fetcher->lots_parsed, $fetcher->lots_fetched);

    unset($fetcher);
  }
}
