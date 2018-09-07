<?php

/**
 * @runClassInSeparateProcess
 */
class Issue2830Test extends PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider simpleDataProvider
     */
    public function testMethodUsesDataProvider()
    {
        $this->assertTrue(true);
    }

    public function simpleDataProvider()
    {
        return [
            ['foo'],
        ];
    }
}