<?php
use PHPUnit\Framework\TestCase;

/**
 * @requires extension I_DO_NOT_EXIST
 */
class Issue1374Test extends TestCase
{
    protected function setUp()
    {
        print __FUNCTION__;
    }

    public function testSomething()
    {
        $this->fail('This should not be reached');
    }

    protected function tearDown()
    {
        print __FUNCTION__;
    }
}
