--TEST--
Test doubles can be used together with partial function application
--SKIPIF--
<?php declare(strict_types=1);
if (version_compare('8.6.0-dev', PHP_VERSION, '>')) {
    print 'skip: PHP 8.6 is required.';
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/../_files/TestDoubleAndPartialFunctionApplicationTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (3 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (3 tests)
Test Suite Started (PHPUnit\TestFixture\TestDoubleAndPartialFunctionApplicationTest, 3 tests)
Test Preparation Started (PHPUnit\TestFixture\TestDoubleAndPartialFunctionApplicationTest::testPartiallyAppliedDoubledMethodIsInvokedWithBoundAndPlaceholderArguments)
Test Prepared (PHPUnit\TestFixture\TestDoubleAndPartialFunctionApplicationTest::testPartiallyAppliedDoubledMethodIsInvokedWithBoundAndPlaceholderArguments)
Mock Object Created (PHPUnit\TestFixture\GreeterForPartialFunctionApplication)
Test Passed (PHPUnit\TestFixture\TestDoubleAndPartialFunctionApplicationTest::testPartiallyAppliedDoubledMethodIsInvokedWithBoundAndPlaceholderArguments)
Test Finished (PHPUnit\TestFixture\TestDoubleAndPartialFunctionApplicationTest::testPartiallyAppliedDoubledMethodIsInvokedWithBoundAndPlaceholderArguments)
Test Preparation Started (PHPUnit\TestFixture\TestDoubleAndPartialFunctionApplicationTest::testCreatingPartialFromDoubledMethodDoesNotCountAsInvocation)
Test Prepared (PHPUnit\TestFixture\TestDoubleAndPartialFunctionApplicationTest::testCreatingPartialFromDoubledMethodDoesNotCountAsInvocation)
Mock Object Created (PHPUnit\TestFixture\GreeterForPartialFunctionApplication)
Test Passed (PHPUnit\TestFixture\TestDoubleAndPartialFunctionApplicationTest::testCreatingPartialFromDoubledMethodDoesNotCountAsInvocation)
Test Finished (PHPUnit\TestFixture\TestDoubleAndPartialFunctionApplicationTest::testCreatingPartialFromDoubledMethodDoesNotCountAsInvocation)
Test Preparation Started (PHPUnit\TestFixture\TestDoubleAndPartialFunctionApplicationTest::testPartialWithBoundArgumentAndVariadicPlaceholderInvokesDoubledMethod)
Test Prepared (PHPUnit\TestFixture\TestDoubleAndPartialFunctionApplicationTest::testPartialWithBoundArgumentAndVariadicPlaceholderInvokesDoubledMethod)
Test Stub Created (PHPUnit\TestFixture\GreeterForPartialFunctionApplication)
Test Passed (PHPUnit\TestFixture\TestDoubleAndPartialFunctionApplicationTest::testPartialWithBoundArgumentAndVariadicPlaceholderInvokesDoubledMethod)
Test Finished (PHPUnit\TestFixture\TestDoubleAndPartialFunctionApplicationTest::testPartialWithBoundArgumentAndVariadicPlaceholderInvokesDoubledMethod)
Test Suite Finished (PHPUnit\TestFixture\TestDoubleAndPartialFunctionApplicationTest, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
