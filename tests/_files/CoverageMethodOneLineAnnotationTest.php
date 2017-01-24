<?php

use PHPUnit\Framework\TestCase;

class CoverageMethodOneLineAnnotationTest extends TestCase
{
    /** @covers CoveredClass::publicMethod */
    public function testSomething()
    {
        $o = new CoveredClass;
        $o->publicMethod();
    }
}
