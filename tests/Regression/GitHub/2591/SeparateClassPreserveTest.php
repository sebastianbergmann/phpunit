<?php
use PhpUnit\Framework\TestCase;

/**
 * @runClassInSeparateProcess
 * @preserveGlobalState enabled
 */
class Issue2591_SeparateClassPreserveTest extends TestCase
{
    public function testOriginalGlobalString()
    {
        $this->assertEquals('Hello', $GLOBALS['globalString']);
    }

    public function testChangedGlobalString()
    {
        $GLOBALS['globalString'] = "Hello!";
        $this->assertEquals('Hello!', $GLOBALS['globalString']);
    }

    public function testGlobalString()
    {
        $this->assertEquals('Hello!', $GLOBALS['globalString']);
    }

}