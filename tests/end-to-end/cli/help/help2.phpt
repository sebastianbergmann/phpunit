--TEST--
phpunit --help
--ARGS--
--no-configuration --columns=80 --help
--FILE--
<?php declare(strict_types=1);
require_once __DIR__ . '/../../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF_EXTERNAL--
../../_files/output-cli-usage.txt
