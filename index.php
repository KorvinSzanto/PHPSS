<?php
if (isset($_GET['f']) && $_GET['f']) {
  $file = preg_replace('~[^a-z0-9]~i','',$_GET['f']);
  if ($response = pullCached($file)) {
    die($response);
  }
}
if (isset($_POST['f'])) {
  $file = preg_replace('~[^a-z0-9]~i','',$_POST['f']);
  if ($response = pullCached($file)) {
    header('location: ?f=' . $file);
    exit;
  }
}

function pullCached($file) {
  if (!file_exists('phpss_cache/' . $file) && isset($_FILES['file'])) {
    require_once 'PHPSS.php';
    copy($_FILES['file']['tmp_name'], 'css_cache/' . $file);

    $css = file_get_contents('css_cache/' . $file);
    try {
      $ast = with(new PHPSSParser($css))->parse();
    } catch (InvalidCSSException $e) {
      echo "<div>Invalid CSS file.</div>";
      unlink('css_cache/' . $file);
      return false;
    }

    $json = json_encode($ast->renderArray());
    file_put_contents('PHPSS_cache/' . $file, $json);

    return $ast->render();
  } else if (file_exists('phpss_cache/' . $file)) {
    require_once 'PHPSS.php';
    $obj = json_decode(file_get_contents('phpss_cache/' . $file));

    return with(new PHPSSTrunk)->loadData($obj)->render();
  }
  return false;
}


function genToken() {
  $grabbag = "abcdefghijklmnopqrstuvwxyz" .
             "ABCDEFGHIJKLMNOPQRSTUVWXYZ" .
             "0123456789";
  do {
    $token = "";
    $i = 5;
    while ($i--) {
      $token .= $grabbag[rand(0,61)];
    }
  } while (file_exists('css_cache/'+$token)); // Hope this doesn't go forever!
  return $token;
}


$token = genToken();
?>
<form action="" enctype="multipart/form-data" method="post">
  <input name='f' value='<?=$token?>' type='hidden' />
  CSS File <INPUT type="file" name="file"><br>
  <button>Submit</button>
</form>
