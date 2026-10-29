# `Classes\NoExtendsRule`

This rule reports an error when a class extends another class.

## Rationale

Extending a class couples the subclass to the implementation of its parent class. Prefer composition: inject collaborators and implement interfaces.

## Examples

### Reported

```php
<?php

declare(strict_types=1);

namespace Example;

abstract class AbstractGreeting
{
}

final class Greeting extends AbstractGreeting
{
}
```

### Not reported

```php
<?php

declare(strict_types=1);

namespace Example;

interface Greeting
{
}

final class FriendlyGreeting implements Greeting
{
}
```

## Parameters

### `enabled`

By default, this rule is enabled, unless you set the [`allRules`](../../../README.md#disabling-all-rules) parameter to `false`.

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		noExtends:
			enabled: false
```

### `classesAllowedToBeExtended`

By default, this rule allows the following classes to be extended:

- [`PHPUnit\Framework\TestCase`](https://github.com/sebastianbergmann/phpunit/blob/6.0.0/src/Framework/TestCase.php)

You can set the `classesAllowedToBeExtended` parameter to a list of class names that you want to allow to be extended.

Default value: `[]`

Example:

```neon
parameters:
	ergebnis:
		noExtends:
			classesAllowedToBeExtended:
				- Ergebnis\PHPStan\Rules\Test\Integration\AbstractTestCase
				- PHPStan\Testing\RuleTestCase
```
