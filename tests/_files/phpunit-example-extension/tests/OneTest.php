<?php
use PHPUnit\ExampleExtension\TestCaseTrait;

class OneTest extends PHPUnit_Framework_TestCase
{
    use TestCaseTrait;

    public function testOne()
    {
        $this->assertExampleExtensionInitialized();
    }
}
