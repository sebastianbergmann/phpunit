--TEST--
Test is skipped when the class named in #[RequiresMethod] cannot be loaded
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/requires-method-class-cannot-be-loaded/phpunit.xml';
$_SERVER['argv'][] = '--display-skipped';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

S.                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There was 1 skipped test:

1) PHPUnit\TestFixture\RequiresMethodClassCannotBeLoaded\RequiresMethodTest::testShouldNotRun
Method PHPUnit\TestFixture\RequiresMethodClassCannotBeLoaded\ClassWithMissingParent::method() is required, but class PHPUnit\TestFixture\RequiresMethodClassCannotBeLoaded\ClassWithMissingParent cannot be loaded: Class "PHPUnit\TestFixture\RequiresMethodClassCannotBeLoaded\ParentClassThatDoesNotExist" not found

OK, but some tests were skipped!
Tests: 2, Assertions: 1, Skipped: 1.
