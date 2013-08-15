<?php
/**
 * Property
 */

final class PHPSSMedia implements PHPSSRender {
  protected $rules = array();
  protected $type;

  public function loadData(stdClass $obj) {
    $this->setMediaType($obj->type);

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
    return $selector_count;
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
    $type = htmlspecialchars($this->type);
    $rendered = "Media Type: {$type}" . render_tag('br');
    foreach ($this->rules as $rule) {
      $rendered .= $rule->render();
    }
    return $rendered;
  }

  public function renderCSS($min = false) {
    $rendered = "";
    if ($min) {
      $rendered = "@media {$this->mediaType}\{";

      foreach ($this->rules as $rule) {
        $rendered .= $rule->renderCSS($min);
      }

      $rendered .= "}";
    } else {
      $rendered = "@media {$this->mediaType} \{\n";


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

  public function setMediaType($type) {
    $this->type = $type;
    return $this;
  }

  public function renderEdit() {
    return;
  }

  public function renderArray() {
    $me = new stdClass;
    $me->type = $this->type;
    $me->rules = array();
    foreach ($this->rules as $rule) {
      $me->rules[] = $rule->renderArray();
    }
    return $me;
  }

}
