--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6601
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/6601/Issue6601Test.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

E                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 error:

1) PHPUnit\TestFixture\Issue6601\Issue6601Test::testOne
PHPUnit\Framework\MockObject\Generator\ClassIsAnonymousException: Class "PHPUnit\TestFixture\Issue6601\Customizable@anonymous%sIssue6601Test.php:%d%s" is an anonymous class and cannot be doubled

%sIssue6601Test.php:%d

ERRORS!
Tests: 1, Assertions: 0, Errors: 1.
