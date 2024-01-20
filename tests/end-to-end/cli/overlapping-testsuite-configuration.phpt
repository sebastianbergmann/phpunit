--TEST--
A test file must not be in more than one test suite configured in the XML configuration file
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/overlapping-testsuite-configuration/phpunit.xml';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %soverlapping-testsuite-configuration%sphpunit.xml

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 PHPUnit test runner warning:

1) Cannot add file %sExampleTest.php to test suite "two" as it was already added to test suite "one"

WARNINGS!
Tests: 1, Assertions: 1, Warnings: 1.
