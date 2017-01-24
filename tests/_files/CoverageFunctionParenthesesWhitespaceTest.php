<?php
use PHPUnit\Framework\TestCase;

class CoverageFunctionParenthesesWhitespaceTest extends TestCase
{
    /**
     * @covers ::globalFunction ( )
     */
    public function testSomething()
    {
        globalFunction();
    }
}
