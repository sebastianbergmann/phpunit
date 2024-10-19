--TEST--
PHPUnit process is blocked when there's a lot of output and a test with separate process
--INI--
error_reporting=-1
display_errors=1
display_startup_errors=1
memory_limit=-1
zend.assertions=1
assert.exception=1
--SKIPIF--
<?php
for ($i = 0; $i < 390; $i++) {
    trigger_error("error $i");
}
?>
--FILE--
<?php

use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

final class Issue5993Test extends TestCase
{
    #[RunInSeparateProcess]
    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}
