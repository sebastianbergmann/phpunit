<?php
final class StopOnTest extends PHPUnit_Framework_TestCase
{
    public function testShouldFail()
    {
        $this->fail('Always fail');
    }

    public function testShouldBeRisky()
    {
        // Always risky, no assertion
    }

    public function testShouldBeIncomplete()
    {
        $this->markTestIncomplete('Always incomplete');
    }

    public function testShouldBeSkipped()
    {
        $this->markTestSkipped('Always skip');
    }

    public function testShouldBeError()
    {
        trigger_error('Should error', E_USER_NOTICE);
    }
}
