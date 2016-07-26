<?php
class TriggerWarningTestCase extends PHPUnit_Framework_TestCase
{
    public function testRealExceptionIsCaught()
    {
        $this->setExpectedException('Exception');
        throw new Exception('', 1);
    }

    /**
     * @errorHandler enabled
     */
    public function testWarningAsExceptionIsNotCaught()
    {
        $this->setExpectedException('Exception');
        trigger_error("Catch as exception", E_USER_NOTICE);
    }
}
