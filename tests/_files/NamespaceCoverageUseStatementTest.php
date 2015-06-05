<?php
namespace Bar;

use Foo\CoveredClass as Baz;

class NamespaceCoverageUseStatementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Baz
     */
    public function testSomething()
    {
        $o = new CoveredClass;
        $o->publicMethod();
    }
}
