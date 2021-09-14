--TEST--
phpunit --process-isolation --verbose ../../_files/dependencies
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--process-isolation';
$_SERVER['argv'][] = '--verbose';
$_SERVER['argv'][] = \realpath(__DIR__ . '/../../_files/dependencies');

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Application::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

...FSSSEE                                                           9 / 9 (100%)

Time: %s, Memory: %s

There were 2 errors:

1) DependencyFailureTest::testHandlesDependsAnnotationForNonexistentTests
This test depends on "DependencyFailureTest::doesNotExist" which does not exist

2) DependencyFailureTest::testHandlesDependsAnnotationWithNoMethodSpecified
This test has an invalid dependency

--

There was 1 failure:

1) DependencyFailureTest::testOne

%s:%i

--

There were 3 skipped tests:

1) DependencyFailureTest::testTwo
This test depends on "DependencyFailureTest::testOne" to pass

2) DependencyFailureTest::testThree
This test depends on "DependencyFailureTest::testTwo" to pass

3) DependencyFailureTest::testFour
This test depends on "DependencyFailureTest::testOne" to pass

ERRORS!
Tests: 9, Assertions: 4, Errors: 2, Failures: 1, Skipped: 3.
