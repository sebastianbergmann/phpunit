# `ArrowFunctions\NoNullableReturnTypeDeclarationRule`

This rule reports an error when an arrow function uses a nullable return type declaration.

## Rationale

A nullable return type forces every caller to handle `null`. Throwing an exception, returning a null object, or splitting the arrow function in two keeps the callers simpler.

## Examples

### Reported

```php
<?php

declare(strict_types=1);

$toUpperCase = static fn (string $value): ?string => \strtoupper($value);
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
		noNullableReturnTypeDeclaration:
			enabled: false
```

💡 The `enabled` parameter for `noNullableReturnTypeDeclaration` also enables or disables the following rules:

- [`Closures\NoNullableReturnTypeDeclarationRule`](../../../doc/rules/Closures/NoNullableReturnTypeDeclarationRule.md)
- [`Functions\NoNullableReturnTypeDeclarationRule`](../../../doc/rules/Functions/NoNullableReturnTypeDeclarationRule.md)
- [`Methods\NoNullableReturnTypeDeclarationRule`](../../../doc/rules/Methods/NoNullableReturnTypeDeclarationRule.md)
