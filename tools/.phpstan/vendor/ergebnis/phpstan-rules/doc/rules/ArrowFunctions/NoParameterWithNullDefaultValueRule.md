# `ArrowFunctions\NoParameterWithNullDefaultValueRule`

This rule reports an error when an arrow function has a parameter with `null` as default value.

## Rationale

A parameter with `null` as its default value makes the argument optional and nullable at once, so the arrow function has to handle a missing value. Separate arrow functions or a null object make each case explicit.

## Examples

### Reported

```php
<?php

declare(strict_types=1);

$toUpperCase = static fn ($value = null): string => \strtoupper((string) $value);
```

### Not reported

```php
<?php

declare(strict_types=1);

$toUpperCase = static fn (string $value): string => \strtoupper($value);
```

## Parameters

### `enabled`

By default, this rule is enabled, unless you set the [`allRules`](../../../README.md#disabling-all-rules) parameter to `false`.

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		noParameterWithNullDefaultValue:
			enabled: false
```

💡 The `enabled` parameter for `noParameterWithNullDefaultValue` also enables or disables the following rules:

- [`Closures\NoParameterWithNullDefaultValueRule`](../../../doc/rules/Closures/NoParameterWithNullDefaultValueRule.md)
- [`Functions\NoParameterWithNullDefaultValueRule`](../../../doc/rules/Functions/NoParameterWithNullDefaultValueRule.md)
- [`Methods\NoParameterWithNullDefaultValueRule`](../../../doc/rules/Methods/NoParameterWithNullDefaultValueRule.md)
