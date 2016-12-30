<?php
use PHPUnit\Framework\TestCase;

class Issue1348Test extends TestCase
{
    public function testSTDOUT()
    {
        fwrite(STDOUT, "\nSTDOUT does not break test result\n");
        $this->assertTrue(true);
    }

    public function testSTDERR()
    {
        fwrite(STDERR, 'STDERR works as usual.');
    }
}
