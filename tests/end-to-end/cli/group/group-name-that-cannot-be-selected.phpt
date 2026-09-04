--TEST--
PHPUnit warns about the name of a group that the --group option parses as a conjunction
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = __DIR__ . '/../../_files/group-conjunctions/tests/GroupNameWithSeparatorTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

Time: %s, Memory: %s

There was 1 PHPUnit test runner warning:

1) Group name "one+two" for class PHPUnit\TestFixture\GroupConjunctions\GroupNameWithSeparatorTest cannot be used to select tests: "+" combines several group names into a selection of the tests that are in all of them

OK, but there were issues!
Tests: 1, Assertions: 1, PHPUnit Warnings: 1.
