# `Expressions\NoIssetRule`

This rule reports an error when the language construct [`isset()`](https://www.php.net/isset) is used.

## Rationale

The language construct `isset()` returns `false` both when a variable, array key, or property does not exist and when it holds `null`, so it hides which of the two applies. Use `array_key_exists()`, `property_exists()`, or a comparison with `null` instead.

## Examples

### Reported

```php
<?php

declare(strict_types=1);

$values = [
    'name' => 'Jane',
];

if (isset($values['name'])) {
    echo $values['name'];
}
```

### Not reported

```php
<?php

declare(strict_types=1);

$values = [
    'name' => 'Jane',
];

if (\array_key_exists('name', $values)) {
    echo $values['name'];
}
```

## Parameters

### `enabled`

By default, this rule is enabled, unless you set the [`allRules`](../../../README.md#disabling-all-rules) parameter to `false`.

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		noIsset:
			enabled: false
```
