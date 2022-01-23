<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class Issue4399Test extends TestCase
{
    public function testOne(): void
    {
        $this->assertCount(0, []);
    }
}
