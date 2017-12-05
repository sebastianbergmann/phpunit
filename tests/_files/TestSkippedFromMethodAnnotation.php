<?php

namespace PHPUnit;

use PHPUnit\Framework\TestCase;

class TestSkippedFromMethodAnnotation extends TestCase
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
