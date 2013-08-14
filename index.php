<?php
if (isset($_POST['f'])) {
  $file = genToken();
  if ($response = pullCached($file)) {
    header('location: ?f=' . $file);
    exit;
  }
}
?>
<!doctype html>
<html>
  <head>
    <link rel="stylesheet" type="text/css" href="uikit.css">
  </head>
  <body>
    <div class='uk-container-center uk-container'>
        <?php
        if (isset($_GET['f']) && $_GET['f']) {
          $file = preg_replace('~[^a-z0-9]~i','',$_GET['f']);
          if ($response = pullCached($file)) {
            die("<div class='uk-width-large-1-1 uk-visible-large uk-panel-box'>" .
                $response . "</div>");

          }
        }

        function pullCached($file) {

          if (!file_exists('phpss_cache')) {
            mkdir('phpss_cache');
          }
          if (!file_exists('css_cache')) {
            mkdir('css_cache');
          }
          if (!file_exists('phpss_cache/' . $file) && isset($_FILES['file'])) {
            require_once 'PHPSS.php';

            if ($_FILES['file']['type'] != 'text/css') {
              echo "<div>Invalid File Type</div>";
              return false;
            }
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
            file_put_contents('phpss_cache/' . $file, $json);

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
          } while (file_exists('css_cache/' . $token)); // Hope this doesn't go forever!
          return $token;
        }


        ?>
      <div style='margin-top:20px' class="uk-panel uk-panel-box uk-panel-box-primary uk-width-1-2 uk-container-center uk-text-center">
        <form class="uk-form uk-form-stacked" action="" enctype="multipart/form-data" method="post">
          <div class="uk-form-row">
            <label class="uk-form-label" for="file">CSS File</label>
            <input type="file" name="file">
            <input type='submit' name='f' value='Submit' class='uk-button uk-button-primary'>
          </div>
        </form>
      </div>
    </div>

  </body>
</html>
