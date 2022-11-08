--TEST--
TestDox: Default output; Test name in camel-case notation; No TestDox metadata
--XFAIL--
TestDox logging has not been migrated to events yet.
See https://github.com/sebastianbergmann/phpunit/issues/5040 for details.
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = __DIR__ . '/_files/CamelCaseTest.php';

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Application::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Camel Case (PHPUnit\TestFixture\TestDox\CamelCase)
 ✔ Something that works
 ✘ Something that does not work
   │
   │ Failed asserting that false is true.
   │
   │ %sCamelCaseTest.php:%d
   │

Time: %s, Memory: %s

Summary of non-successful tests:

Camel Case (PHPUnit\TestFixture\TestDox\CamelCase)
 ✘ Something that does not work
   │
   │ Failed asserting that false is true.
   │
   │ %sCamelCaseTest.php:%d
   │

FAILURES!
Tests: 2, Assertions: 2, Failures: 1.
