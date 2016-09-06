<?php

/**
 * Parse street coords via gmap
 *
 * @author kmad
 */
class ExportToXml extends sfBaseTask {
  
  private $template = '
  <item>
  <campaignid></campaignid>
  <bannerid>0</bannerid>
  <tag>%%tag%%</tag>
  <type>%%type%%</type>
  <text>%%text%% %%tag%% %%type%%</text>
  <link>%%link%%</link>
  </item>';

  public function configure() {

    $this->namespace        = 'domus';
    $this->name             = 'exporttoxml';
  }

  public function execute($arguments = array(), $options = array()) {
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);

    $types = array_keys(Lot::$types);
    $conn = Doctrine::getConnectionByTableName('Lot');
    $dir = sfConfig::get('sf_upload_dir');
    foreach ($types as $type) {
      $this->logSection('Type', $type);
      $res = $conn->execute('SELECT * FROM lot WHERE type = ? AND status = ?', array($type, 'active'));
      $this->logSection('Count', $res->rowCount());
      $file = fopen($dir . '/' . $type . '.xml', 'w');
      fwrite($file, '<banners>');
      while($row = $res->fetch()) {
        $tag = $type;
        $text = $row['address1'] . ', ' . $row['address2'];
        $link = 'http://www.mesto.ru/' . $type . '/' . $row['id'];
        fwrite($file, $this->fillTemplate($tag, $type, $text, $link));
      }
      fwrite($file, PHP_EOL . '</banners>' . PHP_EOL);
      fclose($file);
    }
  }
  
  private function fillTemplate($tag, $type, $text, $link) {
    $search = array('%%tag%%', '%%type%%', '%%text%%', '%%link%%');
    $replace = array($tag, $type, $text, $link);
    return str_replace($search, $replace, $this->template);
  }

}
?>
