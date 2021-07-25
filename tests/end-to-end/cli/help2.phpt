--TEST--
phpunit --help
--ARGS--
--no-configuration --help
--FILE--
<?php declare(strict_types=1);
require_once __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF_EXTERNAL--
_files/output-cli-usage.txt
