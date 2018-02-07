<?php

namespace TwigOverride;

use TwigOverride\Providers\ProviderInterface;

/**
 * The twig extension that provides the dynamic filtering functionality.
 */
class TwigOverrideExtension extends \Twig_Extension {

  /**
   * The name of the function to provide for template overrides.
   *
   * @var string
   */
  const TEMPLATE_OVERRIDE_FUNCTION = '_twig_override';

  /**
   * The name of the function to provide for parameter overrides.
   *
   * @var string
   */
  const PARAMETER_OVERRIDE_FUNCTION = '_twig_override_parameters';

  /**
   * A list of override providers to apply.
   *
   * @var \TwigOverride\Providers\ProviderInterface[]
   */
  protected $providers;

  /**
   * Creates a twig override extension.
   *
   * @param \TwigOverride\Providers\ProviderInterface[] $providers
   *   A list of override providers that template names / args will be filtered
   *   through.
   */
  public function __construct(array $providers) {
    foreach ($providers as $provider) {
      if (!$provider instanceof ProviderInterface) {
        throw new \InvalidArgumentException(
          'One or more providers provided to \TwigOverride\TwigOverrideExtension does not implement \TwigOverride\Providers\ProviderInterface.'
        );
      }
    }
    $this->providers = $providers;
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new \Twig_SimpleFunction(static::TEMPLATE_OVERRIDE_FUNCTION, [$this, 'twigOverride']),
      new \Twig_SimpleFunction(static::PARAMETER_OVERRIDE_FUNCTION, [$this, 'twigOverrideParameters']),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getNodeVisitors() {
    return [
      new TwigOverrideNodeVisitor(),
    ];
  }

  /**
   * Overrides one twig template name with another.
   *
   * @param string $template_name
   *   The initial template name to be potentially overridden.
   * @param bool $only
   *   Whether or not the only flag is set.
   * @param array|null $with
   *   The variables that were passed in the "with" statement.
   * @param array|null $_context
   *   The current twig _context variable where the template is being requested.
   *
   * @return string
   *   The template name after being filtered through the overwrite providers.
   */
  public function twigOverride($template_name, $only, array $with = NULL, array $_context = NULL) {
    $with = isset($with) ? $with : [];
    $_context = isset($_context) ? $_context : [];
    foreach ($this->providers as $provider) {
      $template_name = $provider->rewriteTemplateName($template_name, $with, $_context, $only);
    }
    return $template_name;
  }

  /**
   * Overrides the 'with' arguments passed to a twig template.
   *
   * @param string $template_name
   *   The initial template name the arguments are being passed to.
   * @param bool $only
   *   Whether or not the only flag is set.
   * @param array|null $with
   *   The variables that were passed in the "with" statement.
   * @param array|null $_context
   *   The current twig _context variable where the template is being requested.
   *
   * @return string
   *   The 'with' arguments after being filtered through the overwrite
   *   providers.
   */
  public function twigOverrideParameters($template_name, $only, array $with = NULL, array $_context = NULL) {
    $with = isset($with) ? $with : [];
    $_context = isset($_context) ? $_context : [];
    foreach ($this->providers as $provider) {
      $with = $provider->preprocessTemplateArgs($template_name, $with, $_context, $only);
    }
    return $with;
  }

}
