<?php

class FullCoverageClassTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers FullCoverageClass
     */
    public function testSomething()
    {
        $o = new FullCoverageClass;
        $o->publicMethod();
    }
}
