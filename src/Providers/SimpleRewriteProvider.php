<?php

namespace TwigOverride\Providers;

/**
 * A provider that performs simple mapping from one set of templates to another.
 *
 * Eg.
 * "{% include '@test1.twig' %}" => "{% include '@test2.twig' %}"
 */
class SimpleRewriteProvider implements ProviderInterface {

  /**
   * A map where keys or from template names and values are to template names.
   *
   * @var string[]
   */
  protected $templateMap;

  /**
   * Creates a simple template name mapping override provider.
   *
   * @param string[] $template_map
   *   A map where keys or from template names and values are to template names.
   */
  public function __construct(array $template_map) {
    $this->templateMap = $template_map;
  }

  /**
   * {@inheritdoc}
   */
  public function rewriteTemplateName($template_name, array $with, array $_context, $only) {
    return !empty($this->templateMap[$template_name]) ? $this->templateMap[$template_name] : $template_name;
  }

  /**
   * {@inheritdoc}
   */
  public function preprocessTemplateArgs($template_name, array $with, array $_context, $only) {
    return $with;
  }

}
