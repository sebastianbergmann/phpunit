--TEST--
TestDox: Test methods are grouped by their declaring class, with inherited test methods listed before the ones declared in the test class itself
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--order-by=reverse';
$_SERVER['argv'][] = __DIR__ . '/_files/method-order-inheritance';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

Time: %s, Memory: %s

Inheritance (PHPUnit\TestFixture\TestDox\MethodOrderInheritance\Inheritance)
 ✔ Declared first in parent
 ✔ Declared second in parent
 ✔ Declared first in child
 ✔ Declared second in child

OK (4 tests, 4 assertions)
