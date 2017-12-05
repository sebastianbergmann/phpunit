<?php

namespace My\Space;

class TestSkippedTest extends \PHPUnit\Framework\TestCase
{
    public function notSkippedTest()
    {
    }

    /**
     * @testSkipped
     */
    public function skippedTestWithoutComment()
    {
    }

    /**
     * @testSkipped some smart comment
     */
    public function skippedTestWithComment()
    {
    }
}
