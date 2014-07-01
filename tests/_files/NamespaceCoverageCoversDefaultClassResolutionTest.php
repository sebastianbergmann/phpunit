<?php
namespace Bar;

use Foo\CoveredClass as Baz;

/**
 * @coversDefaultClass Baz
 */
class NamespaceCoverageCoversDefaultClassResolutionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::publicMethod
     */
    public function testSomething()
    {
        $o = new Foo\CoveredClass;
        $o->publicMethod();
    }
}

