<?php
require_once 'TornDown.php';

class TornDown4 extends TornDown
{
    protected function tearDown()
    {
        throw new Exception('tearDown');
    }
}
?>
