--TEST--
Support --enforce-time-limit with specified file
--FILE--
<?php
require __DIR__ . '/../bootstrap.php';

$_SERVER['argv'][] = ''; // present to start index at 0
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--enforce-time-limit';
$_SERVER['argv'][] = __DIR__ . '/_files/AlwaysPass.php';

PHPUnit\TextUI\Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.                                                                   1 / 1 (100%)

Time: %s ms, Memory: %sMB

OK (1 test, 1 assertion)
