# `Methods\NoConstructorParameterWithDefaultValueRule`

This rule reports an error when a constructor has a parameter with a default value.

## Rationale

A constructor parameter with a default value lets callers create an object without deciding on that value. Named constructors make each way of creating the object explicit.

## Examples

### Reported

```php
<?php

declare(strict_types=1);

namespace Example;

final class Greeting
{
    private string $salutation;

    public function __construct(string $salutation = 'Hello')
    {
        $this->salutation = $salutation;
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
    private string $salutation;

    public function __construct(string $salutation)
    {
        $this->salutation = $salutation;
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
		noConstructorParameterWithDefaultValue:
			enabled: false
```
