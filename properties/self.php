<?php

class SelfProperty extends PHPSSProperty {

  public function render() {
    $sanitized_raw_value = addslashes($this->rawValue);
    return ($this->isImportant ? "Important " : "") .
            "{$this->property} => <span style='{$this->property}:" .
            "{$sanitized_raw_value}'>" . $this->getValue() . "</span>";
  }

}
