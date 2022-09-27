--TEST--
Test incorrect testFile is reported
--ARGS--
--no-configuration nonExistingFile.php
--FILE--
<?php declare(strict_types=1);
require_once __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Cannot open file "nonExistingFile.php".
