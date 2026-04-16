--TEST--
--repeat with JUnit XML logging includes repetition info in test names
--FILE--
<?php declare(strict_types=1);
$junitFile = tempnam(sys_get_temp_dir(), 'phpunit_repeat_junit_');

$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '2';
$_SERVER['argv'][] = '--log-junit';
$_SERVER['argv'][] = $junitFile;
$_SERVER['argv'][] = __DIR__ . '/_files/SuccessTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($junitFile);

unlink($junitFile);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

....                                                                4 / 4 (100%)

Time: %s, Memory: %s

OK (4 tests, 4 assertions)
<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="PHPUnit\TestFixture\Repeat\SuccessTest" file="%sSuccessTest.php" tests="4" assertions="4" errors="0" failures="0" skipped="0" time="%f">
    <testcase name="testOne (repetition 1 of 2)" file="%sSuccessTest.php" line="16" class="PHPUnit\TestFixture\Repeat\SuccessTest" classname="PHPUnit.TestFixture.Repeat.SuccessTest" assertions="1" time="%f"/>
    <testcase name="testOne (repetition 2 of 2)" file="%sSuccessTest.php" line="16" class="PHPUnit\TestFixture\Repeat\SuccessTest" classname="PHPUnit.TestFixture.Repeat.SuccessTest" assertions="1" time="%f"/>
    <testcase name="testTwo (repetition 1 of 2)" file="%sSuccessTest.php" line="21" class="PHPUnit\TestFixture\Repeat\SuccessTest" classname="PHPUnit.TestFixture.Repeat.SuccessTest" assertions="1" time="%f"/>
    <testcase name="testTwo (repetition 2 of 2)" file="%sSuccessTest.php" line="21" class="PHPUnit\TestFixture\Repeat\SuccessTest" classname="PHPUnit.TestFixture.Repeat.SuccessTest" assertions="1" time="%f"/>
  </testsuite>
</testsuites>
