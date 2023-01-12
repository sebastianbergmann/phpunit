--TEST--
Test incorrect testFile is reported
--ARGS--
--no-configuration nonExistingFile.php
--FILE--
<?php declare(strict_types=1);
require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Test file "nonExistingFile.php" not found
