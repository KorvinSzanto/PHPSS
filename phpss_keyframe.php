<?php
/**
 * Property
 */

final class PHPSSKeyframe implements PHPSSRender {
  protected $rules = array();
  protected $identifier;
  protected $calledProperty;

  public function loadData(stdClass $obj) {
    $this->setIdentifier($obj->identifier);
    $this->setCalledProperty($obj->calledProperty);
    foreach ($obj->rules as $raw_rule) {
      $this->addRule(with(new PHPSSRule)->loadData($raw_rule));
    }

    return $this;
  }

  public function numberOfSelectors() {
    $selector_count = 0;
    foreach ($this->rules as $rule) {
      $selector_count += $rule->numberOfSelectors();
    }
  }

  public function numberOfRules() {
    return count($this->rules);
  }

  public function numberOfProperties() {
    $property_count = 0;
    foreach ($this->rules as $rule) {
      $property_count += $rule->numberOfProperties();
    }
    return $property_count;
  }


  public function render() {
    $rendered = "Keyframe: {$this->identifier} called with " .
    "{$this->calledProperty}<br>";
    foreach ($this->rules as $rule) {
      $rendered .= $rule->render();
    }
    return $rendered;
  }

  public function renderCSS($min = false) {
    $rendered = "";
    if ($min) {
      $rendered .= "@{$this->calledProperty} {$this->identifier}{";

      foreach ($this->rules as $rule) {
        $rendered .= $rule->renderCSS($min);
      }

      $rendered .= "}";
    } else {
      $rendered .= "@{$this->calledProperty} {$this->identifier} {\n";

      foreach ($this->rules as $rule) {
        $rendered .= $rule->renderCSS($min);
      }

      $rendered .= "\n}";
    }
    return $rendered;
  }

  public function addRule(PHPSSRule $rule) {
    $this->rules[] = $rule;
  }

  public function setIdentifier($identifier) {
    $this->identifier = $identifier;
    return $this;
  }
  public function setCalledProperty($called) {
    $this->calledProperty = $called;
    return $this;
  }

  public function renderEdit() {
    return;
  }

  public function renderArray() {
    $me = new stdClass;
    $me->identifier = $this->identifier;
    $me->calledProperty = $this->calledProperty;
    $me->rules = array();
    foreach ($this->rules as $rule) {
      $me->rules[] = $rule->renderArray();
    }
    return $me;
  }

}
