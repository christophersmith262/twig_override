<?php

namespace TwigOverride\Test;

use TwigOverride\Test\Providers\TestRewriteProvider;
use TwigOverride\TwigOverrideExtension;

class TwigOverrideFunctionalTest extends \PHPUnit\Framework\TestCase {

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
   * @dataProvider provideTwigExamples
   */
  public function testTwigExamples($file_name, array $file_rewrites, array $param_rewrites, array $_context, $expected_output) {
    $twig = $this->createTwig($file_rewrites, $param_rewrites);
    $actual_output = $twig->render($file_name, $_context);
    $this->assertEquals($expected_output, $actual_output);
  }

  public function provideTwigExamples() {
    return [
      ['include', [], [], ['arg1' => 'value1'], 'test1 value1 '],
      ['include', ['static1' => 'static2'], [], ['arg1' => 'value1'], 'test2 value1 '],
      ['include_with', [], [], ['arg1' => 'value1'], 'test1 value1 value2'],
      ['include_with', ['static1' => 'static2' ], [ 'arg2' => 'value3'], ['arg1' => 'value1'], 'test2 value1 value3'],
      ['include_with_only', [], [], ['arg1' => 'value1'], 'test1  value2'],
      ['include_with_only', ['static1' => 'static2' ], [ 'arg2' => 'value3'], ['arg1' => 'value1'], 'test2  value3'],
      ['embed', [], [], ['arg1' => 'value1'], 'test1 value1 '],
      ['embed', ['static1' => 'static2'], [], ['arg1' => 'value1'], 'test2 value1 '],
      ['embed_with', [], [], ['arg1' => 'value1'], 'test1 value1 value2'],
      ['embed_with', ['static1' => 'static2' ], [ 'arg2' => 'value3'], ['arg1' => 'value1'], 'test2 value1 value3'],
      ['embed_with_only', [], [], ['arg1' => 'value1'], 'test1  value2'],
      ['embed_with_only', ['static1' => 'static2' ], [ 'arg2' => 'value3'], ['arg1' => 'value1'], 'test2  value3'],
      ['extends', [], [], ['arg1' => 'value1'], 'test1 value1 '],
      ['extends', ['static1' => 'static2'], [], ['arg1' => 'value1'], 'test2 value1 '],
    ];
  }

  protected function createTwig(array $file_rewrites, array $param_rewrites) {
    $twig = new \Twig_Environment(new \Twig_Loader_Array($this->testFiles));
    $twig->addExtension(new TwigOverrideExtension([
      new TestRewriteProvider($file_rewrites, $param_rewrites),
    ]));
    return $twig;
  }

}
