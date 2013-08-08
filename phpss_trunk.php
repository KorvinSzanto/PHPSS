<?php
/**
 *
 */

final class PHPSSTrunk implements PHPSSRender {

  protected $rules;
  protected $imports;
  protected $charset;

  public function __construct() {
    $this->imports = array();
    $this->rules = array();
  }

  public function addRule(PHPSSRule $rule) {
    $this->rules[] = $rule;
    return $this;
  }

  public function setCharset($charset) {
    $this->charset = $charset;
    return $this;
  }

  public function addImport($import) {
    $this->imports[] = $import;
    return $this;
  }

  public function render() {
    $rendered = "Character Encoding: {$this->charset}<br />";
    foreach ($this->rules as $rule) {
      $rendered .= $rule->render() . "\n";
    }
    return $rendered;
  }

  public function renderCSS($min=false) {
    $rendered = "";
    if ($min) {
      foreach ($this->rules as $rule) {
        $rendered .= $rule->renderCSS($min);
      }
    } else {
      $rendered .= "/**\n * CSS Rendered by PHPSS\n */\n\n";
      foreach ($this->rules as $rule) {
        $rendered .= $rule->renderCSS($min) . "\n";
      }
    }
    return $rendered;
  }
  public function renderEdit() {
    return;
  }

}
