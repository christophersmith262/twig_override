<?php

namespace TwigOverride\Providers;

/**
 * The interface that override providers should implement.
 */
interface ProviderInterface {

  /**
   * Filters a given template name and returns the filtered template name.
   *
   * @param string $template_name
   *   The template name to be filtered.
   * @param array $with
   *   The 'with' arguments being passed to the template.
   * @param array $_context
   *   The current _context twig variable.
   * @param bool $only
   *   Whether or not the only flag is set on the include or embed.
   *
   * @return string
   *   The filtered template name that will be included.
   */
  public function rewriteTemplateName($template_name, array $with, array $_context, $only);

  /**
   * Filters a given set of 'with' arguments being passed into a template.
   *
   * @param string $template_name
   *   The name of the template the args arg getting passed into.
   * @param array $with
   *   The 'with' arguments to filter.
   * @param array $_context
   *   The current _context twig variable.
   * @param bool $only
   *   Whether or not the only flag is set on the include or embed.
   *
   * @return array
   *   The filtered arguments to be passed into the template.
   */
  public function preprocessTemplateArgs($template_name, array $with, array $_context, $only);

}
