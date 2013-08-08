<?php
/**
 * Property
 */

final class PHPSSKeyframe implements PHPSSRender {

  protected $fromRule;
  protected $toRule;
  protected $identifier;
  protected $calledProperty;

  public function loadData(stdClass $obj) {
    $this->setIdentifier($obj->identifier);
    $this->setCalledProperty($obj->calledProperty);
    $this->setToRule(with(new PHPSSRule)->loadData($obj->toRule));
    $this->setFromRule(with(new PHPSSRule)->loadData($obj->fromRule));

    return $this;
  }

  public function render() {
    $rendered = "Keyframe: {$this->identifier} called with " .
    "{$this->calledProperty}<br>";
    $rendered .= $this->toRule->render();
    $rendered .= $this->fromRule->render();
    return $rendered;
  }

  public function renderCSS($min = false) {
    $value = $this->rawValue;
    if ($min) {
      return "@{$this->calledProperty} {$this->identifier}{" .
             $this->fromRule->renderCSS($min) .
             $this->toRule->renderCSS($min) .
             "}";
    } else {
      return "@{$this->calledProperty} {$this->identifier} {\n" .
             $this->fromRule->renderCSS($min) .
             $this->toRule->renderCSS($min) .
             "\n}";
    }
  }

  public function setFromRule(PHPSSRule $rule) {
    $this->fromRule = $rule;
    return $this;
  }
  public function setToRule(PHPSSRule $rule) {
    $this->toRule = $rule;
    return $this;
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
    $me->fromRule = $this->fromRule->renderArray();
    $me->toRule = $this->toRule->renderArray();
    $me->identifier = $this->identifier;
    $me->calledProperty = $this->calledProperty;
    return $me;
  }

}
