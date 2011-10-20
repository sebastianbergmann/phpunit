<?php
class TwoSubsuitesTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider nullProvider
     */
    public function testNoop()
    {
    }

    /**
     * @dataProvider nullProvider
     */
    public function testNoop2()
    {
    }

    public static function nullProvider()
    {
        return array(
          array(),
          array(),
          array(),
        );
    }
}

