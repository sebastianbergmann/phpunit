--TEST--
Tests are correctly ran based on environment variables requirements
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/requires_environment_variable/phpunit.xml';
$_SERVER['argv'][] = '--display-skipped';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

SSS....                                                             7 / 7 (100%)

Time: %s, Memory: %s

There were 3 skipped tests:

1) PHPUnit\TestFixture\requires_environment_variable\SomeTest::testShouldNotRunFOOHasWrongValue
Environment variable "FOO" is required to be "bar".

2) PHPUnit\TestFixture\requires_environment_variable\SomeTest::testShouldNotRunBARIsEmpty
Environment variable "BAR" is required.

3) PHPUnit\TestFixture\requires_environment_variable\SomeTest::testShouldNotRunBAZDoesNotExist
Environment variable "BAZ" is required.

OK, but some tests were skipped!
Tests: 7, Assertions: 4, Skipped: 3.

