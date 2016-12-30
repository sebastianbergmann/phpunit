<?php
use PHPUnit\Framework\TestCase;

class CoverageMethodTest extends TestCase
{
    /**
     * @covers CoveredClass::publicMethod
     */
    public function testSomething()
    {
        $o = new CoveredClass;
        $o->publicMethod();
    }
}
