--TEST--
TestDox: Test methods are sorted by their location in the source code, not by the order in which they were run
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--order-by=reverse';
$_SERVER['argv'][] = __DIR__ . '/_files/method-order-source-location';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

Time: %s, Memory: %s

Source Location (PHPUnit\TestFixture\TestDox\MethodOrderSourceLocation\SourceLocation)
 ✔ Declared first
 ✔ Declared second
 ✔ Declared third

OK (3 tests, 3 assertions)
