<?php
/**
 *
 */

final class PHPSSTrunk implements PHPSSRender {

  protected $rules     = array();
  protected $media     = array();
  protected $keyframes = array();
  protected $imports   = array();
  protected $charset   = "Unknown";
  protected $mediaType = false;

  public function loadData(stdClass $obj) {
    foreach ($obj->imports as $raw_import) {
      $this->addImport($raw_import);
    }

    $this->setCharset($obj->charset);
    $this->setMediaType($obj->mediaType);

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

    foreach ((array)$obj->media as $raw_media) {
      $media = new PHPSSTrunk;
      $media->loadData($raw_media);
      $this->addMedia($media);
    }

    return $this;
  }

  public function numberOfSelectors() {
    $selector_count = 0;
    foreach ($this->rules as $rule) {
      $selector_count += $rule->numberOfSelectors();
    }
    foreach ($this->media as $media) {
      $selector_count += $media->numberOfSelectors();
    }
    foreach ($this->keyframes as $keyframe) {
      $selector_count += $keyframe->numberOfSelectors();
    }
    return $selector_count;
  }

  public function numberOfRules() {
    $rule_count = count($this->rules);
    foreach ($this->media as $media) {
      $rule_count += $media->numberOfRules();
    }
    foreach ($this->keyframes as $keyframe) {
      $rule_count += $keyframe->numberOfRules();
    }
    return $rule_count;
  }

  public function numberOfKeyframes() {
    return count($this->keyframes);
  }

  public function numberOfMedia() {
    return count($this->media);
  }

  public function numberOfProperties() {
    $property_count = 0;
    foreach ($this->rules as $rule) {
      $property_count += $rule->numberOfProperties();
    }
    foreach ($this->media as $media) {
      $property_count += $media->numberOfProperties();
    }
    foreach ($this->keyframes as $keyframe) {
      $property_count += $keyframe->numberOfProperties();
    }
    return $property_count;
  }

  public function addRule(PHPSSRule $rule) {
    $this->rules[] = $rule;
    return $this;
  }

  public function addKeyframe(PHPSSKeyframe $keyframe) {
    $this->keyframes[] = $keyframe;
    return $this;
  }

  public function addMedia(PHPSSTrunk $media) {
    $this->media[] = $media;
    return $this;
  }

  public function setMediaType($media_type) {
    $this->mediaType = $media_type;
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
    if ($this->mediaType === false) {
      $rendered .= "<div class='uk-panel uk-text-center uk-panel-box " .
                   "uk-text-info'>Within this CSS file, there are " .
                     number_format($this->numberOfProperties()) .
                     " properties in " .
                     number_format($this->numberOfRules()) .
                     " rules selected by " .
                     number_format($this->numberOfSelectors()) .
                     " selectors. There area also " .
                     number_format($this->numberOfKeyframes()) .
                     " Keyframes, and " .
                     number_format($this->numberOfMedia()) .
                     " Media Templates." .
                   "</div>";
    } else {
      $rendered .= "Media: {$this->mediaType}<br>";
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
    foreach ($this->media as $media) {
      $rendered .= $media->render();
    }
    return $rendered;
  }

  public function renderCSS($min=false) {
    if ($min) {

      foreach ($this->rules as $rule) {
        $rendered .= $rule->renderCSS($min);
      }
      foreach ($this->media as $media) {
        $rendered .= $media->renderCSS($min);
      }

      if ($this->mediaType !== false) {
        $rendered = "@media {$this->mediaType}\{{$rendered}\}";
      }

    } else {
      $rendered .= "/**\n * CSS Rendered by PHPSS\n */\n\n";
      foreach ($this->rules as $rule) {
        $rendered .= $rule->renderCSS($min) . "\n";
      }
      foreach ($this->subTrunks as $subTrunk) {
        $rendered .= $subTrunk->renderCSS($min);
      }

      if ($this->mediaType !== false) {
        $rendered = "@media {$this->mediaType} \{\n{$rendered}\n\}";
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
    $me->media  = array();
    $me->keyframes  = array();
    $me->imports    = $this->imports;
    $me->charset    = $this->charset;
    $me->mediaType = $this->mediaType;

    foreach ($this->keyframes as $keyframe) {
      $me->keyframes[] = $keyframe->renderArray();
    }

    foreach ($this->rules as $rule) {
      $me->rules[] = $rule->renderArray();
    }

    foreach ($this->media as $media) {
      $me->media[] = $media->renderArray();
    }

    return $me;
  }

}
