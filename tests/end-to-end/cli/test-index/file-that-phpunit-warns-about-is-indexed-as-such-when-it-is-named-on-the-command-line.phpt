--TEST--
A test file PHPUnit has something to say about while loading it is indexed as such when it is named on the command line
--FILE--
<?php declare(strict_types=1);
require __DIR__ . '/_files/setup.php';

$directory = setUpTestFiles();

// This file is in group b and is not selected by the second run below, but
// loading it triggers a deprecation.
writeTestClassThatDeprecatesWhileItIsLoaded($directory, 'Two', 'b');

// Naming the file on its own loads it, reports the deprecation, and indexes it
print issuesForFile($directory, 'Two');

// The index knows that PHPUnit has something to say about the file, so it is
// loaded here as well, and the deprecation is reported just the same
print warningsFor($directory, 'a');

tearDownTestFiles($directory);
--EXPECTF--
Tests: 1, Assertions: 1, Deprecations: 1.
Tests: 1, Assertions: 1, Deprecations: 1.
