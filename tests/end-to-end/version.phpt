--TEST--
phpunit --version
--FILE--
<?php
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--version';

require __DIR__ . '/../bootstrap.php';

PHPUnit\TextUI\Application::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.
