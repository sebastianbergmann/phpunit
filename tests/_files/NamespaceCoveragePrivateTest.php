<?php
use PHPUnit\Framework\TestCase;

class NamespaceCoveragePrivateTest extends TestCase
{
    /**
     * @covers Foo\CoveredClass::<private>
     */
    public function testSomething()
    {
        $o = new Foo\CoveredClass;
        $o->publicMethod();
    }
}
