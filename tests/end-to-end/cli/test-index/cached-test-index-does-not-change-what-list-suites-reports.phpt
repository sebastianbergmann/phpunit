--TEST--
The test index does not change how many tests --list-suites reports
--FILE--
<?php declare(strict_types=1);
require __DIR__ . '/_files/setup.php';

$directory = setUpTestFiles();

// --list-suites ignores --group, and reports every test the test suite has
$cold = listSuites($directory, 'a');

// This is the run that indexes the test files: it selects tests by group
listTests($directory, 'a');

// The index now knows that TwoTest.php has no test in group a, which is not a
// reason for --list-suites to leave the tests in that file out of its count
$warm = listSuites($directory, 'a');

print $cold === $warm ? "cold and warm run report the same number of tests\n" : "REPORT DIFFERS\n" . $cold . "---\n" . $warm;
print $cold;

tearDownTestFiles($directory);
--EXPECT--
cold and warm run report the same number of tests
 - default (2 tests)
