# `Expressions\NoEvalRule`

This rule reports an error when the language construct [`eval()`](https://www.php.net/eval) is used.

## Rationale

The language construct `eval()` executes a string as code, which is a security risk and cannot be analyzed statically.

## Examples

### Reported

```php
<?php

declare(strict_types=1);

$sum = eval('return 1 + 2;');
```

### Not reported

```php
<?php

declare(strict_types=1);

$sum = 1 + 2;
```

## Parameters

### `enabled`

By default, this rule is enabled, unless you set the [`allRules`](../../../README.md#disabling-all-rules) parameter to `false`.

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		noEval:
			enabled: false
```
