--TEST--
A test file PHPUnit warns about is loaded on every run, so the same command says the same thing twice
--FILE--
<?php declare(strict_types=1);
require __DIR__ . '/_files/setup.php';

$directory = setUpTestFiles();

// This file is in group b and is never selected by the runs below, but loading
// it makes PHPUnit warn about it.
writeTestClassThatWarns($directory, 'Two', 'b');

// The first run has to load it and warns
print warningsFor($directory, 'a');

// The second run knows about it, and warns just the same
print warningsFor($directory, 'a');

tearDownTestFiles($directory);
--EXPECTF--
There was 1 PHPUnit test runner warning:
Tests: 1, Assertions: 1, PHPUnit Warnings: 1.
There was 1 PHPUnit test runner warning:
Tests: 1, Assertions: 1, PHPUnit Warnings: 1.
