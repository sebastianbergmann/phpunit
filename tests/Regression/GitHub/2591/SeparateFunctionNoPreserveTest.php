<?php
use PhpUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class Issue2591Test extends TestCase
{
    public function testChangedGlobalString()
    {
        $GLOBALS['globalString'] = "Hello!";
        $this->assertEquals('Hello!', $GLOBALS['globalString']);
    }

    public function testGlobalString()
    {
        $this->assertEquals('Hello', $GLOBALS['globalString']);
    }

}