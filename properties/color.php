<?php
require_once 'libraries/color.php';

class ColorProperty extends PHPSSProperty {

  protected $color;

  # Overrides
  public function setRawValue($value) {
    parent::setRawValue($value);
    $this->color = new Color($value);
  }

  public function render() {
    if ($this->color) {
      $rendered = $this->property . " => <span style='text-shadow:0px 0px 2px " .
              $this->color->opposite() . ";color:". $this->color .
              "'>{$this->rawValue}</span>";
      if ($this->isImportant) {
        $rendered = "Important ". $rendered;
      }
    }
    return parent::render();
  }

  public function getColor() {
    return $this->rawValue;
  }

}
