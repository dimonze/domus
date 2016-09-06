<?php

/**
 * Restore old photos from backup
 *
 * @author kmad
 */
class photoRestoreTask extends sfBaseTask {
  
  protected $backup_path;
  
  public function configure() {
    $this->addOptions(array(
      new sfCommandOption('backup_path', null, sfCommandOption::PARAMETER_REQUIRED, 'Backup path'),
      new sfCommandOption('user_id', null, sfCommandOption::PARAMETER_OPTIONAL, 'User id'),
    ));

    $this->namespace = 'domus';
    $this->name = 'photorestore';
    $this->briefDescription = 'Restore old photos from backup';
  }

  public function execute($arguments = array(), $options = array()) {
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);
    if (empty($options['backup_path'])) throw new Exception('Parameter "backup_path" is required');
    $this->backup_path = realpath($options['backup_path']);
    if (!is_dir($this->backup_path)) throw new Exception('Backup directory does not exists');
    
    $users = Doctrine::getTable('User')
             ->createQuery('u')
             ->select('u.id')
             ->where('photo is null');
    if (isset($options['user_id'])) {
      $users->where('id = ?', $options['user_id']);
    }
    $users =  $users->execute(array(), Doctrine::HYDRATE_ARRAY);
    $img_users = 0;
    foreach ($users as $user) {
      echo '#' . $user['id'] . ': ';
      if ($p = $this->getOldImagePath($user['id'])) {
        $to = $this->getPhotoPath($user['id'], true) . '/' . $user['id'] . '.jpg';
        try {
          $this->restoreImage($p, $to);
          Doctrine_Manager::connection()->query('UPDATE user SET photo = ? WHERE id = ?', array($user['id'] . '.jpg', $user['id']));
          echo $p . ' : '  . $to;
        }
        catch (Exception $e) {
          echo 'FAILED: ' . $e->getMessage();
        }
        $img_users++;
      } else {
        echo 'NO IMAGE';
      }
      echo PHP_EOL;
    }
    echo 'Total: ' . count($users) . ' with images: ' . $img_users . PHP_EOL;
  }

  public function getOldImagePath($id) {
    $filename = sprintf('%s/logo/%d/%d.jpg',
                        $this->backup_path,
                        floor($id / 20),
                        $id);
    return file_exists($filename) ? $filename : null;
  }

  public function restoreImage($from, $to) {
    $info = getimagesize($from);
    $width = $info[0];
    $height = $info[1];
    $x = (150 - $width) / 2;
    $y = (150 - $height) / 2;
    $old = new Imagick($from);
    $new = new Imagick();
    $new->newImage(150, 150, new ImagickPixel("white"));
    $new->compositeImage($old, $old->getImageCompose(), $x, $y);
    $new->writeImage($to);
  }
  
  public function getPhotoPath($_id, $create = true){
    $base = substr(str_replace(sfConfig::get('sf_web_dir'), '', sfConfig::get('sf_upload_dir')), 1);
    $base .= '/user';

    $id = base_convert((int) $_id, 10, 36);
    $id = sprintf("%'03s", $id);

    $path = array_slice(str_split($id), -3);
    $path = array_reverse($path);
    $path[] = $_id;
    $path[] = 'source';

    if ($create) {
      $_path = sfConfig::get('sf_web_dir') . '/' . $base;
      foreach ($path as $path_part) {
        $_path .= '/' . $path_part;
        if (!is_dir($_path)) {
          if (!mkdir($_path, 0777)) {
            throw new Exception('Can\'t create directory for image_path');
          }
        }
      }
    }
    $photo_path = $base . '/' . implode('/', $path);

    return sfConfig::get('sf_web_dir') . '/' . $photo_path;
  }

}

?>
