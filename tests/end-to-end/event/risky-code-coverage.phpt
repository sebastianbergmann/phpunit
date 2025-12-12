--TEST--
The right events are emitted in the right order for a test that is considered risky because it executed code that is not listed as code to be covered or used
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('xdebug')) {
    print 'skip: Extension Xdebug must be loaded.';
}
--INI--
xdebug.mode=coverage
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--coverage-text';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/test-risky-code-coverage';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Bootstrap Finished (%sautoload.php)
Test Suite Loaded (1 test)
Event Facade Sealed
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (%sphpunit.xml, 1 test)
Test Suite Started (default, 1 test)
Test Suite Started (PHPUnit\TestFixture\Event\RiskyCodeCoverage\FooTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\RiskyCodeCoverage\FooTest::testSomething)
Test Prepared (PHPUnit\TestFixture\Event\RiskyCodeCoverage\FooTest::testSomething)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\Event\RiskyCodeCoverage\FooTest::testSomething)
Test Considered Risky (PHPUnit\TestFixture\Event\RiskyCodeCoverage\FooTest::testSomething)
This test executed code that is not listed as code to be covered or used:
- PHPUnit\TestFixture\Event\RiskyCodeCoverage\Bar

Test Finished (PHPUnit\TestFixture\Event\RiskyCodeCoverage\FooTest::testSomething)
Test Suite Finished (PHPUnit\TestFixture\Event\RiskyCodeCoverage\FooTest, 1 test)
Test Suite Finished (default, 1 test)
Test Suite Finished (%sphpunit.xml, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
