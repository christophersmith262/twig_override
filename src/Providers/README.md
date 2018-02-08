[![Build Status](https://travis-ci.org/christophersmith262/twig_override.svg?branch=2.x)](https://travis-ci.org/christophersmith262/twig_override)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/christophersmith262/twig_override/badges/quality-score.png?b=2.x)](https://scrutinizer-ci.com/g/christophersmith262/twig_override/?branch=2.x)
[![Code Coverage](https://scrutinizer-ci.com/g/christophersmith262/twig_override/badges/coverage.png?b=2.x)](https://scrutinizer-ci.com/g/christophersmith262/twig_override/?branch=2.x)

# TwigOverride

TwigOverride provides a [twig extension](https://twig.symfony.com/doc/2.x/advanced.html#creating-an-extension) that can help with:

- Preprocessing the arguments passed to a twig template anywhere that template
  is referenced.
- Dynamically rewriting `include` / `embed` / `extends` to reference a different
  template based on run-time context.

## Installation

TwigOverride supports twig 1.x and twig 2.x.

```
composer require christophersmith262/twig_override
```

## Performing Simple Template Overrides

A basic override provider `TwigOverride\Providers\SimpleRewriteProvider` is
included to perform template file swapping. For example, if I wanted to replace
every occurrence of:

```
{% include "template1.twig" %}
```

with

```
{% include "template2.twig %}
```

### By Manually Adding the Extension

If you are manully creating the twig environment, you can call the
`addExtension` method to directly add the plugin:

```

use TwigOverride\TwigOverrideExtension;
use TwigOverride\Providers\SimpleRewriteProvider;

$twig = new \Twig_Environment(new \Twig_Loader_Array([
  'template1.twig' => 'test1',
  'template2.twig' => 'test2',
  'template3.twig' => '{% include "template1.twig" %}
]));

$twig->addExtension(new TwigOverrideExtension([
  new SimpleRewriteProvider([
    'template1.twig' => 'template2.twig',
  ]),
]);

// Outputs 'test2'.
$twig->render('template3.twig');
```

### Using Twig as a Symfony Service

If you are using [the standard symfony services.yaml](https://symfony.com/doc/current/service_container.html#service-container-services-load-example), then you can simply add the extension as a service:

```
services:
	twig_override.extension:
		class: TwigOverride\TwigOverrideExtension
    	arguments: [['@twig_override.provider.simple']]
    	tags: ['twig.extension']
    
	twig_override.providers.simple:
		class: TwigOverride\Providers\SimpleRewriteProvider
    	arguments: ['%twig_override.simple_mappings%']
        
parameters:
	twig_override.simple_mappings:
    	'template1.twig': 'template2.twig'
```

## Advanced Rewrites with a Custom Provider

In the example below we'll create a provider that dynamically resolves a custom
'profile' template for a user based on the user id, and decorates the arguments
passed to any template that includes a 'user_id' with a 'user_name' argument.

To do this we'll first creat a provider class that will need access to the twig loader:

```
use TwigOverride\Providers\ProviderInterface;

class AdvancedRewriteProvider implements ProviderInterface {

  private $users = [1 => 'Admin', 2 => 'Guest'];
  private $loader;
  
  public function __construct(\Twig_LoaderInterface $loader) {
    $this->loader = $loader;
  }
  
  /**
   * Overrides 'profile.twig' with 'profile--<uid>.twig' if the template exists.
   */
  public function rewriteTemplateName($template_name, array $with, array $_context, $only) {
    if ($template_name == 'profile.twig') {
      $uid = $this->getUid($with, $_context);
      $override = 'profile--' . $uid . '.twig';
      
      if ($this->loader->exists($override)) {
        return $override;
      }
    }
    
    return $template_name;
  }
  
  /**
   * Sets a 'user_name' parameter when 'user_id' is passed to a twig template.
   */
  public function preprocessTemplateArgs($template_name, array $with, array $_context, $only) {
    $uid = $this->getUid($with, $_context);
    
    if (!empty($this->users[$uid])) {
      $with['user_name'] = $this->users[$uid];
    }
    
    return $with;
  }
  
  protected getUid(array $with, array $_context) {
    if (!empty($with['user_id'])) {
      return $with['user_id'];
    elseif (!empty($_context['user_id'] && !$only) {
      return $_context['user_id'];
    }
    else {
      return NULL;
    }
  }
  
}
```

Assuming we are using a twig in a symfony container environment, we can expose
the provider in the services.yaml.  Note that in this example the provider needs
the twig loader service as an argument.

```
services:
	twig_override.extension:
		class: TwigOverride\TwigOverrideExtension
    	arguments: [['@twig_override.provider.advanced']]
    	tags: ['twig.extension']
        
	twig_override.providers.advanced:
		class: TwigOverride\Providers\AdvancedRewriteProvider
    	arguments: ['@twig.loader']
```
