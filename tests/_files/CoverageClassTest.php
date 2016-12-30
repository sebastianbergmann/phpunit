<?php
use PHPUnit\Framework\TestCase;

class CoverageClassTest extends TestCase
{
    /**
     * @covers CoveredClass
     */
    public function testSomething()
    {
        $o = new CoveredClass;
        $o->publicMethod();
    }
}
