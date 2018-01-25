<?php
use PHPUnit\Framework\TestCase;

class TemplateMethodsTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        print __METHOD__ . "\n";
    }

    protected function setUp(): void
    {
        print __METHOD__ . "\n";
    }

    protected function assertPreConditions()
    {
        print __METHOD__ . "\n";
    }

    public function testOne()
    {
        print __METHOD__ . "\n";
        $this->assertTrue(true);
    }

    public function testTwo()
    {
        print __METHOD__ . "\n";
        $this->assertTrue(false);
    }

    protected function assertPostConditions()
    {
        print __METHOD__ . "\n";
    }

    protected function tearDown(): void
    {
        print __METHOD__ . "\n";
    }

    public static function tearDownAfterClass(): void
    {
        print __METHOD__ . "\n";
    }

    protected function onNotSuccessfulTest(Throwable $t)
    {
        print __METHOD__ . "\n";
        throw $t;
    }
}
