<?php
use PHPUnit\Framework\TestCase;

class ChangeCurrentWorkingDirectoryTest extends TestCase
{
    public function testSomethingThatChangesTheCwd()
    {
        chdir('../');
        $this->assertTrue(true);
    }
}
