--TEST--
phpunit ../_files/size-groups/SizeGroupsTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/size-groups/SizeGroupsTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There were 6 PHPUnit test runner warnings:

1) Group name "small" is not allowed for class PHPUnit\TestFixture\SizeGroups\SizeGroupsTest

2) Group name "medium" is not allowed for class PHPUnit\TestFixture\SizeGroups\SizeGroupsTest

3) Group name "large" is not allowed for class PHPUnit\TestFixture\SizeGroups\SizeGroupsTest

4) Group name "small" is not allowed for method PHPUnit\TestFixture\SizeGroups\SizeGroupsTest::testOne

5) Group name "medium" is not allowed for method PHPUnit\TestFixture\SizeGroups\SizeGroupsTest::testOne

6) Group name "large" is not allowed for method PHPUnit\TestFixture\SizeGroups\SizeGroupsTest::testOne

WARNINGS!
Tests: 1, Assertions: 1, Warnings: 6.
