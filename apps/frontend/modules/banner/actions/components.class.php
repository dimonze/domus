<?php

/**
 * banner components.
 *
 * @package    domus
 * @subpackage banner
 * @author     Garin Studio
 * @version    SVN: $Id: components.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class bannerComponents extends sfComponents
{
  public function executeInvisible() {
    $host = strpos($this->getRequest()->getHost(), 'server.garin.su') !== false
      ? 'localhost'
      : '192.168.1.2';
    $this->_conn = new PDO('mysql:dbname=openx;host=' . $host, 'openx', 'N4rbet74btuFgrt28b5bgI',
      array(
        PDO::MYSQL_ATTR_INIT_COMMAND => 'set names utf8',
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      )
    );
    $query = $this->_conn->prepare('
      SELECT ass.ad_id, ass.zone_id, ban.url, ban.htmltemplate, ban.compiledlimitation as limits
      FROM `ox_ad_zone_assoc` as ass LEFT JOIN `ox_banners` ban ON ass.ad_id = ban.bannerid
      WHERE ass.zone_id IN (
        SELECT z.zoneid FROM `ox_zones` as z WHERE z.affiliateid = 1
      ) AND ban.status = 0
      AND ban.htmltemplate NOT LIKE  "%put_banner%"
      GROUP BY ass.ad_id ORDER BY url DESC');
    $query->execute();

    $links = array();
    $now = date('Ymd');
    foreach(OpenX::getBannersForInvisibleList() as $row) {
      if(!empty($row['htmltemplate'])) {
        preg_match('#href="(.*?)"#', $row['htmltemplate'], $url);
        if(empty($url[1])) {
          continue;
        }
        $url = $url[1];
      }
      else{
        $url = $row['url'];
      }
      $pattern = "#MAX_checkTime_Date\('(\d{4}\d{2}\d{2})@.*?', '(<=|>=)'\)#";
      preg_match_all($pattern, $row['limits'], $matches, PREG_SET_ORDER);


      if(!empty($matches)){
        $problems = false;
        foreach($matches as $match){
          $time = $match[1];
          $sign = $match[2];
          if(!eval("return $now $sign $time;")){
            $problems = true;
            continue;
          }
        }
        if($problems){
          // Not for production
          // echo sprintf("<!-- zone_id=%s, banner_id=%s -->\n", $row['zone_id'], $row['ad_id']);
          continue;
        }
      }

      $data = array(
        'oaparams'  => 2,
        'bannerid'  => $row['ad_id'],
        'zoneid'    => $row['zone_id'],
        'oadest'    => $url
      );

      $links[] = 'http://media.mesto.ru/www/delivery/ck.php?' . http_build_query($data, null, '__');
    }
    $this->links = array_unique($links);
  }

  public function executeNbOneSpecial()
  {
    if (empty($this->banner_id)) {
      return false;
    }
    $this->banner = OpenX::generateBannerHTML($this->banner_id);
  }
}
