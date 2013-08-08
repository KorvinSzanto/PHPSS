<?php
/**
 * Rule: This contains the selector and the properties
 */

final class PHPSSRule implements PHPSSRender {

  protected $selectors = array();
  protected $properties = array();

  public function render() {
    $rule = "";
    $selector_count = count($this->selectors);
    for($i = 0; $i < $selector_count; $i++) {
      $selector = $this->selectors[$i];

      $rule .= "<strong>{$selector}" . ($i + 1 != $selector_count ? "," : "") .
               "</strong><br />";
    }
    $rule = rtrim($rule,',');
    $rules = "";
    $styles = "";
    foreach ($this->properties as $property) {
      $rendered_property = $property->render();
      $rules .= "<li>{$rendered_property}</li>";
      $styles .= $property->renderCSS(true);
    }
    return "<div style='position:relative'><span style='{$styles}'>{$rule}</span><ul>{$rules}</ul><div>";
  }

  public function addSelector($selector) {
    $this->selectors[] = $selector;
  }
  public function addProperty(PHPSSProperty $property) {
    $this->properties[] = $property;
  }

  public function renderCSS($min=false) {
    $rendered = "";
    if ($min) {
      $rendered .= implode(',', $this->selectors) . "{";
      foreach ($this->properties as $property) {
        $rendered .= $property->renderCSS($min);
      }
      $rendered = rtrim($rendered,";") . "}";
    } else {
      $rendered .= implode(",\n", $this->selectors) . " {\n";
      foreach ($this->properties as $property) {
        $rendered_property = $property->renderCSS($min);
        $rendered .= "\t{$rendered_property}\n";
      }
      $rendered .= "}\n";
    }
    return $rendered;
  }

  public function renderEdit() {
    return;
  }

}
