<?php
require("PHPSS.php");
$css = file_get_contents("test.css");

$parser = new PHPSSParser($css);
$ast = $parser->parse();
echo $ast->render();
