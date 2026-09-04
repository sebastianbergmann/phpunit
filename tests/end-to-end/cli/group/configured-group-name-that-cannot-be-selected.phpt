--TEST--
PHPUnit warns about the name of a group that is configured for a test file and parsed as a conjunction
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../../_files/group-conjunctions/phpunit.xml';
$_SERVER['argv'][] = '--no-progress';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %sphpunit.xml

Time: %s, Memory: %s

There was 1 PHPUnit test runner warning:

1) Group name "one+two" configured for %sBarTest.php cannot be used to select tests: "+" combines several group names into a selection of the tests that are in all of them

OK, but there were issues!
Tests: 3, Assertions: 3, PHPUnit Warnings: 1.
