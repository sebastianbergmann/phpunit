# `Methods\FinalInAbstractClassRule`

This rule reports an error when a concrete `public` or `protected` method in an `abstract` class is not `final`.

💡 By default, this rule ignores Doctrine entities.

## Rationale

A concrete method in an `abstract` class that is not `final` can be overridden by subclasses, which can break the assumptions of the abstract class. Declare the method `final`, or `abstract` when subclasses need to provide it.

## Examples

### Reported

```php
<?php

declare(strict_types=1);

namespace Example;

abstract class AbstractGreeting
{
    public function greet(): string
    {
        return 'Hello!';
    }
}
```

### Not reported

```php
<?php

declare(strict_types=1);

namespace Example;

abstract class AbstractGreeting
{
    final public function greet(): string
    {
        return 'Hello!';
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
		finalInAbstractClass:
			enabled: false
```

### `ignoreDoctrineEntities`

By default, this rule ignores Doctrine entities, because Doctrine creates proxies for lazy loading by extending them.

When you use [native lazy objects](https://github.com/doctrine/orm/pull/11853) with `doctrine/orm:^3.4.0` on PHP 8.4 or later, Doctrine no longer extends entities, and you can set the `ignoreDoctrineEntities` parameter to `false` to require concrete methods in `abstract` Doctrine entities to be `final` as well.

Default value: `true`

Example:

```neon
parameters:
	ergebnis:
		finalInAbstractClass:
			ignoreDoctrineEntities: false
```
