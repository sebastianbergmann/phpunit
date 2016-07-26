<?php
class Issue1553Test extends PHPUnit_Framework_TestCase
{
    public function testTestsThatDoNotCloseTheirOutputBuffersHaveOutputSwallowed()
    {
        ob_start();
        echo 'here';
    }

    public function testTestsWithoutOutputBufferingDoNotHaveTheirOutputSwallowed()
    {
        echo 'there';
    }
}
