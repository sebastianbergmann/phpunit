<?php
/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState enabled
 */
class Issue1335Test extends PHPUnit_Framework_TestCase
{
    function testGlobalString()
    {
        $this->assertEquals("Hello", $GLOBALS['globalString']);
    }

    function testGlobalIntTruthy()
    {
        $this->assertEquals(1, $GLOBALS['globalIntTruthy']);
    }

    function testGlobalIntFalsey()
    {
        $this->assertEquals(0, $GLOBALS['globalIntFalsey']);
    }

    function testGlobalFloat()
    {
        $this->assertEquals(1.123, $GLOBALS['globalFloat']);
    }

    function testGlobalBoolTrue()
    {
        $this->assertEquals(true, $GLOBALS['globalBoolTrue']);
    }

    function testGlobalBoolFalse()
    {
        $this->assertEquals(false, $GLOBALS['globalBoolFalse']);
    }

    function testGlobalNull()
    {
        $this->assertEquals(null, $GLOBALS['globalNull']);
    }

    function testGlobalArray()
    {
        $this->assertEquals(array("foo"), $GLOBALS['globalArray']);
    }

    function testGlobalNestedArray()
    {
        $this->assertEquals(array(array("foo")), $GLOBALS['globalNestedArray']);
    }

    function testGlobalObject()
    {
        $this->assertEquals((object)array("foo"=>"bar"), $GLOBALS['globalObject']);
    }

    function testGlobalObjectWithBackSlashString()
    {
        $this->assertEquals((object)array("foo"=>"back\\slash"), $GLOBALS['globalObjectWithBackSlashString']);
    }

    function testGlobalObjectWithDoubleBackSlashString()
    {
        $this->assertEquals((object)array("foo"=>"back\\\\slash"), $GLOBALS['globalObjectWithDoubleBackSlashString']);
    }
}
