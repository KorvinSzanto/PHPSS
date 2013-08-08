<?php
/**
 *
 */

final class PHPSSTrunk implements PHPSSRender {

  protected $rules     = array();
  protected $subTrunks = array();
  protected $keyframes = array();
  protected $imports   = array();
  protected $charset   = "Unknown";
  protected $media     = false;

  public function loadData(stdClass $obj) {
    foreach ($obj->imports as $raw_import) {
      $this->addImport($raw_import);
    }

    $this->setCharset($obj->charset);
    $this->setMedia($obj->media);

    foreach ((array)$obj->rules as $raw_rule) {
      $rule = new PHPSSRule;
      $rule->loadData($raw_rule);
      $this->addRule($rule);
    }

    foreach ((array)$obj->keyframes as $raw_keyframe) {
      $keyframe = new PHPSSKeyframe;
      $keyframe->loadData($raw_keyframe);
      $this->addKeyframe($keyframe);
    }

    foreach ((array)$obj->subTrunks as $raw_subTrunk) {
      $sub_trunk = new PHPSSTrunk;
      $sub_trunk->loadData($raw_subTrunk);
      $this->addSubtrunk($sub_trunk);
    }

    return $this;
  }

  public function addRule(PHPSSRule $rule) {
    $this->rules[] = $rule;
    return $this;
  }

  public function addKeyframe(PHPSSKeyframe $keyframe) {
    $this->keyframes[] = $keyframe;
    return $this;
  }

  public function addSubtrunk(PHPSSTrunk $tree) {
    $this->subTrunks[] = $tree;
    return $this;
  }

  public function setMedia($media) {
    $this->media = $media;
    return $this;
  }

  public function setCharset($charset) {
    $this->charset = $charset;
    return $this;
  }

  public function addImport($import) {
    $this->imports[] = $import;
    return $this;
  }

  public function render() {
    $rendered = "";
    if ($this->media !== false) {
      $rendered .= "Media: {$this->media}<br>";
    }
    if ($this->charset) {
      $rendered .= "Character Encoding: {$this->charset}<br />";
    }
    foreach ($this->keyframes as $keyframe) {
      $rendered .= $keyframe->render();
    }
    foreach ($this->rules as $rule) {
      $rendered .= $rule->render();
    }
    foreach ($this->subTrunks as $subTrunk) {
      $rendered .= $subTrunk->render();
    }
    return $rendered;
  }

  public function renderCSS($min=false) {
    if ($min) {
      $rendered = "@media {$this->media} {";
      foreach ($this->rules as $rule) {
        $rendered .= $rule->renderCSS($min);
      }
      foreach ($this->subTrunks as $subTrunk) {
        $rendered .= $subTrunk->renderCSS($min);
      }

      if ($this->media !== false) {
        $rendered = "@media {$this->media}\{{$rendered}\}";
      }

    } else {
      $rendered .= "/**\n * CSS Rendered by PHPSS\n */\n\n";
      foreach ($this->rules as $rule) {
        $rendered .= $rule->renderCSS($min) . "\n";
      }
      foreach ($this->subTrunks as $subTrunk) {
        $rendered .= $subTrunk->renderCSS($min);
      }

      if ($this->media !== false) {
        $rendered = "@media {$this->media} \{\n{$rendered}\n\}";
      }

    }
    return $rendered;
  }

  public function renderEdit() {
    return;
  }

  public function renderArray() {
    $me = new stdClass;
    $me->rules = array();
    $me->subTrunks = array();
    $me->keyframes = array();
    $me->imports = $this->imports;
    $me->charset = $this->charset;
    $me->media   = $this->media;

    foreach ($this->keyframes as $keyframe) {
      $me->keyframes[] = $keyframe->renderArray();
    }

    foreach ($this->rules as $rule) {
      $me->rules[] = $rule->renderArray();
    }

    foreach ($this->subTrunks as $subTrunk) {
      $me->subTrunks[] = $subTrunk->renderArray();
    }

    return $me;
  }

}
