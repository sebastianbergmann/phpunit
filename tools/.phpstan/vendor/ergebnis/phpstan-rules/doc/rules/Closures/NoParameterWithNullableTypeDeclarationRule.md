# `Closures\NoParameterWithNullableTypeDeclarationRule`

This rule reports an error when a closure has a parameter with a nullable type declaration.

## Rationale

A parameter with a nullable type declaration makes the closure handle two cases: a value and `null`. Separate closures or a null object make each case explicit.

## Examples

### Reported

```php
<?php

declare(strict_types=1);

$toUpperCase = static function (?string $value): string {
    return \strtoupper((string) $value);
};
```

### Not reported

```php
<?php

declare(strict_types=1);

$toUpperCase = static function (string $value): string {
    return \strtoupper($value);
};
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

- [`ArrowFunctions\NoParameterWithNullableTypeDeclarationRule`](../../../doc/rules/ArrowFunctions/NoParameterWithNullableTypeDeclarationRule.md)
- [`Functions\NoParameterWithNullableTypeDeclarationRule`](../../../doc/rules/Functions/NoParameterWithNullableTypeDeclarationRule.md)
- [`Methods\NoParameterWithNullableTypeDeclarationRule`](../../../doc/rules/Methods/NoParameterWithNullableTypeDeclarationRule.md)
- [`PropertyHooks\NoParameterWithNullableTypeDeclarationRule`](../../../doc/rules/PropertyHooks/NoParameterWithNullableTypeDeclarationRule.md)
