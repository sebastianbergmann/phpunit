<?php

require_once dirname(__FILE__) . '/NewException.php';

class Issue74Test extends PHPUnit_Framework_TestCase
{
    public function testCreateAndThrowNewExceptionInProcessIsolation()
    {
        throw new NewException('Testing GH-74');
    }
}