<?php
require_once 'Success.php';

class TearDownFailure extends Success
{
    protected function tearDown()
    {
        throw new Exception;
    }
}
?>
