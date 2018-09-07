<?php
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState enabled
 */
class Issue2591_SeparateFunctionPreserveTest extends TestCase
{
    public function testChangedGlobalString()
    {
        $GLOBALS['globalString'] = 'Hello!';
        $this->assertEquals('Hello!', $GLOBALS['globalString']);
    }

    public function testGlobalString()
    {
        $this->assertEquals('Hello', $GLOBALS['globalString']);
    }

}
