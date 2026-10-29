# `PropertyHooks\NoReturnByReferenceRule`

This rule reports an error when a [property hook](https://www.php.net/manual/en/language.oop5.property-hooks.php) returns by reference.

## Rationale

Returning by reference lets the caller change state that the property hook keeps, which makes the code harder to follow. Return a value instead.

## Examples

### Reported

```php
<?php

declare(strict_types=1);

namespace Example;

final class Greetings
{
    private array $values = [];

    public array $salutations {
        &get => $this->values;
    }
}
```

### Not reported

```php
<?php

declare(strict_types=1);

namespace Example;

final class Greetings
{
    private array $values = [];

    public array $salutations {
        get => $this->values;
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
		noReturnByReference:
			enabled: false
```

💡 The `enabled` parameter for `noReturnByReference` also enables or disables the following rules:

- [`Functions\NoReturnByReferenceRule`](../../../doc/rules/Functions/NoReturnByReferenceRule.md)
- [`Methods\NoReturnByReferenceRule`](../../../doc/rules/Methods/NoReturnByReferenceRule.md)
