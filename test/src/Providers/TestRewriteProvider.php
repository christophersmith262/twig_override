<?php

namespace TwigOverride\Test\Providers;

use TwigOverride\Providers\SimpleRewriteProvider;

class TestRewriteProvider extends SimpleRewriteProvider {

  private $paramMap;

  public function __construct(array $template_map, array $param_map) {
    parent::__construct($template_map);
    $this->paramMap = $param_map;
  }

  public function preprocessTemplateArgs($template_name, array $with, array $_context, $only) {
    foreach ($with as $key => $value) {
      if (!empty($this->paramMap[$key])) {
        $with[$key] = $this->paramMap[$key];
      }
    }
    return $with;
  }

}
