<?php

namespace TwigOverride\Test;

use Prophecy\Prophet;
use TwigOverride\TwigOverrideExtension;
use TwigOverride\TwigOverrideNodeVisitor;

class TwigOverrideNodeVisitorTest extends \PHPUnit\Framework\TestCase {

  private $twigEnv;
  private $visitor;

  public function setup() {
    $prophet = new Prophet();
    $this->twigEnv = $prophet->prophesize('\Twig_Environment')->reveal();
    $this->visitor = new TwigOverrideNodeVisitor();
  }

  /**
   * @dataProvider nodeProvider
   */
  public function testRewriteTemplateName($node_type, $template_name, array $with = NULL, $only = NULL) {
    if ($node_type == 'module') {
      $node = $this->createModule($template_name);
      $check_template_node = 'parent';
      $check_variables_node = NULL;
    }
    elseif ($node_type == 'include') {
      $node = $this->createInclude($template_name, $with, $only);
      $check_template_node = 'expr';
      $check_variables_node = 'variables';
    }
    elseif ($node_type == 'embed') {
      $node = $this->createEmbed($template_name, $with, $only);
      $check_template_node = NULL;
      $check_variables_node = 'variables';
    }
    $result_node = $this->visitor->leaveNode($node, $this->twigEnv);

    $expected_args = new \Twig_Node([
      new \Twig_Node_Expression_Constant($template_name, 1),
      new \Twig_Node_Expression_Constant(!!$only, 1),
      $with ? new \Twig_Node_Expression_Constant($with, 1) : new \Twig_Node_Expression_Constant(NULL, 1),
      new \Twig_Node_Expression_Name('_context', 1),
    ]);

    if (isset($check_template_node)) {
      $this->assertCall(TwigOverrideExtension::TEMPLATE_OVERRIDE_FUNCTION, $expected_args, $result_node->getNode($check_template_node));
    }

    if (isset($check_variables_node)) {
      $this->assertCall(TwigOverrideExtension::PARAMETER_OVERRIDE_FUNCTION, $expected_args, $result_node->getNode($check_variables_node));
    }
  }

  public function nodeProvider() {
    return [
      ['module', '@test/test.twig'],
      ['include', '@test/test2.twig', ['withArgs' => TRUE], TRUE],
      ['include', '@test/test3.twig', ['withArgs' => TRUE], FALSE],
      ['include', '@test/test3.twig', [], FALSE],
      ['embed', '@test/test4.twig', ['withArgs' => TRUE], TRUE],
      ['embed', '@test/test5.twig', ['withArgs' => TRUE], FALSE],
      ['embed', '@test/test6.twig', [], FALSE],
    ];
  }

  protected function assertCall($expected_name, \Twig_Node $expected_args, \Twig_Node $node) {
    $this->assertInstanceOf(\Twig_Node_Expression_Function::CLASS, $node);
    $this->assertEquals($expected_name, $node->getAttribute('name'));
    $this->assertTrue($node->hasNode('arguments'));
    $this->assertEquals((string)$expected_args, (string)$node->getNode('arguments'));
  }

  protected function createModule($template_name) {
    return new \Twig_Node_Module(
      new \Twig_Node(),
      new \Twig_Node_Expression_Constant($template_name, 1),
      new \Twig_Node(),
      new \Twig_Node(),
      new \Twig_Node(),
      NULL,
      new \Twig_Source('', $template_name)
    );
  }

  protected function createInclude($template_name, array $with, $only) {
    return new \Twig_Node_Include(
      new \Twig_Node_Expression_Constant($template_name, 1),
      $with ? new \Twig_Node_Expression_Constant($with, 1) : NULL,
      $only,
      FALSE,
      0
    );
  }

  protected function createEmbed($template_name, array $with, $only) {
    return new \Twig_Node_Embed(
      $template_name,
      0,
      $with ? new \Twig_Node_Expression_Constant($with, 1) : NULL,
      $only,
      FALSE,
      0
    );
  }

}

