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
Configuration: %s/filter-enabled.xml

W                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 deprecation:

1) PHPUnit\TestFixture\FilterErrorHandler\SourceClassTest::testSomething
deprecation
%s/src/SourceClass.php:%d

%s/tests/SourceClassTest.php:%d

--

There was 1 notice:

1) PHPUnit\TestFixture\FilterErrorHandler\SourceClassTest::testSomething
notice
%s/src/SourceClass.php:%d

%s/tests/SourceClassTest.php:%d

--

There was 1 warning:

1) PHPUnit\TestFixture\FilterErrorHandler\SourceClassTest::testSomething
warning
%s/src/SourceClass.php:%d

%s/tests/SourceClassTest.php:%d

OK, but some tests have issues!
Tests: 1, Assertions: 1, Warnings: 1, Deprecations: 1, Notices: 1.
