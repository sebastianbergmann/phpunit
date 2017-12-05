<?php

use PHPUnit\Framework\TestCase;

class Issue2867Test extends TestCase
{
    /**
     * @dataProvider arrayProvider
     */
    public function testArrayProvider()
    {
        // shows key as datasource
        $this->assertTrue(true);
    }

    /**
     * @dataProvider iteratorProvider
     */
    public function testIteratorProvider()
    {
        // shows numeric datasource
        $this->assertTrue(true);
    }

    /**
     * @dataProvider multipleIteratorProvider
     */
    public function testMultipleIteratorProviders()
    {
        // shows mixed datasource
        $this->assertTrue(true);
    }

    public static function arrayProvider()
    {
        return array(
            'xyx' => array(11,111),
        );
    }

    public static function iteratorProvider()
    {
        yield 'bla' => array(1, 11);
    }

    public static function multipleIteratorProvider()
    {
        yield from self::iteratorProvider();
        yield array(22, 2);
        yield from self::arrayProvider();
        yield from array(
            array(23, 31),
            array(24, 44),
        );
    }
}
