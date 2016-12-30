<?php
use PHPUnit\Framework\TestCase;

class IncompleteTest extends TestCase
{
    public function testIncomplete()
    {
        $this->markTestIncomplete('Test incomplete');
    }
}
