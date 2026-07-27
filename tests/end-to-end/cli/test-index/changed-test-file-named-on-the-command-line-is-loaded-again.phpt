--TEST--
A test file that changed after it was indexed is loaded again when a directory is named on the command line
--FILE--
<?php declare(strict_types=1);
require __DIR__ . '/_files/setup.php';

$directory = setUpTestFiles();

// Index both test files. TwoTest.php is in group b and is not selected here.
print listTestsInDirectory($directory, 'a');

// Move the test in TwoTest.php into group a. What the index knows about that
// file is now wrong: were it believed, the test would not be found.
writeTestClass($directory, 'Two', 'a');

print listTestsInDirectory($directory, 'a');

tearDownTestFiles($directory);
--EXPECTF--
 - PHPUnit\TestFixture\TestIndex\OneTest::testInGroupa
 - PHPUnit\TestFixture\TestIndex\OneTest::testInGroupa
 - PHPUnit\TestFixture\TestIndex\TwoTest::testInGroupa
