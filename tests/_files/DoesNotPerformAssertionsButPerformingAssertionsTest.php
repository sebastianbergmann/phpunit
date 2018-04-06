<?php
use PHPUnit\Framework\TestCase;

class DoesNotPerformAssertionsButPerformingAssertionsTest extends TestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function testFalseAndTrueAreStillFine()
    {
        $this->assertFalse(false);
        $this->assertTrue(true);
    }
}
