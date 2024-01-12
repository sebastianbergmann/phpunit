<?php

namespace PHPUnit\Framework;

use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\Constraint\LogicalNot;

class AssertNotEqualsTest extends TestCase
{
    public function testConfusingMessagesForLogicalNot()
    {
        $expectedMessage = "Failed asserting that 'test contains something' is not equal to 'test contains something'.";
        $a = 'test contains something';
        $b = 'test contains something';
        $constraint = new LogicalNot(new IsEqual($a));
        try {
            Assert::assertThat($b, $constraint);
        } catch (\Exception $e) {
            $actualMessage = $e->getMessage();
            $this->assertSame($expectedMessage, $actualMessage);
        }
    }
}
