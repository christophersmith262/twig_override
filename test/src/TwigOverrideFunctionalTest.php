<?php

namespace TwigOverride\Test;

use PHPUnit\Framework\TestCase;
use TwigOverride\Test\Providers\TestRewriteProvider;
use TwigOverride\TwigOverrideExtension;

/**
 * Tests the twig extension and node visitor on a real twig environment.
 */
class TwigOverrideFunctionalTest extends TestCase {

  /**
   * A map where keys are template names and values are template contents.
   *
   * This contains the "twig file" set that will be used for all tests.
   *
   * @var array
   */
  private $testFiles = [
    'static1' => 'test1 {{arg1}} {{arg2}}',
    'static2' => 'test2 {{arg1}} {{arg2}}',
    'include' => '{% include "static1" %}',
    'include_with' => '{% include "static1" with { arg2: "value2" } %}',
    'include_with_only' => '{% include "static1" with { arg2: "value2" } only %}',
    'embed' => '{% embed "static1" %}{% endembed %}',
    'embed_with' => '{% embed "static1" with { arg2: "value2" } %}{% endembed %}',
    'embed_with_only' => '{% embed "static1" with { arg2: "value2" } only %}{% endembed %}',
    'extends' => '{% extends "static1" %}',
  ];

  /**
   * Tests rendering a file with a given set of rewrites and execution context.
   *
   * @param string $file_name
   *   The twig template to render. This should be one of the keys in the
   *   $testFiles property.
   * @param bool $use_template_rewrites
   *   TRUE to test with template rewriting enabled, FALSE to disable.
   * @param bool $use_param_rewrites
   *   TRUE to test with param rewriting enabled, FALSE to disable.
   * @param string $expected_output
   *   The expected rendered twig result.
   *
   * @dataProvider provideTwigExamples
   */
  public function testTwigExamples($file_name, $use_template_rewrites, $use_param_rewrites, $expected_output) {
    $_context = ['arg1' => 'value1'];
    $file_rewrites = $use_template_rewrites ? ['static1' => 'static2'] : [];
    $param_rewrites = $use_template_rewrites ? ['arg2' => 'value3'] : [];
    $twig = $this->createTwig($file_rewrites, $param_rewrites);
    $actual_output = $twig->render($file_name, $_context);
    $this->assertEquals($expected_output, $actual_output);
  }

  /**
   * Provides test cases for testTwigExamples.
   *
   * @return array
   *   See PHPUnit docs for info about data providers.
   */
  public function provideTwigExamples() {
    return [
      ['include', FALSE, FALSE, 'test1 value1 '],
      ['include', TRUE, FALSE, 'test2 value1 '],
      ['include_with', FALSE, FALSE, 'test1 value1 value2'],
      ['include_with', TRUE, TRUE, 'test2 value1 value3'],
      ['include_with_only', FALSE, FALSE, 'test1  value2'],
      ['include_with_only', TRUE, TRUE, 'test2  value3'],
      ['embed', FALSE, FALSE, 'test1 value1 '],
      ['embed', TRUE, FALSE, 'test2 value1 '],
      ['embed_with', FALSE, FALSE, 'test1 value1 value2'],
      ['embed_with', TRUE, TRUE, 'test2 value1 value3'],
      ['embed_with_only', FALSE, FALSE, 'test1  value2'],
      ['embed_with_only', TRUE, TRUE, 'test2  value3'],
      ['extends', FALSE, FALSE, 'test1 value1 '],
      ['extends', TRUE, FALSE, 'test2 value1 '],
    ];
  }

  /**
   * Creates a live twig execution environment for testing.
   *
   * @param array $file_rewrites
   *   A map where keys are 'from' file names, and values are 'to' file names.
   * @param array $param_rewrites
   *   A map where keys are 'from' param names, and values are 'to' param names.
   *
   * @return \Twig_Environment
   *   The generated twig execution environment.
   */
  protected function createTwig(array $file_rewrites, array $param_rewrites) {
    $twig = new \Twig_Environment(new \Twig_Loader_Array($this->testFiles));
    $twig->addExtension(new TwigOverrideExtension([
      new TestRewriteProvider($file_rewrites, $param_rewrites),
    ]));
    return $twig;
  }

}
