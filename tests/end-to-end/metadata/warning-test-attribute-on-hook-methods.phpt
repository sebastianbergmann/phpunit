--TEST--
phpunit ../_files/size-combinations/SmallMediumTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/test-attribute-on-hook-methods/TestAttributeOnHookMethodsTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There were 12 PHPUnit test runner warnings:

1) Method PHPUnit\TestFixture\AttributesOnTemplateMethods\TestAttributeOnHookMethodsTest::before_class() cannot be used both as a hook method and as a test method

2) Method PHPUnit\TestFixture\AttributesOnTemplateMethods\TestAttributeOnHookMethodsTest::after_class() cannot be used both as a hook method and as a test method

3) Method PHPUnit\TestFixture\AttributesOnTemplateMethods\TestAttributeOnHookMethodsTest::setUpBeforeClass() cannot be used both as a hook method and as a test method

4) Method PHPUnit\TestFixture\AttributesOnTemplateMethods\TestAttributeOnHookMethodsTest::tearDownAfterClass() cannot be used both as a hook method and as a test method

5) Method PHPUnit\TestFixture\AttributesOnTemplateMethods\TestAttributeOnHookMethodsTest::setUp() cannot be used both as a hook method and as a test method

6) Method PHPUnit\TestFixture\AttributesOnTemplateMethods\TestAttributeOnHookMethodsTest::assertPreConditions() cannot be used both as a hook method and as a test method

7) Method PHPUnit\TestFixture\AttributesOnTemplateMethods\TestAttributeOnHookMethodsTest::assertPostConditions() cannot be used both as a hook method and as a test method

8) Method PHPUnit\TestFixture\AttributesOnTemplateMethods\TestAttributeOnHookMethodsTest::tearDown() cannot be used both as a hook method and as a test method

9) Method PHPUnit\TestFixture\AttributesOnTemplateMethods\TestAttributeOnHookMethodsTest::before_method() cannot be used both as a hook method and as a test method

10) Method PHPUnit\TestFixture\AttributesOnTemplateMethods\TestAttributeOnHookMethodsTest::pre_condition() cannot be used both as a hook method and as a test method

11) Method PHPUnit\TestFixture\AttributesOnTemplateMethods\TestAttributeOnHookMethodsTest::post_condition() cannot be used both as a hook method and as a test method

12) Method PHPUnit\TestFixture\AttributesOnTemplateMethods\TestAttributeOnHookMethodsTest::after_method() cannot be used both as a hook method and as a test method

OK, but there were issues!
Tests: 1, Assertions: 1, PHPUnit Warnings: 12.
