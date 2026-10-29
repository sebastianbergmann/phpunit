# `ArrowFunctions\NoParameterWithNullableTypeDeclarationRule`

This rule reports an error when an arrow function has a parameter with a nullable type declaration.

## Rationale

A parameter with a nullable type declaration makes the arrow function handle two cases: a value and `null`. Separate arrow functions or a null object make each case explicit.

## Examples

### Reported

```php
<?php

declare(strict_types=1);

$toUpperCase = static fn (?string $value): string => \strtoupper((string) $value);
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
		noParameterWithNullableTypeDeclaration:
			enabled: false
```

💡 The `enabled` parameter for `noParameterWithNullableTypeDeclaration` also enables or disables the following rules:

- [`Closures\NoParameterWithNullableTypeDeclarationRule`](../../../doc/rules/Closures/NoParameterWithNullableTypeDeclarationRule.md)
- [`Functions\NoParameterWithNullableTypeDeclarationRule`](../../../doc/rules/Functions/NoParameterWithNullableTypeDeclarationRule.md)
- [`Methods\NoParameterWithNullableTypeDeclarationRule`](../../../doc/rules/Methods/NoParameterWithNullableTypeDeclarationRule.md)
- [`PropertyHooks\NoParameterWithNullableTypeDeclarationRule`](../../../doc/rules/PropertyHooks/NoParameterWithNullableTypeDeclarationRule.md)
