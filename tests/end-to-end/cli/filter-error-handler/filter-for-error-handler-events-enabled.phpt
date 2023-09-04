--TEST--
phpunit --configuration ../../_files/filter-error-handler/filter-enabled.xml
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../../_files/filter-error-handler/filter-enabled.xml';
$_SERVER['argv'][] = '--display-deprecations';
$_SERVER['argv'][] = '--display-notices';
$_SERVER['argv'][] = '--display-warnings';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s%efilter-enabled.xml

W                                                                   1 / 1 (100%)

Time: %s, Memory: %s

1 test triggered 1 warning:

1) %s%esrc%eSourceClass.php:23
warning

Triggered by:

* PHPUnit\TestFixture\FilterErrorHandler\SourceClassTest::testSomething
  %s%etests%eSourceClassTest.php:16

--

1 test triggered 1 notice:

1) %s%esrc%eSourceClass.php:22
notice

Triggered by:

* PHPUnit\TestFixture\FilterErrorHandler\SourceClassTest::testSomething
  %s%etests%eSourceClassTest.php:16

--

1 test triggered 1 deprecation:

1) %s%esrc%eSourceClass.php:21
deprecation

Triggered by:

* PHPUnit\TestFixture\FilterErrorHandler\SourceClassTest::testSomething
  %s%etests%eSourceClassTest.php:16

OK, but there were issues!
Tests: 1, Assertions: 1, Warnings: 1, Deprecations: 1, Notices: 1.
