<?php
use PHPUnit\Framework\TestCase;

class Issue1337Test extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testProvider($a)
    {
        $this->assertTrue($a);
    }

    public function dataProvider()
    {
        return [
          'c:\\'=> [true],
          0.9   => [true]
        ];
    }
}
