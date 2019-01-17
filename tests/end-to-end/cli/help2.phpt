--TEST--
phpunit --help
--ARGS--
--no-configuration --help
--FILE--
<?php declare(strict_types=1);
require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF_EXTERNAL--
_files/output-cli-usage.txt
