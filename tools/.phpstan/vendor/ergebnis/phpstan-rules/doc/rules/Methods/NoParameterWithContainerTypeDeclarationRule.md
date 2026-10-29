# `Methods\NoParameterWithContainerTypeDeclarationRule`

This rule reports an error when a method has a type declaration for a known dependency injection container or service locator.

## Rationale

A method that receives a dependency injection container or service locator hides its actual dependencies. Inject the dependencies directly instead.

## Examples

### Reported

```php
<?php

declare(strict_types=1);

namespace Example;

use Psr\Container;

final class Greeting
{
    public function greet(Container\ContainerInterface $container): string
    {
        return $container->get('salutation');
    }
}
```

### Not reported

```php
<?php

declare(strict_types=1);

namespace Example;

final class Salutation
{
    public function toString(): string
    {
        return 'Hello';
    }
}

final class Greeting
{
    public function greet(Salutation $salutation): string
    {
        return $salutation->toString();
    }
}
```

## Parameters

### `enabled`

By default, this rule is enabled, unless you set the [`allRules`](../../../README.md#disabling-all-rules) parameter to `false`.

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		noParameterWithContainerTypeDeclaration:
			enabled: false
```

### `interfacesImplementedByContainers`

By default, this rule disallows the use of type declarations indicating an implementation of

- [`Psr\Container\ContainerInterface`](https://github.com/php-fig/container/blob/1.0.0/src/ContainerInterface.php)

is expected to be injected into a method.

You can set the `interfacesImplementedByContainers` parameter to a list of interface names of additional containers and service locators.

Default value: `[Psr\Container\ContainerInterface]`

Example:

```neon
parameters:
	ergebnis:
		noParameterWithContainerTypeDeclaration:
			interfacesImplementedByContainers:
				- Fancy\DependencyInjection\ContainerInterface
				- Other\ServiceLocatorInterface
```

### `methodsAllowedToUseContainerTypeDeclarations`

You can set the `methodsAllowedToUseContainerTypeDeclarations` parameter to a list of method names that are allowed to use parameters with container type declarations.

Default value: `[]`

Example:

```neon
parameters:
	ergebnis:
		noParameterWithContainerTypeDeclaration:
			methodsAllowedToUseContainerTypeDeclarations:
				- loadExtension
```
