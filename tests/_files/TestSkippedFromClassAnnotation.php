<?php

namespace PHPUnit;

use PHPUnit\Framework\TestCase;

/**
 * @testSkipped
 */
class TestSkippedFromClassAnnotation extends TestCase
{
    public function testSkipped()
    {
        $this->fail();
    }
}
