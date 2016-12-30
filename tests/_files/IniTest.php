<?php
use PHPUnit\Framework\TestCase;

class IniTest extends TestCase
{
    public function testIni()
    {
        $this->assertEquals('application/x-test', ini_get('default_mimetype'));
    }
}
