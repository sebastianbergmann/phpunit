--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6595 (assertion failure in setUpBeforeClass with directory containing data provider test)
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/6595';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

...

Time: %s, Memory: %s

There were 3 failures:

1) PHPUnit\TestFixture\Issue6595\FailureInSetUpBeforeClassTest
failure in setUpBeforeClass

%sFailureInSetUpBeforeClassTest.php:%d

2) PHPUnit\TestFixture\Issue6595\FailureInSetUpBeforeClassWithDataProviderTest
failure in setUpBeforeClass

%sFailureInSetUpBeforeClassWithDataProviderTest.php:%d

3) PHPUnit\TestFixture\Issue6595\FailureInTearDownAfterClassTest
failure in tearDownAfterClass

%sFailureInTearDownAfterClassTest.php:%d

FAILURES!
Tests: 5, Assertions: 3, Failures: 3.
