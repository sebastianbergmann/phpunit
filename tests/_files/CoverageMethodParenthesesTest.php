<?php
use PHPUnit\Framework\TestCase;

class CoverageMethodParenthesesTest extends TestCase
{
    /**
     * @covers CoveredClass::publicMethod()
     */
    public function testSomething()
    {
        $o = new CoveredClass;
        $o->publicMethod();
    }
}
