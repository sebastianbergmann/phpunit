<?php
namespace Bar;

class NamespaceCoverageDifferentNamespaceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Foo\CoveredClass
     */
    public function testSomething()
    {
        $o = new CoveredClass;
        $o->publicMethod();
    }
}
