<?php
require("PHPSS.php");
$css = file_get_contents("test.css");

$parser = new PHPSSParser($css);
$ast = $parser->parse();

$newast = with(new PHPSSTrunk)->loadData(json_decode(json_encode($ast->renderArray())));

echo $newast->render();
