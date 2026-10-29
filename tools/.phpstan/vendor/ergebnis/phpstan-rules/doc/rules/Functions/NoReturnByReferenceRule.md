# `Functions\NoReturnByReferenceRule`

This rule reports an error when a function [returns by reference](https://www.php.net/manual/en/language.references.return.php).

## Rationale

Returning by reference lets the caller change state that the function keeps, which makes the code harder to follow. Return a value instead.

## Examples

### Reported

```php
<?php

declare(strict_types=1);

function &values(): array
{
    static $values = [];

    return $values;
}
```

### Not reported

```php
<?php

declare(strict_types=1);

function values(): array
{
    return [];
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

- [`Methods\NoReturnByReferenceRule`](../../../doc/rules/Methods/NoReturnByReferenceRule.md)
- [`PropertyHooks\NoReturnByReferenceRule`](../../../doc/rules/PropertyHooks/NoReturnByReferenceRule.md)
