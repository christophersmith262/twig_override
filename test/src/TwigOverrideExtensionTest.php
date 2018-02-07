<?php

namespace TwigOverride\Test;

use Prophecy\Prophet;
use TwigOverride\TwigOverrideExtension;
use TwigOverride\Providers\ProviderInterface;

class TwigOverrideExtensionTest extends \PHPUnit\Framework\TestCase {

  private $prophet;

  public function setup() {
    $this->prophet = new Prophet();
  }

  public function tearDown() {
    $this->prophet->checkPredictions();
  }

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testInvalidConstruction() {
    new TwigOverrideExtension(['a_string']);
  }

  /**
   * @dataProvider paramProvider
   */
  public function testOverrideMethods($override_method, $expected_call) {
    $template_name = 'test.twig';
    $only = TRUE;
    $expected_return = 'return_value';
    $provider = $this->prophet->prophesize(ProviderInterface::CLASS);
    $provider->{$expected_call}($template_name, [], [], $only)
      ->willReturn($expected_return)
      ->shouldBeCalledTimes(1);

    $twig_extension = new TwigOverrideExtension([$provider->reveal()]);
    $actual_return = $twig_extension->{$override_method}($template_name, $only);
    $this->assertEquals($expected_return, $actual_return);
  }

  public function paramProvider() {
    return [
      ['twigOverride', 'rewriteTemplateName'],
      ['twigOverrideParameters', 'preprocessTemplateArgs'],
    ];
  }

}
