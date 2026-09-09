--TEST--
Tests are correctly ran based on class requirements
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/requires_class/phpunit.xml';
$_SERVER['argv'][] = '--display-skipped';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

..S                                                                 3 / 3 (100%)

Time: %s, Memory: %s

There was 1 skipped test:

1) PHPUnit\TestFixture\requires_class\SomeTest::testShouldNotRunClassDoesNotExist
Class PHPUnit\TestFixture\requires_class\ClassThatDoesNotExist is required.

OK, but some tests were skipped!
Tests: 3, Assertions: 2, Skipped: 1.