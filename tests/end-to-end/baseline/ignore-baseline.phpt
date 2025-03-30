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

DNWDW                                                               5 / 5 (100%)

Time: %s, Memory: %s

1 test triggered 1 PHP warning:

1) %sSource.php:81
Undefined property: class@anonymous::$a

Triggered by:

* PHPUnit\TestFixture\Baseline\SourceTest::testPhpNoticeAndWarning
  %sSourceTest.php:44

--

1 test triggered 1 warning:

1) %sSource.php:57
warning

Triggered by:

* PHPUnit\TestFixture\Baseline\SourceTest::testWarning
  %sSourceTest.php:30

--

1 test triggered 1 PHP notice:

1) %sSource.php:81
Accessing static property class@anonymous::$a as non static

Triggered by:

* PHPUnit\TestFixture\Baseline\SourceTest::testPhpNoticeAndWarning
  %sSourceTest.php:44

--

1 test triggered 1 notice:

1) %sSource.php:52
notice

Triggered by:

* PHPUnit\TestFixture\Baseline\SourceTest::testNotice
  %sSourceTest.php:23

--

1 test triggered 1 PHP deprecation:

1) %sSource.php:62
Serializable@anonymous implements the Serializable interface, which is deprecated. Implement __serialize() and __unserialize() instead (or in addition, if support for old PHP versions is necessary)

Triggered by:

* PHPUnit\TestFixture\Baseline\SourceTest::testPhpDeprecation
  %sSourceTest.php:37

--

1 test triggered 1 deprecation:

1) %sSource.php:47
deprecation

Triggered by:

* PHPUnit\TestFixture\Baseline\SourceTest::testDeprecation
  %sSourceTest.php:16

OK, but there were issues!
Tests: 5, Assertions: 5, Warnings: 2, Deprecations: 2, Notices: 2.
