--TEST--
A run for which no test file has to be loaded reports that no tests were executed, and does not show the help
--FILE--
<?php declare(strict_types=1);
require __DIR__ . '/_files/setup.php';

$directory = setUpTestFiles();

// Neither of the two test files can contribute a test to a run that selects a
// group that does not exist. The first run has to load them to find that out.
print resultForTestFilesFile($directory, 'c');

// The second run knows that neither file has to be loaded, so the test suite
// that is built is empty. Tests were selected for it all the same.
print resultForTestFilesFile($directory, 'c');

tearDownTestFiles($directory);
--EXPECTF--
No tests executed!
No tests executed!
