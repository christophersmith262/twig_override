<?php

namespace TwigOverride\Test;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophet;
use TwigOverride\TwigOverrideExtension;
use TwigOverride\Providers\ProviderInterface;

/**
 * Unit test for the TwigOverrideExtension class.
 *
 * @covers \TwigOverride\TwigOverrideExtension
 */
class TwigOverrideExtensionTest extends TestCase {

  /**
   * A prophecy prophet for creating mocks.
   *
   * @var \Prophecy\Prophet
   */
  private $prophet;

  /**
   * {@inheritdoc}
   */
  public function setup() {
    $this->prophet = new Prophet();
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    $this->prophet->checkPredictions();
  }

  /**
   * Tests trying to create a twig extension with invalid providers.
   *
   * @expectedException \InvalidArgumentException
   */
  public function testInvalidConstruction() {
    new TwigOverrideExtension(['a_string']);
  }

  /**
   * Tests that the override methods call the correct provider method.
   *
   * @param string $override_method
   *   The name of the method to call on the extension.
   * @param string $expected_call
   *   The name of the expected provider method to be called.
   *
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

  /**
   * Provides test cases for testOverrideMethods.
   *
   * @return array
   *   See PHPUnit docs for info about data providers.
   */
  public function paramProvider() {
    return [
      ['twigOverride', 'rewriteTemplateName'],
      ['twigOverrideParameters', 'preprocessTemplateArgs'],
    ];
  }

}
