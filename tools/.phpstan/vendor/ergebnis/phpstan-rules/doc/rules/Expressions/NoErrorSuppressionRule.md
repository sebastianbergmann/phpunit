# `Expressions\NoErrorSuppressionRule`

This rule reports an error when [`@`](https://www.php.net/manual/en/language.operators.errorcontrol.php) is used to suppress errors.

## Rationale

The error control operator hides every error that the expression causes, including errors you did not expect. Check for the error condition explicitly instead.

## Examples

### Reported

```php
<?php

declare(strict_types=1);

$contents = @\file_get_contents(__DIR__ . '/example.txt');
```

### Not reported

```php
<?php

declare(strict_types=1);

$path = __DIR__ . '/example.txt';

if (\is_readable($path)) {
    $contents = \file_get_contents($path);
}
```

## Parameters

### `enabled`

By default, this rule is enabled, unless you set the [`allRules`](../../../README.md#disabling-all-rules) parameter to `false`.

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		noErrorSuppression:
			enabled: false
```
