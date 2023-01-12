--TEST--
phpunit ../../_files/AssertionExampleTest.php
--SKIPIF--
<?php declare(strict_types=1);
if (ini_get('zend.assertions') != 1) {
    print 'skip: zend.assertions=1 is required' . PHP_EOL;
}

if (ini_get('assert.exception') != 1) {
    print 'skip: assert.exception=1 is required' . PHP_EOL;
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../../_files/AssertionExampleTest.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\AssertionExampleTest::testOne
assert(false)

%s:%i
%s:%i

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
