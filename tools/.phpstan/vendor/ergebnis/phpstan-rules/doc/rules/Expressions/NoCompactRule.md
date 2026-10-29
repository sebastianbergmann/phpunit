# `Expressions\NoCompactRule`

This rule reports an error when the function [`compact()`](https://www.php.net/compact) is used.

## Rationale

The function `compact()` refers to variables by their names in strings, which static analysis and automated refactoring cannot follow. Build the array explicitly instead.

## Examples

### Reported

```php
<?php

declare(strict_types=1);

$name = 'Jane';

$context = \compact('name');
```

### Not reported

```php
<?php

declare(strict_types=1);

$name = 'Jane';

$context = [
    'name' => $name,
];
```

## Parameters

### `enabled`

By default, this rule is enabled, unless you set the [`allRules`](../../../README.md#disabling-all-rules) parameter to `false`.

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		noCompact:
			enabled: false
```
