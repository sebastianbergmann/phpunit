--TEST--
phpunit --log-junit php://stdout _files/TypeErrorTest.php
--SKIPIF--
<?php declare(strict_types=1);
if (DIRECTORY_SEPARATOR === '\\') {
    print "skip: this test does not work on Windows / GitHub Actions\n";
}
--FILE--
<?php declare(strict_types=1);
$logfile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--dont-report-useless-tests';
$_SERVER['argv'][] = '--log-junit';
$_SERVER['argv'][] = $logfile;
$_SERVER['argv'][] = __DIR__ . '/../_files/TypeErrorTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($logfile);

unlink($logfile);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

E                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 error:

1) PHPUnit\TestFixture\TypeErrorTest::testMe
TypeError: Cannot assign DateTime to property PHPUnit\TestFixture\TypeErrorTest::$dateTime of type DateTimeImmutable

%sTypeErrorTest.php:%d

ERRORS!
Tests: 1, Assertions: 0, Errors: 1.
<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="PHPUnit\TestFixture\TypeErrorTest" file="%sTypeErrorTest.php" tests="1" assertions="0" errors="1" failures="0" skipped="0" time="%f">
    <testcase name="testMe" file="%sTypeErrorTest.php" line="%d" class="PHPUnit\TestFixture\TypeErrorTest" classname="PHPUnit.TestFixture.TypeErrorTest" assertions="0" time="%f">
      <error type="TypeError">PHPUnit\TestFixture\TypeErrorTest::testMe
TypeError: Cannot assign DateTime to property PHPUnit\TestFixture\TypeErrorTest::$dateTime of type DateTimeImmutable

%sTypeErrorTest.php:%s</error>
    </testcase>
  </testsuite>
</testsuites>
