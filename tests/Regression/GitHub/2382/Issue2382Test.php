<?php
class Issue2382Test extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testOne($test)
    {
        $this->assertInstanceOf(\Exception::class, $test);
    }

    public function dataProvider()
    {
        return [
            [
                $this->getMockBuilder(\Exception::class)->getMock()
            ]
        ];
    }
}
