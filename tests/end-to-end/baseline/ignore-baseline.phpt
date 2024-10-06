--TEST--
phpunit --configuration ../_files/baseline/use-baseline/phpunit.xml --ignore-baseline
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/baseline/use-baseline/phpunit.xml';
$_SERVER['argv'][] = '--ignore-baseline';
$_SERVER['argv'][] = '--display-deprecations';
$_SERVER['argv'][] = '--display-notices';
$_SERVER['argv'][] = '--display-warnings';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

W                                                                   1 / 1 (100%)

Time: %s, Memory: %s

1 test triggered 1 PHP warning:

1) %sUseBaselineTest.php:31
Undefined variable $b

Triggered by:

* PHPUnit\TestFixture\Baseline\UseBaselineTest::testOne
  %sUseBaselineTest.php:21

--

1 test triggered 1 warning:

1) %sUseBaselineTest.php:35
warning

Triggered by:

* PHPUnit\TestFixture\Baseline\UseBaselineTest::testOne
  %sUseBaselineTest.php:21

--

1 test triggered 1 PHP notice:

1) %sUseBaselineTest.php:29
Only variables should be assigned by reference

Triggered by:

* PHPUnit\TestFixture\Baseline\UseBaselineTest::testOne
  %sUseBaselineTest.php:21

--

1 test triggered 1 notice:

1) %sUseBaselineTest.php:34
notice

Triggered by:

* PHPUnit\TestFixture\Baseline\UseBaselineTest::testOne
  %sUseBaselineTest.php:21

--

1 test triggered 1 PHP deprecation:

1) %sUseBaselineTest.php:23
strlen(): Passing null to parameter #1 ($string) of type string is deprecated

Triggered by:

* PHPUnit\TestFixture\Baseline\UseBaselineTest::testOne
  %sUseBaselineTest.php:21

--

1 test triggered 1 deprecation:

1) %sUseBaselineTest.php:33
deprecation

Triggered by:

* PHPUnit\TestFixture\Baseline\UseBaselineTest::testOne
  %sUseBaselineTest.php:21

OK, but there were issues!
Tests: 1, Assertions: 1, Warnings: 2, Deprecations: 2, Notices: 2.
