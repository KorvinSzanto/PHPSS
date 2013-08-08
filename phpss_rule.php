<?php
/**
 * Rule: This contains the selector and the properties
 */

final class PHPSSRule implements PHPSSRender {

  protected $selectors = array();
  protected $properties = array();

  public function loadData(stdClass $obj) {
    foreach ($obj->selectors as $raw_selector) {
      $this->addSelector($raw_selector);
    }

    foreach ($obj->properties as $raw_property) {
      $property = PHPSSParser::getPropertyObject($raw_property->property);
      $property->loadData($raw_property);
      $this->addProperty($property);
    }

    return $this;
  }

  public function numberOfSelectors() {
    return count($this->selectors);
  }

  public function numberOfProperties() {
    return count($this->properties);
  }

  public function render() {
    $rule = "";
    $selector_count = count($this->selectors);
    for($i = 0; $i < $selector_count; $i++) {
      $selector = $this->selectors[$i];

      $rule .= "<strong>{$selector}" . ($i + 1 != $selector_count ? "," : "") .
               "</strong><br />";
    }
    $rule = htmlspecialchars(trim($rule,','));
    $rules = "";
    $styles = "";
    foreach ($this->properties as $property) {
      $rendered_property = $property->render();
      $rules .= "<li>{$rendered_property}</li>";
      $styles .= $property->renderCSS(true);
    }
    $styles = str_replace(array('fixed','"'), array('absolute',"'"), $styles);
    return "<div style='overflow:hidden;position:relative'>" .
             "<span style=\"{$styles}\">{$rule}</span>" .
             "<ul style='clear:both'>{$rules}</ul>" .
           "</div>";
  }

  public function addSelector($selector) {
    $this->selectors[] = $selector;
    return $this;
  }
  public function addProperty(PHPSSProperty $property) {
    $this->properties[] = $property;
    return $this;
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

  public function renderArray() {
    $me = new stdClass;
    $me->selectors = $this->selectors;
    $me->properties = array();

    foreach ($this->properties as $property) {
      $me->properties[] = $property->renderArray();
    }

    return $me;
  }

}
