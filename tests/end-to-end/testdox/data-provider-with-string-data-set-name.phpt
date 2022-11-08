--TEST--
TestDox: Default output; Data Provider with numeric data set name; No TestDox metadata
--XFAIL--
TestDox logging has not been migrated to events yet.
See https://github.com/sebastianbergmann/phpunit/issues/5040 for details.
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = __DIR__ . '/_files/DataProviderWithStringDataSetNameTest.php';

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Application::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Data Provider With String Data Set Name (PHPUnit\TestFixture\TestDox\DataProviderWithStringDataSetName)
 ✔ Something that works with data set "data set name"
 ✘ Something that does not work with data set "data set name"
   │
   │ Failed asserting that false is true.
   │
   │ %s:%d
   │

Time: %s, Memory: %s

Summary of non-successful tests:

Data Provider With String Data Set Name (PHPUnit\TestFixture\TestDox\DataProviderWithStringDataSetName)
 ✘ Something that does not work with data set "data set name"
   │
   │ Failed asserting that false is true.
   │
   │ %s:%d
   │

FAILURES!
Tests: 2, Assertions: 2, Failures: 1.
