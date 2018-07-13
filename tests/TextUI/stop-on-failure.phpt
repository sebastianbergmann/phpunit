--TEST--
Support --stop-on-failure long option.
--FILE--
<?php
$_SERVER['argv'][] = ''; // present to start index at 0
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'Fail';
$_SERVER['argv'][] = '--stop-on-failure';
$_SERVER['argv'][] = __DIR__ . '/_files/StopOn.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

F

Time: %s, Memory: %s

There was 1 failure:

1) StopOn::testShouldFail
Always fail

%s/StopOn.php:%s

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.

