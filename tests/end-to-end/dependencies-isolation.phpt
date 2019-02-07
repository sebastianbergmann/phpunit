--TEST--
phpunit --process-isolation --verbose DependencyTestSuite ../../_files/DependencyTestSuite.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--process-isolation';
$_SERVER['argv'][3] = '--verbose';
$_SERVER['argv'][4] = 'DependencyTestSuite';
$_SERVER['argv'][5] = __DIR__ . '/../_files/DependencyTestSuite.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

...FSSSW                                                            8 / 8 (100%)

Time: %s, Memory: %s

There was 1 warning:

1) DependencyFailureTest::testHandlesDependsAnnotationForNonexistentTests
This test depends on "DependencyFailureTest::doesNotExist" which does not exist.

--

There was 1 failure:

1) DependencyFailureTest::testOne

%s:%i

--

There were 3 skipped tests:

1) DependencyFailureTest::testTwo
This test depends on "DependencyFailureTest::testOne" to pass.

2) DependencyFailureTest::testThree
This test depends on "DependencyFailureTest::testTwo" to pass.

3) DependencyFailureTest::testFour
This test depends on "DependencyFailureTest::testOne" to pass.

FAILURES!
Tests: 8, Assertions: 4, Failures: 1, Warnings: 1, Skipped: 3.
