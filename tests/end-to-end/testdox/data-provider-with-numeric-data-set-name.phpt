--TEST--
TestDox: Default output; Data Provider with numeric data set name; No TestDox metadata
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = __DIR__ . '/_files/DataProviderWithNumericDataSetNameTest.php';

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Data Provider With Numeric Data Set Name (PHPUnit\TestFixture\TestDox\DataProviderWithNumericDataSetName)
 ✔ Something that works with data set #0
 ✘ Something that does not work with data set #0
   │
   │ Failed asserting that false is true.
   │
   │ %s:%d
   │

Time: %s, Memory: %s

Summary of non-successful tests:

Data Provider With Numeric Data Set Name (PHPUnit\TestFixture\TestDox\DataProviderWithNumericDataSetName)
 ✘ Something that does not work with data set #0
   │
   │ Failed asserting that false is true.
   │
   │ %s:%d
   │

FAILURES!
Tests: 2, Assertions: 2, Failures: 1.
