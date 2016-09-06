<?php
class OpenX {
  const NAYDIDOM_HIT_BANNER_ID = 1004;

  private static $_conn = null;

  protected static function getConnection() {
    if(null === self::$_conn) {
      $host = strpos(sfContext::getInstance()->getRequest()->getHost(), 'server.garin.su') !== false
        ? 'localhost'
        : '192.168.1.3';
      self::$_conn =  new PDO('mysql:dbname=openx;host=' . $host, 'openx', 'N4rbet74btuFgrt28b5bgI',
        array(
          PDO::MYSQL_ATTR_INIT_COMMAND => "set character set latin1",
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        )
      );
    }
    return self::$_conn;
  }

  public static function getOneBanner($id) {
    $query = self::getConnection()->prepare('
      SELECT ass.ad_id, ass.zone_id, ban.url, ban.htmltemplate, ban.compiledlimitation as limits
      FROM `ox_ad_zone_assoc` as ass LEFT JOIN `ox_banners` ban ON ass.ad_id = ban.bannerid
      WHERE  ban.status = 0 AND ban.bannerid = ? AND ass.zone_id != 0
      ORDER BY ass.zone_id LIMIT 1');
    $query->execute(array($id));
    return $query->fetch(PDO::FETCH_ASSOC);
  }

  public static function getBannersForInvisibleList() {
    $query = self::getConnection()->prepare('
      SELECT ass.ad_id, ass.zone_id, ban.url, ban.htmltemplate, ban.compiledlimitation as limits
      FROM `ox_ad_zone_assoc` as ass LEFT JOIN `ox_banners` ban ON ass.ad_id = ban.bannerid
      WHERE ass.zone_id IN (
        SELECT z.zoneid FROM `ox_zones` as z WHERE z.affiliateid = 1
      ) AND ban.status = 0
      AND ban.htmltemplate NOT LIKE  "%put_banner%"
      GROUP BY ass.ad_id ORDER BY url DESC');
    $query->execute();
    return $query->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function updateStat($id){
    if (!empty($id)) {
      if (is_int($id)) {
        //Total stat. Get id for update.
        $query = self::getConnection()->prepare('
          SELECT `data_intermediate_ad_id` as id
          FROM `ox_data_intermediate_ad`
          WHERE ad_id = ? AND zone_id = 333 ORDER BY date_time DESC LIMIT 1
        ');
        $query->execute(array($id));
        $row = $query->fetch(PDO::FETCH_ASSOC);
        //Update total stat by id.
        $query = self::getConnection()->prepare('
          UPDATE `ox_data_intermediate_ad` SET impressions = impressions + 1
          WHERE data_intermediate_ad_id = ?
        ');
        $query->execute(array($row['id']));


        //Hourly stat. Get id for update.
        $query = self::getConnection()->prepare('
          SELECT `data_summary_ad_hourly_id` as id
          FROM `ox_data_summary_ad_hourly`
          WHERE ad_id = ? AND zone_id = 333 ORDER BY date_time DESC LIMIT 1
        ');
        $query->execute(array($id));
        $row = $query->fetch(PDO::FETCH_ASSOC);
        //Update hourly stat by id.
        $query = self::getConnection()->prepare('
          UPDATE `ox_data_summary_ad_hourly` SET impressions = impressions + 1
          WHERE data_summary_ad_hourly_id = ?
        ');
        $query->execute(array($row['id']));

        return true;
      }
    }
    return false;
  }

  public static function hitClick()
  {
     //Total stat. Get id for update.
      $query = self::getConnection()->prepare('
        SELECT `data_intermediate_ad_id` as id
        FROM `ox_data_intermediate_ad`
        WHERE ad_id = ? ORDER BY date_time DESC LIMIT 1
      ');
      $query->execute(array(self::NAYDIDOM_HIT_BANNER_ID));
      $row = $query->fetch(PDO::FETCH_ASSOC);
      //Update total stat by id.
      $query = self::getConnection()->prepare('
        UPDATE `ox_data_intermediate_ad` SET clicks = clicks + 1
        WHERE data_intermediate_ad_id = ?
      ');
      $query->execute(array($row['id']));

      //Hourly stat. Get id for update.
      $query = self::getConnection()->prepare('
        SELECT `data_summary_ad_hourly_id` as id
        FROM `ox_data_summary_ad_hourly`
        WHERE ad_id = ? ORDER BY date_time DESC LIMIT 1
      ');
      $query->execute(array(self::NAYDIDOM_HIT_BANNER_ID));
      $row = $query->fetch(PDO::FETCH_ASSOC);
      //Update hourly stat by id.
      $query = self::getConnection()->prepare('
        UPDATE `ox_data_summary_ad_hourly` SET clicks = clicks + 1
        WHERE data_summary_ad_hourly_id = ?
      ');
      $query->execute(array($row['id']));

      return true;
  }

  public static function generateBannerHTML($banner_id)
  {
    if (!empty($banner_id) && preg_match('/^\d+$/', $banner_id)) {
      $banner = OpenX::getOneBanner((int)$banner_id);

      if(!empty($banner['htmltemplate']) && preg_match('#href="(.*?)"#', $banner['htmltemplate'], $url)) {
        $url = $url[1];
      }
      else{
        $url = $banner['url'];
      }

      $link = 'http://media.mesto.ru/www/delivery/ck.php?' . http_build_query(array(
        'oaparams'  => 2,
        'bannerid'  => $banner['ad_id'],
        'zoneid'    => $banner['zone_id'],
        'oadest'    => $url
      ), null, '__');

      if(preg_match('/x-shockwave-flash/', $banner['htmltemplate'])){
        $banner['htmltemplate'] = sprintf('<a href="" class="special-overlay"><div></div></a>%s', $banner['htmltemplate']);
      }
      
      return preg_replace('/href=".*?"/', 'href="' . $link . '"', $banner['htmltemplate']);
    }

    return false;
  }
  
  public static function generateBannerLink($banner_id)
  {
    if (!empty($banner_id) && preg_match('/^\d+$/', $banner_id)) {
      $banner = OpenX::getOneBanner((int)$banner_id);

      if(!empty($banner['htmltemplate']) && preg_match('#href="(.*?)"#', $banner['htmltemplate'], $url)) {
        $url = $url[1];
      }
      else{
        $url = $banner['url'];
      }

      $link = 'http://media.mesto.ru/www/delivery/ck.php?' . http_build_query(array(
        'oaparams'  => 2,
        'bannerid'  => $banner['ad_id'],
        'zoneid'    => $banner['zone_id'],
        'oadest'    => $url
      ), null, '__');      
      
      return $link;
    }

    return false;
  }
}
