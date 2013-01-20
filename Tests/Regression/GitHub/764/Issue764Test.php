<?php
class Issue764Test extends PHPUnit_Framework_TestCase
{
    public function testNotIsEqual()
    {
        $this->assertNotContains('is required', 'username is required');
    }
}
