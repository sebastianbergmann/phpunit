# `CallLikes\NoNamedArgumentRule`

This rule reports an error when an anonymous function, a function, or a method is invoked using a [named argument](https://www.php.net/manual/en/functions.arguments.php#functions.named-arguments).

## Rationale

Named arguments make the names of parameters part of the public API, so renaming a parameter breaks callers. Positional arguments keep the names of parameters an implementation detail.

## Examples

### Reported

```php
<?php

declare(strict_types=1);

function greet(string $name): string
{
    return \sprintf(
        'Hello, %s!',
        $name,
    );
}

greet(name: 'Jane');
```

### Not reported

```php
<?php

declare(strict_types=1);

function greet(string $name): string
{
    return \sprintf(
        'Hello, %s!',
        $name,
    );
}

greet('Jane');
```

## Parameters

### `enabled`

By default, this rule is enabled, unless you set the [`allRules`](../../../README.md#disabling-all-rules) parameter to `false`.

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		noNamedArgument:
			enabled: false
```
