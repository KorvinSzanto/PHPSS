<?php
require_once 'properties/color.php';

function applyClass($class, $property_name, $properties) {
  $properties = (array) $properties;
  require_once "properties/{$property_name}.php";
  foreach ($properties as $property) {
    PHPSSParser::addPropertyClass($property, $class);
  }
}

applyClass('ColorProperty', 'color', array(
  'color',
  'border-color',
  'background-color'));
