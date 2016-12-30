<?php
use PHPUnit\Framework\TestCase;

class IgnoreCodeCoverageClassTest extends TestCase
{
    public function testReturnTrue()
    {
        $sut = new IgnoreCodeCoverageClass();
        $this->assertTrue($sut->returnTrue());
    }

    public function testReturnFalse()
    {
        $sut = new IgnoreCodeCoverageClass();
        $this->assertFalse($sut->returnFalse());
    }
}
