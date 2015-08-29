<?php

class IgnoreCoverageClassTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers IgnoreCoverageClass
     */
    public function testSomething()
    {
        $o = new IgnoreCoverageClass;
        $o->publicMethod();
    }
}
