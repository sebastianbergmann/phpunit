--TEST--
Test fail on missing bootstrap
--ARGS--
--no-configuration --bootstrap nonExistingBootstrap.php
--FILE--
<?php declare(strict_types=1);
require_once __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s #StandWithUkraine

Cannot open file "nonExistingBootstrap.php".
