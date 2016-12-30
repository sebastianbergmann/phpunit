<?php
class Issue2380Test extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider generatorData
     */
    public function testGeneratorProvider($data)
    {
        $this->assertNotEmpty($data);
    }

    /**
     * @return Generator
     */
    public function generatorData()
    {
        yield ['testing'];
    }
}
