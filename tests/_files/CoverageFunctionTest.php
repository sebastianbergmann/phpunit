<?php
use PHPUnit\Framework\TestCase;

class CoverageFunctionTest extends TestCase
{
    /**
     * @covers ::globalFunction
     */
    public function testSomething()
    {
        globalFunction();
    }
}
