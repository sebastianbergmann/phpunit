<?php
require_once 'Success.php';

class SetupFailure extends Success
{
    protected function setUp()
    {
        throw new Exception;
    }
}
?>
