# `Functions\NoNullableReturnTypeDeclarationRule`

This rule reports an error when a function uses a nullable return type declaration.

## Rationale

A nullable return type forces every caller to handle `null`. Throwing an exception, returning a null object, or splitting the function in two keeps the callers simpler.

## Examples

### Reported

```php
<?php

declare(strict_types=1);

function toUpperCase(string $value): ?string
{
    return \strtoupper($value);
}
```

### Not reported

```php
<?php

declare(strict_types=1);

function toUpperCase(string $value): string
{
    return \strtoupper($value);
}
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

- [`ArrowFunctions\NoNullableReturnTypeDeclarationRule`](../../../doc/rules/ArrowFunctions/NoNullableReturnTypeDeclarationRule.md)
- [`Closures\NoNullableReturnTypeDeclarationRule`](../../../doc/rules/Closures/NoNullableReturnTypeDeclarationRule.md)
- [`Methods\NoNullableReturnTypeDeclarationRule`](../../../doc/rules/Methods/NoNullableReturnTypeDeclarationRule.md)
