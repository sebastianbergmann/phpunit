--TEST--
PHPT test that is run without a code coverage driver
--INI--
pcov.enabled=0
xdebug.mode=off
--ENV--
XDEBUG_MODE=off
--FILE--
<?php declare(strict_types=1);
require __DIR__ . '/src/Covered.php';

var_dump((new PHPUnit\TestFixture\PhptCodeCoverageDriverMissingInChildProcess\Covered)->doSomething());
--EXPECT--
bool(true)
