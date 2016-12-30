<?php
use PHPUnit\Framework\TestCase;

class Issue2366
{
    public function foo()
    {
    }
}

class Issue2366Test extends TestCase
{
    /**
     * @dataProvider provider
     */
    public function testOne($o)
    {
        $this->assertEquals(1, $o->foo());
    }

    public function provider()
    {
        $o = $this->createMock(Issue2366::class);

        $o->method('foo')->willReturn(1);

        return [
            [$o],
            [$o]
        ];
    }
}
