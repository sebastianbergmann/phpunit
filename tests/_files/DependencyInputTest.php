<?php

use PHPUnit\Framework\TestCase;

class DependencyInputTest extends TestCase
{
    public function testDependencyInputAsParameter(string $dependencyInput): void
    {
        $this->assertEquals("value from TestCaseTest", $dependencyInput);
    }
}
