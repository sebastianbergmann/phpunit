--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6595 (assertion failure in setUpBeforeClass)
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/6595/FailureInSetUpBeforeClassTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s



Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\Issue6595\FailureInSetUpBeforeClassTest
failure in setUpBeforeClass

%sFailureInSetUpBeforeClassTest.php:%d

FAILURES!
Tests: 1, Assertions: 0, Failures: 1.
