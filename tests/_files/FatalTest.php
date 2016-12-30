<?php

use PHPUnit\Framework\TestCase;

class FatalTest extends TestCase
{
    public function testFatalError()
    {
        if (extension_loaded('xdebug')) {
            xdebug_disable();
        }

        eval('class FatalTest {}');
    }
}
