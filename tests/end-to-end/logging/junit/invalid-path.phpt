--TEST--
Test runner emits warning when --log-junit is used with an invalid target path
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--display-phpunit-deprecations';
$_SERVER['argv'][] = '--log-junit';
$_SERVER['argv'][] = '';
$_SERVER['argv'][] = __DIR__ . '/../../_files/basic/SuccessTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 PHPUnit test runner warning:

1) Cannot log test results in JUnit XML format to "": Directory "" does not exist and could not be created

--

There was 1 PHPUnit test runner deprecation:

1) Support for logging test results in JUnit XML format has been deprecated.
This feature will be removed in PHPUnit 14.
Either migrate from consuming JUnit XML to consuming Open Test Reporting (OTR) XML,
or convert the OTR XML that PHPUnit generates to JUnit XML.

OK, but there were issues!
Tests: 1, Assertions: 1, PHPUnit Warnings: 1, PHPUnit Deprecations: 1.
