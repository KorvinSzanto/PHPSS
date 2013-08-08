<?php
/**
 * Property
 */

class PHPSSProperty implements PHPSSRender {

  protected $property;
  protected $rawValue;
  protected $isImportant = false;

  public function render() {
    return ($this->isImportant ? "Important " : "") .
            "{$this->property} => {$this->rawValue}";
  }

  public function renderCSS($min = false) {
    $value = $this->rawValue;
    if ($min) {
      return "{$this->property}:{$value}" .
              ($this->isImportant ? "!important" : "") . ";";
    } else {
      return "{$this->property}: {$value}" .
              ($this->isImportant ? " !important" : "") . ";";
    }
  }

  public function setProperty($property) {
    $this->property = $property;
    return $this;
  }

  public function setRawValue($raw_value) {
    $this->isImportant = (strtolower(substr($raw_value, -10)) == '!important');
    if ($this->isImportant) {
      $this->rawValue = rtrim(substr($raw_value, 0, -10));
    } else {
      $this->rawValue = $raw_value;
    }
    return $this;
  }

  public function renderEdit() {
    return;
  }

}
