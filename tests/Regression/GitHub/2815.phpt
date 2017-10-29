--TEST--
GH-2815: exclude should work even if <exclude> if a parent of <directory>
--FILE--
<?php

$_SERVER['argv'][1] = '--configuration';
$_SERVER['argv'][2] = __DIR__ . '/2815/phpunit2815.xml';
$_SERVER['argv'][3] = '--debug';

require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %s



Time: %s, Memory: %s

No tests executed!
