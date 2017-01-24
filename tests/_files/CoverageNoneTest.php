<?php
use PHPUnit\Framework\TestCase;

class CoverageNoneTest extends TestCase
{
    public function testSomething()
    {
        $o = new CoveredClass;
        $o->publicMethod();
    }
}
