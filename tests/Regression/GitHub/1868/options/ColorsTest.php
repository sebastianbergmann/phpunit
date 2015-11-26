<?php
class ColorsTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldAlwaysFail()
    {
        $this->fail('always failure');
    }

    public function testShouldAlwaysBeIncomplete()
    {
        $this->markTestIncomplete('always incomplete');
    }

    public function testShouldAlwaysPass()
    {
        $this->assertTrue(true);
    }

    public function testShouldAlwaysSkip()
    {
        $this->markTestSkipped('always skip');
    }
}
