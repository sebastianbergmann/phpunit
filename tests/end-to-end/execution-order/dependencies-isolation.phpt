--TEST--
phpunit --process-isolation --verbose ../../_files/DependencyTestSuite.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--process-isolation';
$_SERVER['argv'][] = '--verbose';
$_SERVER['argv'][] = \realpath(__DIR__ . '/_files/DependencyTestSuite.php');

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

...FSSSWS                                                           9 / 9 (100%)

Time: %s, Memory: %s

There was 1 warning:

1) DependencyFailureTest::testHandlesDependsAnnotationForNonexistentTests
This test depends on "DependencyFailureTest::doesNotExist" which does not exist.

--

There was 1 failure:

1) DependencyFailureTest::testOne

%s:%i

--

There were 4 skipped tests:

1) DependencyFailureTest::testTwo
This test depends on "DependencyFailureTest::testOne" to pass.

2) DependencyFailureTest::testThree
This test depends on "DependencyFailureTest::testTwo" to pass.

3) DependencyFailureTest::testFour
This test depends on "DependencyFailureTest::testOne" to pass.

4) DependencyFailureTest::testHandlesDependsAnnotationWithNoMethodSpecified
This method has an invalid @depends annotation.

FAILURES!
Tests: 9, Assertions: 4, Failures: 1, Warnings: 1, Skipped: 4.
