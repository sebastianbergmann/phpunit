--TEST--
phpunit --no-configuration ../_files/RequirementsTest.php
--FILE--
<?php declare(strict_types=1);
require_once(__DIR__ . '/../bootstrap.php');

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = \realpath(__DIR__ . '/../_files/RequirementsTest.php');

PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

RRRRRRRRSSSSSRRRRSSSSRSSSSSSSRRSSSSSSSRRSSSSSSSSSSSSSSSSSSSWWS    62 / 62 (100%)

Time: %s, Memory: %s

There were 2 warnings:

1) PHPUnit\TestFixture\RequirementsTest::testVersionConstraintInvalidPhpConstraint
Version constraint ~^12345 is not supported.
%a
2) PHPUnit\TestFixture\RequirementsTest::testVersionConstraintInvalidPhpUnitConstraint
Version constraint ~^12345 is not supported.
%a
--

There were 17 risky tests:

1) PHPUnit\TestFixture\RequirementsTest::testOne
This test did not perform any assertions

%stests%e_files%eRequirementsTest.php:%d

2) PHPUnit\TestFixture\RequirementsTest::testTwo
This test did not perform any assertions

%stests%e_files%eRequirementsTest.php:%d

3) PHPUnit\TestFixture\RequirementsTest::testThree
This test did not perform any assertions

%stests%e_files%eRequirementsTest.php:%d

4) PHPUnit\TestFixture\RequirementsTest::testFour
This test did not perform any assertions

%stests%e_files%eRequirementsTest.php:%d

5) PHPUnit\TestFixture\RequirementsTest::testFive
This test did not perform any assertions

%stests%e_files%eRequirementsTest.php:%d

6) PHPUnit\TestFixture\RequirementsTest::testSix
This test did not perform any assertions

%stests%e_files%eRequirementsTest.php:%d

7) PHPUnit\TestFixture\RequirementsTest::testSeven
This test did not perform any assertions

%stests%e_files%eRequirementsTest.php:%d

8) PHPUnit\TestFixture\RequirementsTest::testEight
This test did not perform any assertions

%stests%e_files%eRequirementsTest.php:%d

9) PHPUnit\TestFixture\RequirementsTest::testExistingFunction
This test did not perform any assertions

%stests%e_files%eRequirementsTest.php:%d

10) PHPUnit\TestFixture\RequirementsTest::testExistingMethod
This test did not perform any assertions

%stests%e_files%eRequirementsTest.php:%d

11) PHPUnit\TestFixture\RequirementsTest::testExistingExtension
This test did not perform any assertions

%stests%e_files%eRequirementsTest.php:%d

12) PHPUnit\TestFixture\RequirementsTest::testExistingOs
This test did not perform any assertions

%stests%e_files%eRequirementsTest.php:%d

13) PHPUnit\TestFixture\RequirementsTest::testSpace
This test did not perform any assertions

%stests%e_files%eRequirementsTest.php:%d

14) PHPUnit\TestFixture\RequirementsTest::testPHPVersionOperatorBangEquals
This test did not perform any assertions

%stests%e_files%eRequirementsTest.php:%d

15) PHPUnit\TestFixture\RequirementsTest::testPHPVersionOperatorNotEquals
This test did not perform any assertions

%stests%e_files%eRequirementsTest.php:%d

16) PHPUnit\TestFixture\RequirementsTest::testPHPUnitVersionOperatorBangEquals
This test did not perform any assertions

%stests%e_files%eRequirementsTest.php:%d

17) PHPUnit\TestFixture\RequirementsTest::testPHPUnitVersionOperatorNotEquals
This test did not perform any assertions

%stests%e_files%eRequirementsTest.php:%d

WARNINGS!
Tests: 62, Assertions: 0, Warnings: 2, Skipped: 43, Risky: 17.
