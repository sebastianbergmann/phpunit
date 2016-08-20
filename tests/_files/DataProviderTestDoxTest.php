<?php
class DataProviderTestDoxTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provider
     * @testdox Does something with
     */
    public function testOne()
    {
        $this->assertTrue(true);
    }

    /**
     * @dataProvider provider
     */
    public function testDoesSomethingElseWith()
    {
        $this->assertTrue(true);
    }

    public function provider()
    {
        return [
            'one' => [1],
            'two' => [2]
        ];
    }
}
