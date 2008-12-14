<?php
require_once 'TornDown.php';

class TornDown5 extends TornDown
{
    protected function setUp()
    {
        $this->fail();
    }
}
?>
