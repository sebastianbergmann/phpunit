<?php
use PHPUnit\Framework\TestCase;

class Issue1216Test extends TestCase
{
    public function testConfigAvailableInBootstrap()
    {
        $this->assertTrue($_ENV['configAvailableInBootstrap']);
    }
}
