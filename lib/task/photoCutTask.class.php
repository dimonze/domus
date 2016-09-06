<?php

/**
 * Cut off logos from lot images
 *
 * @author kmad
 */
class photoCutTask extends sfBaseTask {

  protected $bottom;
  protected $top;
  protected $left;
  protected $right;

  public function configure() {
    $this->addOptions(array(
      new sfCommandOption('user_id', null, sfCommandOption::PARAMETER_REQUIRED, 'User id'),
      new sfCommandOption('lot_id', null, sfCommandOption::PARAMETER_OPTIONAL, 'Lot id'),
      new sfCommandOption('bottom', null, sfCommandOption::PARAMETER_OPTIONAL, 'Bottom cut(px)', 0),
      new sfCommandOption('top', null, sfCommandOption::PARAMETER_OPTIONAL, 'Top cut(px)', 0),
      new sfCommandOption('left', null, sfCommandOption::PARAMETER_OPTIONAL, 'Left cut(px)', 0),
      new sfCommandOption('right', null, sfCommandOption::PARAMETER_OPTIONAL, 'Right cut(px)', 0),
    ));

    $this->namespace = 'domus';
    $this->name = 'photocut';
    $this->briefDescription = 'Cut off logos from lot images';
  }

  public function execute($arguments = array(), $options = array()) {
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);
    if (empty($options['user_id'])) throw new Exception('Parameter "user_id" is required');
    $user_id = $options['user_id'];
    
    $this->bottom = $options['bottom'];
    $this->top = $options['top'];
    $this->left = $options['left'];
    $this->right = $options['right'];

    $lots = Doctrine::getTable('Lot')
            ->createQuery('l')
            ->select('l.id, l.images')
            ->where('user_id = ?', $user_id);
    if ($options['lot_id']) {
      $lots->andWhere('id = ?', $options['lot_id']);
    }
    $lots = $lots->execute(array(), Doctrine::HYDRATE_ARRAY);
    foreach ($lots as $lot) {
      $images = $lot['images'] ? explode(',', $lot['images']) : array();
      echo 'Processing lot #' . $lot['id'] . PHP_EOL;
      foreach ($images as $image) {
        echo 'Cropping ' . $this->getImagePath($lot['id']) . '/' . $image . PHP_EOL;
        $this->crop($this->getImagePath($lot['id']) . '/' . $image);
      }
    }
  }

  public function getImagePath($id) {
    $lot_id = $id;
    $base = sfConfig::get('sf_upload_dir');
    $base .= '/lot';

    $id = base_convert((int) $id, 10, 36);
    $id = sprintf("%'03s", $id);

    $path = array_slice(str_split($id), -3);
    $path = array_reverse($path);
    $path[] = $lot_id;

    $image_path = $base . '/' . implode('/', $path);

    return $image_path . '/source';
  }

  public function crop($image) {
    $info = getimagesize($image);
    $width = $info[0];
    $height = $info[1];

    $width -= $this->left + $this->right;
    $height -= $this->top + $this->bottom;
    $x = $this->left;
    $y = $this->top;

    $img_obj = new Imagick($image);
    $img_new = $img_obj->clone();
    if (!$img_new->cropImage($width, $height, $x, $y)) {
      unlink($image);
      continue;
    }
    if (!$img_new->writeImage($image)) {
      unlink($image);
      continue;
    }
  }

}

?>
