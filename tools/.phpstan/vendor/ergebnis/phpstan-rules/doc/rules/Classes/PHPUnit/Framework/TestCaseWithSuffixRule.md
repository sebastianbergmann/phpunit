# `Classes\PHPUnit\Framework\TestCaseWithSuffixRule`

This rule reports an error when a concrete class is a sub-class of `PHPUnit\Framework\TestCase` but does not have a `Test` suffix.

## Rationale

By default, PHPUnit only runs tests from files whose names end with `Test.php`, and the suffix tells readers which classes are tests.

## Examples

### Reported

```php
<?php

declare(strict_types=1);

namespace Example\Test;

use PHPUnit\Framework;

final class Greeting extends Framework\TestCase
{
}
```

### Not reported

```php
<?php

declare(strict_types=1);

namespace Example\Test;

use PHPUnit\Framework;

final class GreetingTest extends Framework\TestCase
{
}
```

## Parameters

### `enabled`

By default, this rule is enabled, unless you set the [`allRules`](../../../../../README.md#disabling-all-rules) parameter to `false`.

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		testCaseWithSuffix:
			enabled: false
```
