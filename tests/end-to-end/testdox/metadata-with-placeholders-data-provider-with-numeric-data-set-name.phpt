--TEST--
TestDox: Default output; Data Provider with numeric data set name; TestDox metadata with placeholders
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--bootstrap';
$_SERVER['argv'][] = __DIR__ . '/_files/bootstrap.php';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = __DIR__ . '/_files/DataProviderWithNumericDataSetNameAndMetadataWithPlaceholdersTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

Time: %s, Memory: %s

Text from class-level TestDox metadata
 ✔ Text from method-level TestDox metadata for successful test with placeholders (string, 0, 0.0, array, true, bar, FOO)
 ✘ Text from method-level TestDox metadata for failing test with placeholders (string, 0, 0.0, array, true, bar, FOO)
   │
   │ Failed asserting that false is true.
   │
   │ %s:%d
   │

FAILURES!
Tests: 2, Assertions: 2, Failures: 1.
