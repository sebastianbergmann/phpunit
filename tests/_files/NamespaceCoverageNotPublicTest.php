<?php
use PHPUnit\Framework\TestCase;

class NamespaceCoverageNotPublicTest extends TestCase
{
    /**
     * @covers Foo\CoveredClass::<!public>
     */
    public function testSomething()
    {
        $o = new Foo\CoveredClass;
        $o->publicMethod();
    }
}
