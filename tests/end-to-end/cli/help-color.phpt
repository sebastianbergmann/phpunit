--TEST--
phpunit --help
--ARGS--
--no-configuration --help
--FILE--
<?php declare(strict_types=1);
require __DIR__ . '/../../bootstrap.php';

$help = new PHPUnit\TextUI\Help(72, true);

$help->writeToConsole();
--EXPECTF_EXTERNAL--
_files/output-cli-help-color.txt
