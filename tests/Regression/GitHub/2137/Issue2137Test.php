<?php
class Issue2137Test extends PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideBrandService
     *
     * @param $provided
     * @param $expected
     *
     * @throws Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testBrandService($provided, $expected)
    {
        $this->assertSame($provided, $expected);
    }


    public function provideBrandService()
    {
        return [
            //[true, true]
            new stdClass() // not valid
        ];
    }


    /**
     * @dataProvider provideBrandService
     *
     * @param $provided
     * @param $expected
     *
     * @throws Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testSomethingElseInvalid($provided, $expected)
    {
        $this->assertSame($provided, $expected);
    }
}
