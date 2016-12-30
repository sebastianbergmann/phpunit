<?php
use PHPUnit\ExampleExtension\TestCaseTrait;
use PHPUnit\Framework\TestCase;

class OneTest extends TestCase
{
    use TestCaseTrait;

    public function testOne()
    {
        $this->assertExampleExtensionInitialized();
    }
}
