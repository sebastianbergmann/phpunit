--TEST--
A test file is not skipped when the filter can only match through the name of a data set
--FILE--
<?php declare(strict_types=1);
require __DIR__ . '/_files/setup.php';

$directory = setUpTestFiles();

writeTestClassWithDataProvider($directory, 'Three', 'a very unusual data set name');

// The name of the data set is not in the index: it is only known once the data
// provider has been invoked, so the file has to be loaded to find this test.
print listTestsMatching($directory, 'a very unusual data set name');

// The same filter again, now that the index knows about all three files
print listTestsMatching($directory, 'a very unusual data set name');

tearDownTestFiles($directory);
--EXPECTF--
 - PHPUnit\TestFixture\TestIndex\ThreeTest::testWithDataSet"a very unusual data set name"
 - PHPUnit\TestFixture\TestIndex\ThreeTest::testWithDataSet"a very unusual data set name"
