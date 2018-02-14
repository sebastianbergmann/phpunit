<?php
include __DIR__ . '/UndefinedIndex.php';

class Issue3010Test extends PHPUnit\Framework\TestCase
{
    public function testOne()
    {
        $u = new UndefinedIndex();
        $r = $u->hello([]);

        $this->assertSame(0, $r);
    }
}
