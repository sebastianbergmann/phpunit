--TEST--
phpunit -c ../../_files/configuration.depends-on-class.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '-c';
$_SERVER['argv'][] = \realpath(__DIR__ . '/../../_files/configuration.depends-on-class.xml');


require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %s%etests%e_files%econfiguration.depends-on-class.xml

....SFSSSWS                                                       11 / 11 (100%)

Time: %s, Memory: %s

There was 1 warning:

1) PHPUnit\TestFixture\DependencyFailureTest::testHandlesDependsAnnotationForNonexistentTests
This test depends on "PHPUnit\TestFixture\DependencyFailureTest::doesNotExist" which does not exist.

--

There was 1 failure:

1) PHPUnit\TestFixture\DependencyFailureTest::testOne
Failed asserting that false is true.

%s%etests%e_files%eDependencyFailureTest.php:18

--

There were 5 skipped tests:

1) PHPUnit\TestFixture\DependencyOnClassTest::testThatDependsOnAFailingClass
This test depends on "PHPUnit\TestFixture\DependencyFailureTest::class" to pass.

2) PHPUnit\TestFixture\DependencyFailureTest::testTwo
This test depends on "PHPUnit\TestFixture\DependencyFailureTest::testOne" to pass.

3) PHPUnit\TestFixture\DependencyFailureTest::testThree
This test depends on "PHPUnit\TestFixture\DependencyFailureTest::testTwo" to pass.

4) PHPUnit\TestFixture\DependencyFailureTest::testFour
This test depends on "PHPUnit\TestFixture\DependencyFailureTest::testOne" to pass.

5) PHPUnit\TestFixture\DependencyFailureTest::testHandlesDependsAnnotationWithNoMethodSpecified
This method has an invalid @depends annotation.

FAILURES!
Tests: 11, Assertions: 5, Failures: 1, Warnings: 1, Skipped: 5.
