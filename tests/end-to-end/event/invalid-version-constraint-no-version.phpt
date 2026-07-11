--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6356
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/InvalidVersionConstraintNoVersionTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       PHP %s

There was 1 PHPUnit error:

1) PHPUnit\TestFixture\Event\InvalidVersionConstraintNoVersionTest::testOne
Attribute RequiresPhp for test method PHPUnit\TestFixture\Event\InvalidVersionConstraintNoVersionTest::testOne has invalid version requirement "invalid-version": expected a version constraint (such as "^8.1", "~8.1.0", or "8.1.*") or a version comparison (such as ">= 8.1.0")

%sInvalidVersionConstraintNoVersionTest.php:18

--

There was 1 PHPUnit test runner warning:

1) No tests found in class "PHPUnit\TestFixture\Event\InvalidVersionConstraintNoVersionTest".

No tests executed!
