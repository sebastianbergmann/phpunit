--TEST--
phpunit --verbose --order-by=depends,reverse ../execution-order/_files/MultiDependencyTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--verbose';
$_SERVER['argv'][] = '--order-by=depends,size';
$_SERVER['argv'][] = \realpath(__DIR__ . '/../../_files/TestWithDifferentSizes.php');

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

Test 'PHPUnit\TestFixture\TestWithDifferentSizes::testDataProviderWithSizeSmall with data set #0 (false)' started
Test 'PHPUnit\TestFixture\TestWithDifferentSizes::testDataProviderWithSizeSmall with data set #0 (false)' ended
Test 'PHPUnit\TestFixture\TestWithDifferentSizes::testDataProviderWithSizeSmall with data set #1 (true)' started
Test 'PHPUnit\TestFixture\TestWithDifferentSizes::testDataProviderWithSizeSmall with data set #1 (true)' ended
Test 'PHPUnit\TestFixture\TestWithDifferentSizes::testDataProviderWithSizeMedium with data set #0 (false)' started
Test 'PHPUnit\TestFixture\TestWithDifferentSizes::testDataProviderWithSizeMedium with data set #0 (false)' ended
Test 'PHPUnit\TestFixture\TestWithDifferentSizes::testDataProviderWithSizeMedium with data set #1 (true)' started
Test 'PHPUnit\TestFixture\TestWithDifferentSizes::testDataProviderWithSizeMedium with data set #1 (true)' ended
Test 'PHPUnit\TestFixture\TestWithDifferentSizes::testWithSizeMedium' started
Test 'PHPUnit\TestFixture\TestWithDifferentSizes::testWithSizeMedium' ended
Test 'PHPUnit\TestFixture\TestWithDifferentSizes::testWithSizeLarge' started
Test 'PHPUnit\TestFixture\TestWithDifferentSizes::testWithSizeLarge' ended
Test 'PHPUnit\TestFixture\TestWithDifferentSizes::testWithSizeUnknown' started
Test 'PHPUnit\TestFixture\TestWithDifferentSizes::testWithSizeUnknown' ended


Time: %s, Memory: %s

There was 1 skipped test:

1) PHPUnit\TestFixture\TestWithDifferentSizes::testWithSizeSmall
This test depends on a test that is larger than itself.

OK, but incomplete, skipped, or risky tests!
Tests: 7, Assertions: 7, Skipped: 1.
