--TEST--
TestDox: Verbose output; Test name in camel-case notation; No TestDox metadata
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--verbose';
$_SERVER['argv'][] = __DIR__ . '/_files/CamelCaseTest.php';

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

Camel Case (PHPUnit\TestFixture\TestDox\CamelCase)
 ✔ Something that works [%s]
 ✘ Something that does not work [%s]
   │
   │ Failed asserting that false is true.
   │
   │ %sCamelCaseTest.php:%d
   │

Time: %s, Memory: %s

Summary of non-successful tests:

Camel Case (PHPUnit\TestFixture\TestDox\CamelCase)
 ✘ Something that does not work [%s]
   │
   │ Failed asserting that false is true.
   │
   │ %sCamelCaseTest.php:%d
   │

FAILURES!
Tests: 2, Assertions: 2, Failures: 1.
