<?php

use PHPUnit\Framework\TestCase;

class TestSkippedFromAnnotation extends TestCase
{
    /**
     * @testSkipped
     */
    public function testSkipped()
    {
        $this->fail();
    }

    /**
     * @testSkipped some smart comment
     */
    public function testSkippedWithComment()
    {
        $this->fail();
    }
}
