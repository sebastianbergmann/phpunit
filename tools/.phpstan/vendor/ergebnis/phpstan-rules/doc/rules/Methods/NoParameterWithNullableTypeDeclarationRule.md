# `Methods\NoParameterWithNullableTypeDeclarationRule`

This rule reports an error when a method has a parameter with a nullable type declaration.

## Rationale

A parameter with a nullable type declaration makes the method handle two cases: a value and `null`. Separate methods or a null object make each case explicit.

## Examples

### Reported

```php
<?php

declare(strict_types=1);

namespace Example;

final class Greeting
{
    public function greet(?string $name): string
    {
        return \sprintf(
            'Hello, %s!',
            (string) $name,
        );
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
    public function greet(string $name): string
    {
        return \sprintf(
            'Hello, %s!',
            $name,
        );
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
		noParameterWithNullableTypeDeclaration:
			enabled: false
```

💡 The `enabled` parameter for `noParameterWithNullableTypeDeclaration` also enables or disables the following rules:

- [`ArrowFunctions\NoParameterWithNullableTypeDeclarationRule`](../../../doc/rules/ArrowFunctions/NoParameterWithNullableTypeDeclarationRule.md)
- [`Closures\NoParameterWithNullableTypeDeclarationRule`](../../../doc/rules/Closures/NoParameterWithNullableTypeDeclarationRule.md)
- [`Functions\NoParameterWithNullableTypeDeclarationRule`](../../../doc/rules/Functions/NoParameterWithNullableTypeDeclarationRule.md)
- [`PropertyHooks\NoParameterWithNullableTypeDeclarationRule`](../../../doc/rules/PropertyHooks/NoParameterWithNullableTypeDeclarationRule.md)
