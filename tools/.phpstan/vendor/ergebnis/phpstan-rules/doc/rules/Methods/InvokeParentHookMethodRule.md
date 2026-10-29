# `Methods\InvokeParentHookMethodRule`

This rule reports an error when a hook method that overrides a hook method in a parent class does not invoke the overridden hook method in the expected order.

## Rationale

A hook method such as `setUp()` that overrides a hook method of a parent class needs to invoke the parent hook method in the expected order, or the parent class does not set up or tear down as intended.

## Examples

### Reported

```php
<?php

declare(strict_types=1);

namespace Example\Test;

use PHPUnit\Framework;

final class GreetingTest extends Framework\TestCase
{
    protected function setUp(): void
    {
        \putenv('GREETING=Hello');

        parent::setUp();
    }
}
```

### Not reported

```php
<?php

declare(strict_types=1);

namespace Example\Test;

use PHPUnit\Framework;

final class GreetingTest extends Framework\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        \putenv('GREETING=Hello');
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
		invokeParentHookMethod:
			enabled: false
```

### `hookMethods`

By default, this rule requires the following hook methods to be invoked before doing something in the overriding method:

- [`Codeception\PHPUnit\TestCase::_setUp()`](https://github.com/Codeception/phpunit-wrapper/blob/9.0.0/src/TestCase.php#L11-L13)
- [`Codeception\PHPUnit\TestCase::_setUpBeforeClass()`](https://github.com/Codeception/phpunit-wrapper/blob/9.0.0/src/TestCase.php#L25-L27)
- [`Codeception\Test\Unit::_before()`](https://github.com/Codeception/Codeception/blob/4.2.2/src/Codeception/Test/Unit.php#L63-L65)
- [`Codeception\Test\Unit::_setUp()`](https://github.com/Codeception/Codeception/blob/4.2.2/src/Codeception/Test/Unit.php#L34-L58)
- [`PHPUnit\Framework\TestCase::assertPreConditions()`](https://github.com/sebastianbergmann/phpunit/blob/6.0.0/src/Framework/TestCase.php#L2073-L2075)
- [`PHPUnit\Framework\TestCase::setUp()`](https://github.com/sebastianbergmann/phpunit/blob/6.0.0/src/Framework/TestCase.php#L2063-L2065)
- [`PHPUnit\Framework\TestCase::setUpBeforeClass()`](https://github.com/sebastianbergmann/phpunit/blob/6.0.0/src/Framework/TestCase.php#L2055-L2057)

By default, this rule requires the following hook methods to be invoked after doing something in the overriding method:

- [`Codeception\PHPUnit\TestCase::_tearDown()`](https://github.com/Codeception/phpunit-wrapper/blob/9.0.0/src/TestCase.php#L18-L20)
- [`Codeception\PHPUnit\TestCase::_tearDownAfterClass()`](https://github.com/Codeception/phpunit-wrapper/blob/9.0.0/src/TestCase.php#L32-L34)
- [`Codeception\Test\Unit::_after()`](https://github.com/Codeception/Codeception/blob/4.2.2/src/Codeception/Test/Unit.php#L75-L77)
- [`Codeception\Test\Unit::_tearDown()`](https://github.com/Codeception/Codeception/blob/4.2.2/src/Codeception/Test/Unit.php#L67-L70)
- [`PHPUnit\Framework\TestCase::assertPostConditions()`](https://github.com/sebastianbergmann/phpunit/blob/6.0.0/src/Framework/TestCase.php#L2083-L2085)
- [`PHPUnit\Framework\TestCase::tearDown()`](https://github.com/sebastianbergmann/phpunit/blob/6.0.0/src/Framework/TestCase.php#L2091-L2093)
- [`PHPUnit\Framework\TestCase::tearDownAfterClass()`](https://github.com/sebastianbergmann/phpunit/blob/6.0.0/src/Framework/TestCase.php#L2098-L2100)

You can set the `hookMethods` parameter to a list of additional hook methods, where each hook method has the following keys:

- `className`: name of the class that declares the hook method
- `hasContent`: one of `"yes"`, `"no"`
- `invocation`: one of `"any"` (needs to be invoked), `"first"` (needs to be invoked before all other statements in the overriding hook method), `"last"` (needs to be invoked after all other statements in the overriding hook method)
- `methodName`: name of the hook method

Default value: `[]`

Example:

```neon
parameters:
	ergebnis:
		invokeParentHookMethod:
			hookMethods:
				-
					className: Example\Test\Functional\AbstractCest
					hasContent: 'yes'
					invocation: first
					methodName: _before
```
