--TEST--
The right events are emitted in the right order for a successful test that targets a trait with #[CoversTrait]
--SKIPIF--
<?php declare(strict_types=1);
require __DIR__ . '/../../_files/skip-if-requires-code-coverage-driver.php';
--FILE--
<?php declare(strict_types=1);
$traceFile    = tempnam(sys_get_temp_dir(), __FILE__);
$coverageFile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--coverage-text=' . $coverageFile;
$_SERVER['argv'][] = '--coverage-filter';
$_SERVER['argv'][] = __DIR__ . '/../_files';
$_SERVER['argv'][] = __DIR__ . '/../_files/TraitTargetedWithCoversTraitTest.php';

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
Test Suite Started (PHPUnit\DeprecatedAnnotationsTestFixture\TraitTargetedWithCoversTraitTest, 1 test)
Test Preparation Started (PHPUnit\DeprecatedAnnotationsTestFixture\TraitTargetedWithCoversTraitTest::testSomething)
Test Prepared (PHPUnit\DeprecatedAnnotationsTestFixture\TraitTargetedWithCoversTraitTest::testSomething)
Test Passed (PHPUnit\DeprecatedAnnotationsTestFixture\TraitTargetedWithCoversTraitTest::testSomething)
Test Runner Triggered Deprecation (Targeting a trait such as PHPUnit\TestFixture\CoveredTrait with #[CoversTrait] is deprecated. The traits used by the class(es) you target with #[CoversClass] will be targeted as well.)
Test Finished (PHPUnit\DeprecatedAnnotationsTestFixture\TraitTargetedWithCoversTraitTest::testSomething)
Test Suite Finished (PHPUnit\DeprecatedAnnotationsTestFixture\TraitTargetedWithCoversTraitTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
