# `Methods\NoParameterPassedByReferenceRule`

This rule reports an error when a method has a parameter that is [passed by reference](https://www.php.net/manual/en/language.references.pass.php).

## Rationale

A parameter passed by reference lets a method change a variable of its caller as a side effect, which makes the code harder to follow. Return the new value instead.

## Examples

### Reported

```php
<?php

declare(strict_types=1);

namespace Example;

final class Greeting
{
    public function increment(int &$value): void
    {
        ++$value;
    }
}
```

### Not reported

```php
<?php

declare(strict_types=1);

namespace Example;

final class Greeting
{
    public function increment(int $value): int
    {
        return $value + 1;
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
		noParameterPassedByReference:
			enabled: false
```

💡 The `enabled` parameter for `noParameterPassedByReference` also enables or disables the following rules:

- [`ArrowFunctions\NoParameterPassedByReferenceRule`](../../../doc/rules/ArrowFunctions/NoParameterPassedByReferenceRule.md)
- [`Closures\NoParameterPassedByReferenceRule`](../../../doc/rules/Closures/NoParameterPassedByReferenceRule.md)
- [`Functions\NoParameterPassedByReferenceRule`](../../../doc/rules/Functions/NoParameterPassedByReferenceRule.md)
