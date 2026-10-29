# `Statements\NoSwitchRule`

This rule reports an error when the statement [`switch()`](https://www.php.net/manual/control-structures.switch.php) is used.

## Rationale

A `switch` statement compares loosely and falls through to the next case without `break`, which can cause subtle bugs. Use `if` statements or `match` instead.

## Examples

### Reported

```php
<?php

declare(strict_types=1);

$status = 'active';

switch ($status) {
    case 'active':
        echo 'Active';

        break;

    default:
        echo 'Inactive';
}
```

### Not reported

```php
<?php

declare(strict_types=1);

$status = 'active';

if ('active' === $status) {
    echo 'Active';
} else {
    echo 'Inactive';
}
```

## Parameters

### `enabled`

By default, this rule is enabled, unless you set the [`allRules`](../../../README.md#disabling-all-rules) parameter to `false`.

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		noSwitch:
			enabled: false
```
