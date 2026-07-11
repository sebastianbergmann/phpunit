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
An error occurred inside PHPUnit.

Message:  Attribute RequiresPhp for test method PHPUnit\TestFixture\Event\InvalidVersionConstraintNoVersionTest::testOne has invalid version requirement "invalid-version": expected a version constraint (such as "^8.1", "~8.1.0", or "8.1.*") or a version comparison (such as ">= 8.1.0")
Location: %s%eAttributeParser.php:%d

%a
