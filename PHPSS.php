<?php

// Exceptions
class PHPSSInvalidFileTypeException extends Exception {}

function with($obj) { return $obj; }
function render_tag($tag, array $attributes=array(), $contents=null) {
  $attribute_string = '';

  foreach ($attributes as $property => $value) {
    if (is_int($property)) {
      $attribute_string .= ' ' . $value;
    } elseif (!$value) {
      $attribute_string .= ' ' . $property;
    } else {
      $attribute_string .= " " . $property . "='" . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . "'";
    }
  }
  if ($contents === null) return "<{$tag}{$attribute_string} />";
  return "<{$tag}{$attribute_string}>{$contents}</{$tag}>";
}

interface PHPSSRender {
  public function render();
  public function renderCSS($min);
  public function renderEdit();
  public function renderArray();
}

require_once 'phpss_property.php';
require_once 'phpss_rule.php';
require_once 'phpss_keyframe.php';
require_once 'phpss_media.php';
require_once 'phpss_trunk.php';
require_once 'phpss_parser.php';
require_once 'phpss_property_map.php';
