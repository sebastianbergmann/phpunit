--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6861
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/6861/Issue6861Test.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\Issue6861\Issue6861Test, 1 test)
Test Runner Triggered PHPUnit Warning (Method PHPUnit\TestFixture\Issue6861\Issue6861Test::setUpBeforeClass() is a template method and does not need the #[BeforeClass] attribute; the attribute is ignored)
Test Runner Triggered PHPUnit Warning (Method PHPUnit\TestFixture\Issue6861\Issue6861Test::tearDownAfterClass() is a template method and does not need the #[AfterClass] attribute; the attribute is ignored)
Test Runner Triggered PHPUnit Warning (Method PHPUnit\TestFixture\Issue6861\Issue6861Test::setUp() is a template method and does not need the #[Before] attribute; the attribute is ignored)
Test Runner Triggered PHPUnit Warning (Method PHPUnit\TestFixture\Issue6861\Issue6861Test::assertPreConditions() is a template method and does not need the #[PreCondition] attribute; the attribute is ignored)
Test Runner Triggered PHPUnit Warning (Method PHPUnit\TestFixture\Issue6861\Issue6861Test::assertPostConditions() is a template method and does not need the #[PostCondition] attribute; the attribute is ignored)
Test Runner Triggered PHPUnit Warning (Method PHPUnit\TestFixture\Issue6861\Issue6861Test::tearDown() is a template method and does not need the #[After] attribute; the attribute is ignored)
Before First Test Method Called (PHPUnit\TestFixture\Issue6861\Issue6861Test::setUpBeforeClass)
Before First Test Method Finished:
- PHPUnit\TestFixture\Issue6861\Issue6861Test::setUpBeforeClass
Test Preparation Started (PHPUnit\TestFixture\Issue6861\Issue6861Test::testOne)
Before Test Method Called (PHPUnit\TestFixture\Issue6861\Issue6861Test::setUp)
Before Test Method Finished:
- PHPUnit\TestFixture\Issue6861\Issue6861Test::setUp
Pre Condition Method Called (PHPUnit\TestFixture\Issue6861\Issue6861Test::assertPreConditions)
Pre Condition Method Finished:
- PHPUnit\TestFixture\Issue6861\Issue6861Test::assertPreConditions
Test Prepared (PHPUnit\TestFixture\Issue6861\Issue6861Test::testOne)
Post Condition Method Called (PHPUnit\TestFixture\Issue6861\Issue6861Test::assertPostConditions)
Post Condition Method Finished:
- PHPUnit\TestFixture\Issue6861\Issue6861Test::assertPostConditions
After Test Method Called (PHPUnit\TestFixture\Issue6861\Issue6861Test::tearDown)
After Test Method Finished:
- PHPUnit\TestFixture\Issue6861\Issue6861Test::tearDown
Test Passed (PHPUnit\TestFixture\Issue6861\Issue6861Test::testOne)
Test Finished (PHPUnit\TestFixture\Issue6861\Issue6861Test::testOne)
After Last Test Method Called (PHPUnit\TestFixture\Issue6861\Issue6861Test::tearDownAfterClass)
After Last Test Method Finished:
- PHPUnit\TestFixture\Issue6861\Issue6861Test::tearDownAfterClass
Test Suite Finished (PHPUnit\TestFixture\Issue6861\Issue6861Test, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
