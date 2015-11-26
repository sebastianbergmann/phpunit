<?php
class IncludePathTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldSetIncludePath()
    {
        $this->assertContains(dirname(__DIR__), ini_get('include_path'));
    }
}
