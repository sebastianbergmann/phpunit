<?php
use PHPUnit\Framework\TestCase;

class Issue503Test extends TestCase
{
    public function testCompareDifferentLineEndings()
    {
        $this->assertSame(
            "foo\n",
            "foo\r\n"
        );
    }
}
