<?php

namespace TwigOverride;

/**
 * Twig parse node visitor to dynamically rewrite include / embed / extends.
 */
class TwigOverrideNodeVisitor extends \Twig_BaseNodeVisitor {

  /**
   * {@inheritdoc}
   */
  protected function doEnterNode(\Twig_Node $node, \Twig_Environment $env) {
    return $node;
  }

  /**
   * {@inheritdoc}
   */
  protected function doLeaveNode(\Twig_Node $node, \Twig_Environment $env) {

    // Replace:
    // {% embed ... %} with {% embed twig_override(...) %}
    // and:
    // {% extends ... %} with {% extends twig_override(...) %}
    if ($node instanceof \Twig_Node_Module && $node->hasNode('parent')) {
      $line = $node->getLine();
      $with = new \Twig_Node_Expression_Constant(NULL, $line);
      $template_name = $node->getNode('parent');
      $only = new \Twig_Node_Expression_Constant(FALSE, $line);
      $_context = new \Twig_Node_Expression_Name('_context', $line);
      $arguments = new \Twig_Node([$template_name, $only, $with, $_context]);
      $node->setNode('parent', new \Twig_Node_Expression_Function(TwigOverrideExtension::TEMPLATE_OVERRIDE_FUNCTION, $arguments, $line));
    }

    // Replace
    // {% include 1 with 2 %} with {% include twig_override(1) with twig_override_parameters(2) %}
    // and 
    // {% embed 1 with 2 %} with {% embed 1 with twig_override_parameters(2) %}
    else if ($node instanceof \Twig_Node_Include) {
      $line = $node->getLine();
      $with = $node->hasNode('variables') ? $node->getNode('variables') : new \Twig_Node_Expression_Constant(NULL, $line);
      $only = new \Twig_Node_Expression_Constant($node->hasAttribute('only') ? $node->getAttribute('only') : FALSE, $line);
      $_context = new \Twig_Node_Expression_Name('_context', $line);

      // The order of these checks is important since Twig_Node_Embed is a
      // subclass of Twig_Node_Include.
      if ($node instanceof \Twig_Node_Embed) {
        $template_name = new \Twig_Node_Expression_Constant($node->getAttribute('filename'), $line);
        $arguments = new \Twig_Node([$template_name, $only, $with, $_context]);
      }
      else {
        $template_name = $node->getNode('expr');
        $arguments = new \Twig_Node([$template_name, $only, $with, $_context]);
        $node->setNode('expr', new \Twig_Node_Expression_Function(TwigOverrideExtension::TEMPLATE_OVERRIDE_FUNCTION, $arguments, $line));
      }

      $node->setNode('variables', new \Twig_Node_Expression_Function(TwigOverrideExtension::PARAMETER_OVERRIDE_FUNCTION, $arguments, $line));
    }

    return $node;
  }

  /**
   * {@inheritdoc}
   */
  public function getPriority() {
    // Just above the Optimizer, which is the normal last one.
    return 256;
  }

}
