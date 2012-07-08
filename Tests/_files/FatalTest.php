<?php

class FatalTest extends PHPUnit_Framework_TestCase
{
    public function testFatalError()
    {
        non_existing_function();
    }

}
