<?php
/**
 * Property
 */

class PHPSSProperty implements PHPSSRender {

  protected $property;
  protected $rawValue;
  protected $isImportant = false;

  public function loadData(stdClass $obj) {
    $this->setProperty($obj->property);
    $this->setRawValue($obj->rawValue);

    return $this;
  }

  public function render() {
    return ($this->isImportant ? "Important " : "") .
            "{$this->property} => " . $this->getValue();
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

  public function getValue() {
    return htmlspecialchars($this->rawValue);
  }

  public function setProperty($property) {
    $this->property = preg_replace(
      "~[^a-z0-9\-]~i",
      '',
      str_replace("'","\'", $property));
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

  public function renderArray() {
    $me = new stdClass;
    $me->property = $this->property;
    $me->rawValue = $this->rawValue;
    $me->isImportant = $this->isImportant;
    return $me;
  }

}
