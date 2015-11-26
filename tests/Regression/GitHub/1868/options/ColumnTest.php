<?php
class ColumnTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideColumnCount
     */
    public function testShouldAlwaysPass()
    {
        $this->assertTrue(true);
    }

    public function provideColumnCount()
    {
        $data = [];
        for ($i = 0; $i < 20; $i++) {
            $data[] = [];
        }

        return $data;
    }
}
