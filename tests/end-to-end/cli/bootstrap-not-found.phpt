--TEST--
Test fail on missing bootstrap
--ARGS--
--no-configuration --bootstrap nonExistingBootstrap.php
--FILE--
<?php declare(strict_types=1);
require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Cannot open bootstrap script "nonExistingBootstrap.php"
