<?php
use PHPUnit\Framework\TestCase;

class CoverageProtectedTest extends TestCase
{
    /**
     * @covers CoveredClass::<protected>
     */
    public function testSomething()
    {
        $o = new CoveredClass;
        $o->publicMethod();
    }
}
