<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;

/**
 * @since      Class available since Release 3.3.0
 * @covers     PHPUnit_Util_XML
 */
class Util_XMLTest extends TestCase
{
    /**
     * @dataProvider charProvider
     */
    public function testPrepareString($char)
    {
        $e = null;

        $escapedString = PHPUnit_Util_XML::prepareString($char);
        $xml           = "<?xml version='1.0' encoding='UTF-8' ?><tag>$escapedString</tag>";
        $dom           = new DomDocument('1.0', 'UTF-8');

        try {
            $dom->loadXML($xml);
        } catch (Exception $e) {
        }

        $this->assertNull($e, sprintf(
            'PHPUnit_Util_XML::prepareString("\x%02x") should not crash DomDocument',
            ord($char)
        ));
    }

    public function charProvider()
    {
        $data = [];

        for ($i = 0; $i < 256; $i++) {
            $data[] = [chr($i)];
        }

        return $data;
    }

    public function testLoadEmptyString()
    {
        $this->expectException(PHPUnit\Framework\Exception::class);
        $this->expectExceptionMessage('Could not load XML from empty string');

        PHPUnit_Util_XML::load('');
    }

    public function testLoadArray()
    {
        $this->expectException(PHPUnit\Framework\Exception::class);
        $this->expectExceptionMessage('Could not load XML from array');

        PHPUnit_Util_XML::load([1, 2, 3]);
    }

    public function testLoadBoolean()
    {
        $this->expectException(PHPUnit\Framework\Exception::class);
        $this->expectExceptionMessage('Could not load XML from boolean');

        PHPUnit_Util_XML::load(false);
    }
}
