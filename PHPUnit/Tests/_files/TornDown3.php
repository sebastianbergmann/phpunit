<?php
require_once 'TornDown.php';

class TornDown3 extends TornDown
{
    protected function setUp()
    {
        throw new Exception;
    }
}
?>
