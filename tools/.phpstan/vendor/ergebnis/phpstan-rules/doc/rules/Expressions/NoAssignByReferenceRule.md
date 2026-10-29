# `Expressions\NoAssignByReferenceRule`

This rule reports an error when [a variable is assigned by reference](https://www.php.net/manual/en/language.references.whatdo.php#language.references.whatdo.assign).

## Rationale

Assigning by reference makes two variables share one value, so changing one changes the other, which makes the code harder to follow.

## Examples

### Reported

```php
<?php

declare(strict_types=1);

$values = [
    'foo',
];

$alias = &$values;
```

### Not reported

```php
<?php

declare(strict_types=1);

$values = [
    'foo',
];

$copy = $values;
```

## Parameters

### `enabled`

By default, this rule is enabled, unless you set the [`allRules`](../../../README.md#disabling-all-rules) parameter to `false`.

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		noAssignByReference:
			enabled: false
```
