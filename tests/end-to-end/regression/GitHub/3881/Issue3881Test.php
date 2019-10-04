<?php
use PHPUnit\Framework\TestCase;

abstract class AbstractIssue3881Test extends TestCase
{
    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}

final class Issue3881Test extends AbstractIssue3881Test
{
}
