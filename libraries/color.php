<?php
class Color {

  protected $red;
  protected $green;
  protected $blue;
  protected $alpha;

  public function __toString() {
    return $this->RGBAStringValue();
  }

  public function opposite() {
    return new Color(255 - $this->red,
                     255 - $this->green,
                     255 - $this->blue,
                     0.5);
  }

  public function hexValue() {
    return dechex($this->red) .
           dechex($this->green) .
           dechex($this->blue);
  }

  public function hexStringValue() {
    return '#' . $this->hexValue();
  }

  public function RGBStringValue() {
    return 'rgb(' . $this->red . ',' .
                    $this->green . ',' .
                    $this->blue . ')';
  }

  public function RGBAStringValue() {
    return 'rgba(' . $this->red . ',' .
                    $this->green . ',' .
                    $this->blue . ',' .
                    $this->alpha . ')';
  }

  public function __construct($r, $g=false, $b=false, $a=1) {
    if ($g === false) {
      $value = $r;
    } else {
      $this->red   = (float)$r;
      $this->green = (float)$g;
      $this->blue  = (float)$b;
      $this->alpha = (float)$a;
      return;
    }
    if ($value[0] == "#") {
      if (strlen($value) == 4) {
        $hex_r = $value[1] . $value[1];
        $hex_g = $value[2] . $value[2];
        $hex_b = $value[3] . $value[3];
      } else {
        $hex_r = substr($value, 1, 2);
        $hex_g = substr($value, 3, 2);
        $hex_b = substr($value, 5, 2);
      }
      $this->red   = intval($hex_r, 16);
      $this->blue  = intval($hex_g, 16);
      $this->green = intval($hex_b, 16);
      $this->alpha = 1.0;
    } else if (substr($value, 0, 4) == 'rgb(') {
      $rgb = explode(',', rtrim(substr($value, 4), ')'));
      $this->red   = (float)trim($rgb[0]);
      $this->blue  = (float)trim($rgb[1]);
      $this->green = (float)trim($rgb[2]);
      $this->alpha = 1.0;
    } else if (substr($value, 0, 4) == 'rgba(') {
      $rgb = explode(',', rtrim(substr($value, 5), ')'));
      $this->red   = (float)trim($rgb[0]);
      $this->blue  = (float)trim($rgb[1]);
      $this->green = (float)trim($rgb[2]);
      $this->alpha = (float)trim($rgb[3]);
    }
  }
}
