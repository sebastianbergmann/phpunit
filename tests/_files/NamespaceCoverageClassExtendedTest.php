<?php
use PHPUnit\Framework\TestCase;

class NamespaceCoverageClassExtendedTest extends TestCase
{
    /**
     * @covers Foo\CoveredClass<extended>
     */
    public function testSomething()
    {
        $o = new Foo\CoveredClass;
        $o->publicMethod();
    }
}
