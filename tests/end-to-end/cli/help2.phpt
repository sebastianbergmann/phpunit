--TEST--
phpunit --help
--ARGS--
--no-configuration --help
--FILE--
<?php
require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF_EXTERNAL--
_files/output-cli-usage.txt
