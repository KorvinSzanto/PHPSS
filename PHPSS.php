<?php

interface PHPSSRender {
  public function render();
  public function renderCSS($min);
  public function renderEdit();
}

require_once 'phpss_property.php';
require_once 'phpss_rule.php';
require_once 'phpss_trunk.php';
require_once 'phpss_parser.php';
require_once 'phpss_property_map.php';