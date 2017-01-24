<?php
use PHPUnit\Framework\TestCase;

class NamespaceCoverageMethodTest extends TestCase
{
    /**
     * @covers Foo\CoveredClass::publicMethod
     */
    public function testSomething()
    {
        $o = new Foo\CoveredClass;
        $o->publicMethod();
    }
}
