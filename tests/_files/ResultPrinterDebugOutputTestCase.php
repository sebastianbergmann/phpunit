<?php
class ResultPrinterDebugOutputTestCase extends PHPUnit_Framework_TestCase
{
    public function testPrintStringFoo()
    {
        print 'foo';
    }
    
    public function testEchoStringBar()
    {
        echo 'bar';
    }
}
