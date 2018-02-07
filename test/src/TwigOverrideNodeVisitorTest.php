<?php

namespace TwigOverride\Test;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophet;
use TwigOverride\TwigOverrideExtension;
use TwigOverride\TwigOverrideNodeVisitor;

/**
 * Unit test for the TwigOverrideNodeVisitor class.
 *
 * @covers \TwigOverride\TwigOverrideNodeVisitor
 */
class TwigOverrideNodeVisitorTest extends TestCase {

  /**
   * A mock twig environment to pass to the visitor methods.
   *
   * We don't need a real twig environment since the class doesn't use it.
   *
   * @var \Twig_Environment
   */
  private $twigEnv;

  /**
   * The visitor object to test.
   *
   * Since this object is stateless, we only need to create it once.
   *
   * @var \TwigOverride\TwigOverrideNodeVisitor
   */
  private $visitor;

  /**
   * {@inheritdoc}
   */
  public function setup() {
    $prophet = new Prophet();
    $this->twigEnv = $prophet->prophesize('\Twig_Environment')->reveal();
    $this->visitor = new TwigOverrideNodeVisitor();
  }

  /**
   * Tests the leaveNode method.
   *
   * @param string $node_type
   *   A 'module', 'include', or 'embed' to specify which type of node to test.
   * @param string $template_name
   *   The name of the template to test.
   * @param array|null $with
   *   Optional 'with' variables to pass to include or embed.
   * @param bool $only
   *   Optional 'only' flag to pass to include or embed.
   *
   * @dataProvider nodeProvider
   */
  public function testLeaveNode($node_type, $template_name, array $with = NULL, $only = NULL) {
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
    else {
      throw new \InvalidArgumentException('Invalid Node Type');
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

  /**
   * A test case provider for testLeaveNode.
   *
   * @return array
   *   See PHPUnit docs for info about data providers.
   */
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

  /**
   * A helper function for asserting that a twig call was injected correctly.
   *
   * @param string $expected_name
   *   The name of the function expected to be called.
   * @param \Twig_Node $expected_args
   *   The arguments that were expected to be passed as a twig node subtree.
   * @param \Twig_Node $node
   *   The node to test against.
   */
  protected function assertCall($expected_name, \Twig_Node $expected_args, \Twig_Node $node) {
    $this->assertInstanceOf(\Twig_Node_Expression_Function::CLASS, $node);
    $this->assertEquals($expected_name, $node->getAttribute('name'));
    $this->assertTrue($node->hasNode('arguments'));
    $this->assertEquals((string) $expected_args, (string) $node->getNode('arguments'));
  }

  /**
   * Creates a Twig_Node_Module for a template.
   *
   * This simulates a twig module loaded as a result of a {% embed ... %} or
   * {% include ... %}.
   *
   * @return \Twig_Node_Module
   *   The simulated module node.
   */
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

  /**
   * Creates a Twig_Node_Include for a template.
   *
   * This simulates a {% include ... %}.
   *
   * @return \Twig_Node_Include
   *   The simulated include node.
   */
  protected function createInclude($template_name, array $with = NULL, $only) {
    return new \Twig_Node_Include(
      new \Twig_Node_Expression_Constant($template_name, 1),
      $with ? new \Twig_Node_Expression_Constant($with, 1) : NULL,
      $only,
      FALSE,
      0
    );
  }

  /**
   * Creates a Twig_Node_Embed for a template.
   *
   * This simulates a {% embed ... %}.
   *
   * @return \Twig_Node_Embed
   *   The simulated embed node.
   */
  protected function createEmbed($template_name, array $with = NULL, $only) {
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
