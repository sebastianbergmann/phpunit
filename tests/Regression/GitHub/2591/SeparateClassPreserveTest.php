<?php
use PHPUnit\Framework\TestCase;

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
        $value = 'Hello! I am changed from inside!';

        $GLOBALS['globalString'] = $value;
        $this->assertEquals($value, $GLOBALS['globalString']);
    }

    public function testGlobalString()
    {
        $this->assertEquals('Hello', $GLOBALS['globalString']);
    }

}
