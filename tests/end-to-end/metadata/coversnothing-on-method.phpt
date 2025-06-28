--TEST--
phpunit ../_files/size-combinations/SmallMediumTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--display-phpunit-deprecations';
$_SERVER['argv'][] = __DIR__ . '/../../_files/CoversNothingOnMethodTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

D                                                                   1 / 1 (100%)

Time: %s, Memory: %s

1 test triggered 1 PHPUnit deprecation:

1) PHPUnit\TestFixture\CoversNothingOnMethodTest::testSomething
Using #[CoversNothing] on a test method is deprecated, support for this will be removed in PHPUnit 13

%sCoversNothingOnMethodTest.php:%d

OK, but there were issues!
Tests: 1, Assertions: 1, PHPUnit Deprecations: 1.
