<?php

namespace TwigOverride\Test\Providers;

use TwigOverride\Providers\SimpleRewriteProvider;

/**
 * A provider for functional testing.
 */
class TestRewriteProvider extends SimpleRewriteProvider {

  /**
   * A map of rewrites.
   *
   * @var array
   */
  private $paramMap;

  /**
   * Creates a TestRewriteProvider object.
   *
   * @param array $template_map
   *   A map where keys are 'from' template names and values are 'to' template
   *   names.
   * @param array $param_map
   *   A map where keys are 'from' param names and values are 'to' param names.
   */
  public function __construct(array $template_map, array $param_map) {
    parent::__construct($template_map);
    $this->paramMap = $param_map;
  }

  /**
   * {@inheritdoc}
   */
  public function preprocessTemplateArgs($template_name, array $with, array $_context, $only) {
    foreach ($with as $key => $value) {
      if (!empty($this->paramMap[$key])) {
        $with[$key] = $this->paramMap[$key];
      }
    }
    return $with;
  }

}
