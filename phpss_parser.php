<?php
/**
 * HerpDerp
 */

final class PHPSSParser {

  protected $rawCSS;
  protected $charset;
  protected $includes;
  protected static $propertyClasses = array();

  public static function addPropertyClass($property, $class) {
    self::$propertyClasses[$property] = $class;
  }

  public function __construct($rawCSS) {
    $this->rawCSS = $rawCSS;
    $this->ast = array();
  }

  private function getSheets($css) {
    $sheets = array();
    $raw_sheets = array();
    $total_subsheets = preg_match_all('~^@media(?P<media>.+?) \{'.
                                      '(?P<rules>(.|\n)+?)\}\n\}~m',
                                      $css,
                                      $raw_sheets);

    $sheets['subsheets'] = array();
    foreach($raw_sheets['media'] as $key => $media) {
      $sheets['subsheets'][] = array(
        'media' => $media,
        'rules' => $raw_sheets['rules'][$key] . "}");
    }

    $raw_keyframes = array();
    $total_keyframes = preg_match_all('~^@(?P<called>(?:-.+?-)?keyframes) ' .
                                      '(?P<identifier>.+?) {' .
                                      '(?P<rules>(\n|.)+?)}\n}~m',
                                      $css,
                                      $raw_keyframes);
    $sheets['keyframes'] = array();
    foreach($raw_keyframes['called'] as $key => $called) {
      $sheets['keyframes'][] = array(
        'called' => $media,
        'identifier' => $raw_keyframes['identifier'][$key],
        'rules' => $raw_keyframes['rules'][$key] . "}");
    }

    $sheets['main'] = preg_replace('~^@((-.+?-)?keyframes|media).+?\{' .
                                   '(.|\n)+?\}\n\}~m',
                                   "",
                                   $css);

    return $sheets;
  }

  private function renderCleanCSS() {

    $clean_css = preg_replace(
      array(
        '~/\*((.|\n)*?)\*/~',
        '~(;|\}|{)~',
        '~;?\s*\}~',
        '~^\s*~m',
        '~\s*$~m',
        '~\n+~m',
        '~\};~',
        '~\s*\{~'),
      array('',"$1\n",";\n}",'','',"\n",'}',' {'),
      $this->rawCSS);
    $this->rawCSS = '';

    return $clean_css;
  }

  public function parse() {
    // http://www.php.net/manual/en/function.mb-detect-encoding.php#91051
    $bom_types = array(
      array('UTF-8',    chr(0xEF) . chr(0xBB) . chr(0xBF)),
      array('UTF-32BE', chr(0x00) . chr(0x00) . chr(0xFE) . chr(0xFF)),
      array('UTF-32LE', chr(0xFF) . chr(0xFE) . chr(0x00) . chr(0x00)),
      array('UTF-16BE', chr(0xFE) . chr(0xFF)),
      array('UTF-16LE', chr(0xFF) . chr(0xFE)));

    $charset = null;
    $test_string = substr($this->rawCSS, 0, 4);
    foreach ($bom_types as $bom_type) {
      if (strpos($test_string, $bom_type[1]) === 0) {
        $charset = $bom_type[0];
        break;
      }
    }

    $trimmed_css = trim($this->rawCSS);
    // We do this before cleaning the CSS because @charset has to be first.
    if (substr($trimmed_css,0,9) === "@charset ") {
      $raw_charset = substr($trimmed_css,0,strpos($trimmed_css,";"));
      $charset = trim(substr($raw_charset,9),'"\''); // Not sure if ' works
    }

    $clean_css = $this->renderCleanCSS();
    $sheets = $this->getSheets($clean_css);

    if (!$this->isValid($sheets['main'])) {
      throw new InvalidCSSException;
    }

    $ast = $this->createTree($sheets['main']);

    foreach ($sheets['subsheets'] as $sheet) {
      if (!$this->isValid($sheet['rules'])) {
        throw new InvalidCSSException;
      }
      $subast = $this->createTree($sheet['rules']);
      $subast->setMedia($sheet['media']);
      $ast->addSubtrunk($subast);
    }
    foreach ($sheets['keyframes'] as $raw_keyframe) {
      $keyframe = new PHPSSKeyframe;

      $raw_rules = array();
      $total_rules = preg_match_all(
        "~^(?P<selector>.+) \{(?P<properties>(\n|.)+?)\n\}~m",
        $raw_keyframe['rules'],
        $raw_rules);

      foreach($raw_rules['selector'] as $key => $selector) {
        $rule = $this->createRule($selector,$raw_rules['properties'][$key]);
        $keyframe->addRule($rule);
      }


      $keyframe->setIdentifier($raw_keyframe['identifier'])
               ->setCalledProperty($raw_keyframe['called']);

      $ast->addKeyframe($keyframe);
    }

    $ast->setCharset($charset);

    $css_head = substr($clean_css, 0, strpos($clean_css, '{'));
    $imports = array();
    $total_imports = preg_match_all("~^@import (.+);$~m", $css_head, $imports);

    foreach ($imports[1] as $import) {
      $ast->addImport($import);
    }

    return $ast;
  }

  private function createTree($css) {
    $ast = new PHPSSTrunk;
    $raw_rules = array();
    $total_rules = preg_match_all(
      "~^(?P<selector>.+) \{(?P<properties>(\n|.)+?)\n\}~m",
      $css,
      $raw_rules);

    foreach($raw_rules['selector'] as $key => $selector) {
      $rule = $this->createRule($selector,$raw_rules['properties'][$key]);
      $ast->addRule($rule);
    }

    return $ast;
  }

  private function createRule($selector, $raw_rule) {
    $rule = new PHPSSRule;
    $selectors = explode(',', $selector);
    foreach ($selectors as $selector_part) {
      $rule->addSelector(trim($selector_part));
    }

    $raw_properties = array();
    $total_properties = preg_match_all(
      "~^[ \t]*(?P<property>.+?)[ \t]*:[ \t]*(?P<value>.+?)[ \t]*;~m",
      $raw_rule,
      $raw_properties);

    foreach($raw_properties['property'] as $key => $property) {
      $property_object = self::getPropertyObject(strtolower($property));
      $property_object->setProperty($property)
                      ->setRawValue($raw_properties['value'][$key]);
      $rule->addProperty($property_object);
    }
    return $rule;
  }

  private function isValid($css) {
    $only_brackets = preg_replace(array(
      '~[^\\\\]?("|\')[^$1]+?[^\\\\]$1~',
      '~[^\{\}]~'),
      '',
      $css);
    if (strlen(str_replace('{}', '', $only_brackets))) {
      return false;
    }
    return true;
  }

  public static function getPropertyObject($property) {
    $classes = self::$propertyClasses;
    if (isset($classes[$property]) && class_exists($classes[$property])) {
      $object = new $classes[$property];
      return $object;
    }
    return new PHPSSProperty;
  }


}
class InvalidCSSException extends exception{}
