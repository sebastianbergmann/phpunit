--TEST--
phpunit --parallel=2 --log-junit nests the test suite of a test class in a test suite of the configuration that is named like that class, as a sequential run does
--FILE--
<?php declare(strict_types=1);
$logfile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/test-suite-named-like-its-only-class/phpunit.xml';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = '--log-junit';
$_SERVER['argv'][] = $logfile;

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($logfile);

unlink($logfile);
--EXPECTF--
<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="%sphpunit.xml" tests="2" assertions="2" errors="0" failures="0" skipped="0" time="%s">
    <testsuite name="PHPUnit\TestFixture\ParallelLogging\OnlyClassOfItsTestSuiteTest" tests="1" assertions="1" errors="0" failures="0" skipped="0" time="%s">
      <testsuite name="PHPUnit\TestFixture\ParallelLogging\OnlyClassOfItsTestSuiteTest" file="%sOnlyClassOfItsTestSuiteTest.php" tests="1" assertions="1" errors="0" failures="0" skipped="0" time="%s">
        <testcase name="testPasses" file="%sOnlyClassOfItsTestSuiteTest.php" line="%d" class="PHPUnit\TestFixture\ParallelLogging\OnlyClassOfItsTestSuiteTest" classname="PHPUnit.TestFixture.ParallelLogging.OnlyClassOfItsTestSuiteTest" assertions="1" time="%s"/>
      </testsuite>
    </testsuite>
    <testsuite name="other" tests="1" assertions="1" errors="0" failures="0" skipped="0" time="%s">
      <testsuite name="PHPUnit\TestFixture\ParallelLogging\OtherTestSuiteTest" file="%sOtherTestSuiteTest.php" tests="1" assertions="1" errors="0" failures="0" skipped="0" time="%s">
        <testcase name="testPasses" file="%sOtherTestSuiteTest.php" line="%d" class="PHPUnit\TestFixture\ParallelLogging\OtherTestSuiteTest" classname="PHPUnit.TestFixture.ParallelLogging.OtherTestSuiteTest" assertions="1" time="%s"/>
      </testsuite>
    </testsuite>
  </testsuite>
</testsuites>
