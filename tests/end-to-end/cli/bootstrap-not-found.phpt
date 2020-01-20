--TEST--
Test fail on missing bootstrap
--ARGS--
--no-configuration --bootstrap nonExistingBootstrap.php
--FILE--
<?php declare(strict_types=1);
require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Cannot open file "nonExistingBootstrap.php".
