--TEST--
phpunit --verbose --order-by=depends,reverse ../execution-order/_files/MultiDependencyTest.php
--FILE--
<?php declare(strict_types=1);
$arguments = [
    '--no-configuration',
    '--debug',
    '--verbose',
    '--order-by=depends,size',
    'TestWithDifferentSizes',
    \realpath(__DIR__ . '/../../_files/TestWithDifferentSizes.php'),
];
\array_splice($_SERVER['argv'], 1, count($arguments), $arguments);

require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

Test 'TestWithDifferentSizes::testWithSizeSmall' started
Test 'TestWithDifferentSizes::testWithSizeSmall' ended
Test 'TestWithDifferentSizes::testDataProviderWithSizeSmall with data set #0 (false)' started
Test 'TestWithDifferentSizes::testDataProviderWithSizeSmall with data set #0 (false)' ended
Test 'TestWithDifferentSizes::testDataProviderWithSizeSmall with data set #1 (true)' started
Test 'TestWithDifferentSizes::testDataProviderWithSizeSmall with data set #1 (true)' ended
Test 'TestWithDifferentSizes::testDataProviderWithSizeMedium with data set #0 (false)' started
Test 'TestWithDifferentSizes::testDataProviderWithSizeMedium with data set #0 (false)' ended
Test 'TestWithDifferentSizes::testDataProviderWithSizeMedium with data set #1 (true)' started
Test 'TestWithDifferentSizes::testDataProviderWithSizeMedium with data set #1 (true)' ended
Test 'TestWithDifferentSizes::testWithSizeMedium' started
Test 'TestWithDifferentSizes::testWithSizeMedium' ended
Test 'TestWithDifferentSizes::testWithSizeLarge' started
Test 'TestWithDifferentSizes::testWithSizeLarge' ended
Test 'TestWithDifferentSizes::testWithSizeUnknown' started
Test 'TestWithDifferentSizes::testWithSizeUnknown' ended


Time: %s, Memory: %s

OK (8 tests, 8 assertions)
