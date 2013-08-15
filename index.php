<?php
require_once 'PHPSS.php';

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
      throw new PHPSSInvalidFileTypeException;
    }
    copy($_FILES['file']['tmp_name'], 'css_cache/' . $file);

    $css = file_get_contents('css_cache/' . $file);
    try {
      $ast = with(new PHPSSParser($css))->parse();
    } catch (PHPSSInvalidCSSException $e) {
      unlink('css_cache/' . $file);
      throw $e;
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


$body = '';
$render_form = true;

if (isset($_POST['f'])) {
  $file = genToken();
  try {
    pullCached($file);
    header('location: ?f=' . $file);
    exit;
  } catch (PHPSSInvalidCSSException $e) {
    $body = render_tag(
      'div',
      array('class'=>'uk-alert uk-alert-danger uk-width-1-2 uk-container-center uk-text-center'),
      'Invalid CSS');
  } catch (PHPSSInvalidFileTypeException $e) {
    $body = render_tag(
      'div',
      array('class'=>'uk-alert uk-alert-danger uk-width-1-2 uk-container-center uk-text-center'),
      'Invalid File Type');
  }
} elseif (isset($_GET['f']) && $_GET['f']) {
  $file = preg_replace('~[^a-z0-9]~i','',$_GET['f']);
  try {
    $body = render_tag(
      'div',
      array('class'=>'uk-width-large-1-1 uk-visible-large uk-panel-box'),
      pullCached($file));
    $render_form = false;
  } catch (PHPSSInvalidCSSException $e) {
    $body = render_tag(
      'div',
      array('class'=>'uk-alert uk-alert-danger uk-width-1-2 uk-container-center uk-text-center'),
      'Invalid CSS');
  } catch (PHPSSInvalidFileTypeException $e) {
    $body = render_tag(
      'div',
      array('class'=>'uk-alert uk-alert-danger uk-width-1-2 uk-container-center uk-text-center'),
      'Invalid File Type');
  }

}
if ($render_form) {
  // form
  $body .= render_tag(
    'div',
    array(
      'style'=>'margin-top:20px',
      'class'=>'uk-panel uk-panel-box uk-panel-box-primary ' .
               'uk-width-1-2 uk-container-center uk-text-center'),
    render_tag('form', array(
      'class'=>'uk-form uk-form-stacked',
      'action'=>'',
      'enctype'=>'multipart/form-data',
      'method'=>'post'),
      render_tag(
        'div',
        array('class'=>'uk-form-row'),
        render_tag(
          'label',
          array(
            'class'=>'uk-form-label',
            'for'=>'file'),
          'CSS File') .
        render_tag(
          'input',
          array('type'=>'file', 'name'=>'file')) .
        render_tag(
          'input',
          array(
            'type'=>'submit',
            'name'=>'f',
            'value'=>'Submit',
            'class'=>'uk-button uk-button-primary'))
      )));
}

echo "<!doctype html>" .
  render_tag(
    'html',
    array(),
    render_tag(
      'head',
      array(),
      render_tag(
        'link',
        array('rel'=>'stylesheet', 'type'=>'text/css', 'href'=>'uikit.css'))) .
    render_tag(
      'body',
      array(),
      render_tag('div', array('class'=>'uk-container-center uk-container'), $body)));
