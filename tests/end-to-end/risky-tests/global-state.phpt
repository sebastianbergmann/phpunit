--TEST--
Changes to global state are reported when backupGlobals="true" and beStrictAboutChangesToGlobalState="true" are configured
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/global-state/phpunit.xml';

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

R                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 risky test:

1) PHPUnit\TestFixture\RiskyTests\GlobalStateManipulatorTest::testManipulatesGlobalState
--- Global variables before the test
+++ Global variables after the test
@@ @@
%A
+    'foo' => 'bar'
%A

OK, but incomplete, skipped, or risky tests!
Tests: 1, Assertions: 1, Risky: 1.
