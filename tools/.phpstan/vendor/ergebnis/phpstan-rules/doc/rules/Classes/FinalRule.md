# `Classes\FinalRule`

This rule reports an error when a non-anonymous class is not `final`.

💡 By default, this rule ignores classes that

- use `@Entity`, `@ORM\Entity`, or `@ORM\Mapping\Entity` annotations
- use `Doctrine\ORM\Mapping\Entity` attributes

on the class level.

## Rationale

A class that is not `final` can be extended, so every change to it can break a subclass. Declare classes `final` and prefer composition over inheritance.

## Examples

### Reported

```php
<?php

declare(strict_types=1);

namespace Example;

class Greeting
{
}
```

### Not reported

```php
<?php

declare(strict_types=1);

namespace Example;

final class Greeting
{
}
```

## Parameters

### `enabled`

By default, this rule is enabled, unless you set the [`allRules`](../../../README.md#disabling-all-rules) parameter to `false`.

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		final:
			enabled: false
```

### `allowAbstractClasses`

By default, this rule allows to declare `abstract` classes.

You can set the `allowAbstractClasses` parameter to `false` to disallow abstract classes.

Default value: `true`

Example:

```neon
parameters:
	ergebnis:
		final:
			allowAbstractClasses: false
```

### `classesNotRequiredToBeAbstractOrFinal`

You can set the `classesNotRequiredToBeAbstractOrFinal` parameter to a list of class names that you want to exclude from inspection.

Default value: `[]`

Example:

```neon
parameters:
	ergebnis:
		final:
			classesNotRequiredToBeAbstractOrFinal:
				- Foo\Bar\NeitherAbstractNorFinal
				- Bar\Baz\NeitherAbstractNorFinal
```

### `ignoreDoctrineEntities`

By default, this rule ignores Doctrine entities, because Doctrine creates proxies for lazy loading by extending them.

When you use [native lazy objects](https://github.com/doctrine/orm/pull/11853) with `doctrine/orm:^3.4.0` on PHP 8.4 or later, Doctrine no longer extends entities, and you can set the `ignoreDoctrineEntities` parameter to `false` to require Doctrine entities to be `final` as well.

Default value: `true`

Example:

```neon
parameters:
	ergebnis:
		final:
			ignoreDoctrineEntities: false
```
