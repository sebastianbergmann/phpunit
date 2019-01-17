--TEST--
phpunit --no-configuration RequirementsTest ../_files/RequirementsTest.php
--FILE--
<?php declare(strict_types=1);
require_once(__DIR__ . '/../bootstrap.php');

$arguments = [
    '--no-configuration',
    'RequirementsTest',
    \realpath(__DIR__ . '/../_files/RequirementsTest.php'),
];
\array_splice($_SERVER['argv'], 1, count($arguments), $arguments);

PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

RRRRRRRRSSSSSRRRRSSSSRSSSSSSSRRSSSSSSSRRSSSSSSSSSSSSSSSSSSSWWS    62 / 62 (100%)

Time: %s, Memory: %s

There were 2 warnings:

1) RequirementsTest::testVersionConstraintInvalidPhpConstraint
Version constraint ~^12345 is not supported.
%a
2) RequirementsTest::testVersionConstraintInvalidPhpUnitConstraint
Version constraint ~^12345 is not supported.
%a
--

There were 17 risky tests:

1) RequirementsTest::testOne
This test did not perform any assertions

%stests%e_files%eRequirementsTest.php:14

2) RequirementsTest::testTwo
This test did not perform any assertions

%stests%e_files%eRequirementsTest.php:21

3) RequirementsTest::testThree
This test did not perform any assertions

%stests%e_files%eRequirementsTest.php:28

4) RequirementsTest::testFour
This test did not perform any assertions

%stests%e_files%eRequirementsTest.php:36

5) RequirementsTest::testFive
This test did not perform any assertions

%stests%e_files%eRequirementsTest.php:43

6) RequirementsTest::testSix
This test did not perform any assertions

%stests%e_files%eRequirementsTest.php:50

7) RequirementsTest::testSeven
This test did not perform any assertions

%stests%e_files%eRequirementsTest.php:57

8) RequirementsTest::testEight
This test did not perform any assertions

%stests%e_files%eRequirementsTest.php:64

9) RequirementsTest::testExistingFunction
This test did not perform any assertions

%stests%e_files%eRequirementsTest.php:117

10) RequirementsTest::testExistingMethod
This test did not perform any assertions

%stests%e_files%eRequirementsTest.php:124

11) RequirementsTest::testExistingExtension
This test did not perform any assertions

%stests%e_files%eRequirementsTest.php:131

12) RequirementsTest::testExistingOs
This test did not perform any assertions

%stests%e_files%eRequirementsTest.php:138

13) RequirementsTest::testSpace
This test did not perform any assertions

%stests%e_files%eRequirementsTest.php:174

14) RequirementsTest::testPHPVersionOperatorBangEquals
This test did not perform any assertions

%stests%e_files%eRequirementsTest.php:237

15) RequirementsTest::testPHPVersionOperatorNotEquals
This test did not perform any assertions

%stests%e_files%eRequirementsTest.php:245

16) RequirementsTest::testPHPUnitVersionOperatorBangEquals
This test did not perform any assertions

%stests%e_files%eRequirementsTest.php:309

17) RequirementsTest::testPHPUnitVersionOperatorNotEquals
This test did not perform any assertions

%stests%e_files%eRequirementsTest.php:317

WARNINGS!
Tests: 62, Assertions: 0, Warnings: 2, Skipped: 43, Risky: 17.
