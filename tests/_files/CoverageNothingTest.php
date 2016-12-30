<?php
use PHPUnit\Framework\TestCase;

class CoverageNothingTest extends TestCase
{
    /**
     * @covers CoveredClass::publicMethod
     * @coversNothing
     */
    public function testSomething()
    {
        $o = new CoveredClass;
        $o->publicMethod();
    }
}
