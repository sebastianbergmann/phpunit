<?php

namespace PHPUnit\Framework;

class AssertNotEqualsTest extends TestCase
{
    public function testConfusingMessagesForLogicalNot()
    {
        $a = 'test contains something';
        $b = 'test contains something';

        $this->assertNotEquals($b, $a);
    }
}
