<?php

class KCaptcha
{
  protected
    $options = array(),
    $key = null,
    $alphabet = '0123456789abcdefghijklmnopqrstuvwxyz',
    $fonts = array(),

    $image = null,
    $bColor = null,
    $fColor = null;

  /**
   *
   * @param array $options Array of $option => $value pairs. Allowed options are:
   *  - allowed_symbols: '23456789abcdeghkmnpqsuvxyz'
   *  - length: 5..6
   *  - width: 120
   *  - height: 60
   *  - credits: 'www.captcha.ru'
   *  - foreground: array(0..100, 0..100, 0..100)
   *  - background: array(200..255, 200..255, 200..255)
   *  - exclude_pattern: null
   *  - include_pattern: 'cp|cb|ck|c6|c9|rn|rm|mm|co|do|cl|db|qp|qb|dp|ww'
   *  - ampl_x: null
   *  - ampl_y: null
   */
  public function __construct($options = array())
  {
    $this->options = array(
      'allowed_symbols' => '23456789abcdeghkmnpqsuvxyz',
      'length' => mt_rand(5, 6),
      'width'  => 120,
      'height' => 60,
      'credits' => '',
      'foreground' => array(mt_rand(0, 100), mt_rand(0, 100), mt_rand(0, 100)),
      'background' => array(mt_rand(200, 255), mt_rand(200, 255), mt_rand(200, 255)),
    );

    $this->setOptions($options);

    $this->fonts = glob(dirname(__FILE__).'/fonts/*.png');

    $this->generateKey();
  }

  public function getOptions()
  {
    return $this->options;
  }

  public function getOption($name, $default = null)
  {
    return isset($this->options[$name]) && $this->options[$name] !== null ? $this->options[$name] : $default;
  }

  public function setOptions($options)
  {
    $this->options = array_merge($this->options, $options);
  }

  public function setOption($name, $value)
  {
    $this->options[$name] = $value;
  }

  public function getKey()
  {
    return $this->key;
  }

  public function generateKey()
  {
    $symbols = $this->getOption('allowed_symbols');

    do
    {
      $key = '';
      for ($i = 0; $i < $this->getOption('length'); $i++)
      {
        $key .= substr($symbols, mt_rand(0, strlen($symbols) - 1), 1);
      }
    }
    while (!$this->checkKey($key));

    return $this->key = $key;
  }

  private function checkKey ($key)
  {
    $include_pattern = $this->getOption('include_pattern', 'cp|cb|ck|c6|c9|rn|rm|mm|co|do|cl|db|qp|qb|dp|ww');
    $exclude_pattern = $this->getOption('exclude_pattern');
    $result = true;

    if ($include_pattern) {
      $result = $result && preg_match('/' . $include_pattern . '/', $key);
    }
    if ($exclude_pattern) {
      $result = $result && !preg_match('/' . $exclude_pattern . '/', $key);
    }

    return $result;
  }

  public function render()
  {
    $this->initImage();
    $this->drawText();

    ob_start();
    imagepng($this->image);
    $image = ob_get_clean();
    
    $this->freeImage();

    return $image;
  }

  protected function initImage()
  {
    $credits = (string) $this->getOption('credits');

    $w = $this->getOption('width');
    $h = $this->getOption('height');

    $this->image = imagecreatetruecolor($w, $h  + (strlen($credits) ? 12 : 0));

    list($fR, $fG, $fB) = $this->getOption('foreground');
    $this->fColor = imagecolorallocate($this->image, $fR, $fG, $fB);

    list($bR, $bG, $bB) = $this->getOption('background');
    $this->bColor = imagecolorallocate($this->image, $bR, $bG, $bB);

    imagefilledrectangle($this->image, 0, 0, $w - 1, $h - 1, $this->bColor);

    if (strlen($credits))
    {
      imagefilledrectangle($this->image, 0, $h, $w - 1, $h + 11, $this->fColor);
      imagestring($this->image, 2, ($w - imagefontwidth(2)*strlen($credits))/2, $h - 2, $credits, $this->bColor);
    }
  }

  protected function freeImage()
  {
    imagedestroy($this->image);
    $this->image = null;
  }

  protected function drawText()
  {
    $font = imagecreatefrompng($this->fonts[mt_rand(0, count($this->fonts) - 1)]);

    $this->createTextImage($font);

    imagedestroy($font);
  }


  protected function loadFontMetrics($font)
  {
    imagealphablending($font, true);

    $w = imagesx($font);
    $h = imagesy($font);
    
    $metrics = array();
    $symbol = 0;

    $isSymbol = false;

    for ($i = 0; $i < $w; $i++)
    {
      $isTransparent = imagecolorat($font, $i, 0) >> 24 == 0x7F;
      $s = substr($this->alphabet, $symbol, 1);

      if ($isSymbol)
      {
        if ($isTransparent)
        {
          $metrics[$s]['end'] = $i;
          $isSymbol = false;
          $symbol++;
        }
      }
      else
      {
        if (!$isTransparent)
        {
          $metrics[$s]['start'] = $i;
          $isSymbol = true;
        }
      }
    }
    
    return $metrics;
  }

  protected function createTextImage($font)
  {
    $metrics = $this->loadFontMetrics($font);

    $w = $this->getOption('width');
    $h = $this->getOption('height');
    
    $im = imagecreatetruecolor($w, $h);

    imagefilledrectangle($im, 0, 0, $w - 1, $h - 1, 0xFFFFFF);

    $x = 1;
    for ($i = 0; $i < $this->getOption('length'); $i++)
    {
      $m = $metrics[substr($this->getKey(), $i, 1)];

      $fluctuation = round($h/15);
      $y = mt_rand(-$fluctuation, $fluctuation) + ($h - imagesy($font))/2 + 2;

      $shift = 0;
			if ($i > 0)
      {
        $shift = 10000;
        for ($sy = 7; $sy < imagesy($font) - 20; $sy++)
        {
          for($sx = $m['start'] - 1; $sx < $m['end']; $sx++)
          {
            $opacity = imagecolorat($font, $sx, $sy) >> 24;
            if ($opacity < 0x7F)
            {
              $left = $sx - $m['start'] + $x;
              $py = $sy + $y;

              if ($py > $h)
                break;

              for ($px = min($left, $w - 1); $px > $left - 12 && $px >= 0; $px--)
              {
                $color = imagecolorat($im, $px, $py) & 0xFF;
								if ($color + $opacity < 190)
                {
                  if ($shift > $left - $px)
                  {
                    $shift = $left - $px;
                  }
                  break;
                }
              }
              break;
            }
          }
        }

        if($shift == 10000)
        {
          $shift = mt_rand(4, 6);
        }
      }

      imagecopy($im, $font, $x - $shift, $y, $m['start'], 1, $m['end'] - $m['start'], imagesy($font) - 1);
			$x += $m['end'] - $m['start'] - $shift;
    }
    
    //while ($x >= $w - 10);

    $this->applyWaveDistortion($im, $x/2);

    imagedestroy($im);
  }

  protected function applyWaveDistortion($text, $center)
  {
    $w = $this->getOption('width');
    $h = $this->getOption('height');

    $perX1 = mt_rand(750000, 1200000)/10000000;
		$perX2 = mt_rand(750000, 1200000)/10000000;
		$perY1 = mt_rand(750000, 1200000)/10000000;
		$perY2 = mt_rand(750000, 1200000)/10000000;

    $phX1 = mt_rand(0, 31415926)/10000000;
    $phX2 = mt_rand(0, 31415926)/10000000;
    $phY1 = mt_rand(0, 314159260)/10000000;
		$phY2 = mt_rand(0, 31415926)/10000000;

    $amplX = $this->getOption('ampl_x', mt_rand(330, 420)/110);
		$amplY = $this->getOption('ampl_y', mt_rand(330, 450)/110);

    for ($x = 0; $x < $w; $x++)
    {
			for ($y = 0; $y < $h; $y++)
      {
				$sx = $x + (sin($x*$perX1 + $phX1) + sin($y*$perY1 + $phY1))*$amplX - $w/2 + $center + 1;
				$sy = $y + (sin($x*$perX2 + $phX2) + sin($y*$perY2 + $phY2))*$amplY;

				if ($sx < 0 || $sy < 0 || $sx >= $w - 1 || $sy >= $h - 1)
        {
					continue;
				}
        else
        {
					$color   = imagecolorat($text, $sx, $sy) & 0xFF;
					$colorX  = imagecolorat($text, $sx + 1, $sy) & 0xFF;
					$colorY  = imagecolorat($text, $sx, $sy + 1) & 0xFF;
					$colorXY = imagecolorat($text, $sx + 1, $sy + 1) & 0xFF;
				}

				if ($color == 0xFF && $colorX == 0xFF && $colorY == 0xFF && $colorXY == 0xFF)
        {
					continue;
				}
        elseif ($color == 0 && $colorX == 0 && $colorY == 0 && $colorXY == 0)
        {
					$newColor = $this->fColor;
				}
        else
        {
					$frsx = $sx - floor($sx);
					$frsy = $sy - floor($sy);
					$frsx1 = 1 - $frsx;
					$frsy1 = 1 - $frsy;

					$k = $color*$frsx1*$frsy1 + $colorX*$frsx*$frsy1 + $colorY*$frsx1*$frsy + $colorXY*$frsx*$frsy;

          $k = min($k, 0xFF)/0xFF;

          $r = $k*self::getChannel($this->bColor, self::RED) + (1 - $k)*self::getChannel($this->fColor, self::RED);
          $g = $k*self::getChannel($this->bColor, self::GREEN) + (1 - $k)*self::getChannel($this->fColor, self::GREEN);
          $b = $k*self::getChannel($this->bColor, self::BLUE) + (1 - $k)*self::getChannel($this->fColor, self::BLUE);

          $newColor = imagecolorallocate($text, $r, $g, $b);
				}

				imagesetpixel($this->image, $x, $y, $newColor);
			}
		}
  }


  const
    RED = 2,
    GREEN = 1,
    BLUE = 0;
  
  static protected function getChannel($color, $channel)
  {
    settype($color, 'integer');

    return ($color & (0xFF << 8*$channel)) >> 8*$channel;
  }

  static protected function setChannel(&$color, $channelColor, $channel)
  {
    $color &= 0xFFFFFF;
    $color &= ~(0xFF << 8*$channel);

    $channelColor = ($channelColor & 0xFF) << 8*$channel;
    
    return $color |= $channelColor;
  }

}
