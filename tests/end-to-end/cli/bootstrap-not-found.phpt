--TEST--
Test fail on missing bootstrap
--ARGS--
--no-configuration --bootstrap nonExistingBootstrap.php
--FILE--
<?php declare(strict_types=1);
require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Application::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Cannot open bootstrap script "nonExistingBootstrap.php"
