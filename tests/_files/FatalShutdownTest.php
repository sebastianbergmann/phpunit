<?php

class FatalShutdownTest extends PHPUnit_Framework_TestCase
{
    public function testWarning()
    {
        trigger_error('FatalShutdownTest warning');
    }

    public function testFatalError()
    {
        if (extension_loaded('xdebug')) {
            xdebug_disable();
        }

        eval('class FatalShutdownTest {}');
    }
}
