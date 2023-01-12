--TEST--
phpunit --version
--FILE--
<?php
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--version';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.
