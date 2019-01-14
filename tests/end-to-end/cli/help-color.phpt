--TEST--
phpunit --help
--ARGS--
--no-configuration --help
--FILE--
<?php
require __DIR__ . '/../../bootstrap.php';
$help = new \Help(72, true);
$help->writeToConsole();
--EXPECTF_EXTERNAL--
_files/output-cli-help-color.txt
