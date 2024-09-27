--TEST--
phpunit --group small,medium,large ../../_files/size-groups/SizeGroupsTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--group';
$_SERVER['argv'][] = 'small,medium,large';
$_SERVER['argv'][] = __DIR__ . '/../../_files/size-groups/SizeGroupsTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

There were 7 PHPUnit test runner warnings:

1) Using comma-separated values with --group is deprecated and will no longer work in PHPUnit 12. You can use --group multiple times instead.

2) Group name "small" is not allowed for class PHPUnit\TestFixture\SizeGroups\SizeGroupsTest

3) Group name "medium" is not allowed for class PHPUnit\TestFixture\SizeGroups\SizeGroupsTest

4) Group name "large" is not allowed for class PHPUnit\TestFixture\SizeGroups\SizeGroupsTest

5) Group name "small" is not allowed for method PHPUnit\TestFixture\SizeGroups\SizeGroupsTest::testOne

6) Group name "medium" is not allowed for method PHPUnit\TestFixture\SizeGroups\SizeGroupsTest::testOne

7) Group name "large" is not allowed for method PHPUnit\TestFixture\SizeGroups\SizeGroupsTest::testOne

No tests executed!
