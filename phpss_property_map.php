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
  'color'));
applyClass('SelfProperty', 'self', array(
  'font',
  'font-family',
  'font-size',
  'font-weight',
  'font-style',
  'text-decoration',
  'text-transform',
  'outline',
  'border',
  'border-top',
  'border-bottom',
  'border-left',
  'border-right',
  'border-color',
  'background-color'));
