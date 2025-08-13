--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5863
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--display-errors';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] =  __DIR__ . '/../_files/ThrowsWithPreviousExceptionTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       PHP %s

E                                                                   1 / 1 (100%)

Time: %s, Memory: %s

Throws With Previous Exception (PHPUnit\TestFixture\ThrowsWithPreviousException)
 ✘ Foo
   │
   │ Exception: Outer
   │
   │ %sThrowsWithPreviousExceptionTest.php:%d
   │  
   │ Caused by:
   │ Exception: Inner
   │
   │ %sThrowsWithPreviousExceptionTest.php:%d
   │

ERRORS!
Tests: 1, Assertions: 0, Errors: 1.
