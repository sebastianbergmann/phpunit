--TEST--
The methods that run before the first and after the last test of a test class are run once when the test class is the only one in a test suite of the XML configuration file that is named like it
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/test-suite-named-like-test-class/phpunit.xml';
$_SERVER['argv'][] = '--debug';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (%sphpunit.xml, 1 test)
Test Suite Started (PHPUnit\TestFixture\Event\TestSuiteNamedLikeTestClassTest, 1 test)
Test Suite Started (PHPUnit\TestFixture\Event\TestSuiteNamedLikeTestClassTest, 1 test)
Before First Test Method Called (PHPUnit\TestFixture\Event\TestSuiteNamedLikeTestClassTest::setUpBeforeClass)
Before First Test Method Finished:
- PHPUnit\TestFixture\Event\TestSuiteNamedLikeTestClassTest::setUpBeforeClass
Test Preparation Started (PHPUnit\TestFixture\Event\TestSuiteNamedLikeTestClassTest::testOne)
Test Prepared (PHPUnit\TestFixture\Event\TestSuiteNamedLikeTestClassTest::testOne)
Test Passed (PHPUnit\TestFixture\Event\TestSuiteNamedLikeTestClassTest::testOne)
Test Finished (PHPUnit\TestFixture\Event\TestSuiteNamedLikeTestClassTest::testOne)
After Last Test Method Called (PHPUnit\TestFixture\Event\TestSuiteNamedLikeTestClassTest::tearDownAfterClass)
After Last Test Method Finished:
- PHPUnit\TestFixture\Event\TestSuiteNamedLikeTestClassTest::tearDownAfterClass
Test Suite Finished (PHPUnit\TestFixture\Event\TestSuiteNamedLikeTestClassTest, 1 test)
Test Suite Finished (PHPUnit\TestFixture\Event\TestSuiteNamedLikeTestClassTest, 1 test)
Test Suite Finished (%sphpunit.xml, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
