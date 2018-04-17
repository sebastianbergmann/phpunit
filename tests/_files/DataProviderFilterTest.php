<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;

class DataProviderFilterTest extends TestCase
{
    public static function truthProvider()
    {
        return [
           [true],
           [true],
           [true],
           [true]
        ];
    }

    public static function falseProvider()
    {
        return [
          'false test'       => [false],
          'false test 2'     => [false],
          'other false test' => [false],
          'other false test2'=> [false]
        ];
    }

    /**
     * @dataProvider truthProvider
     *
     * @param mixed $truth
     */
    public function testTrue($truth): void
    {
        $this->assertTrue($truth);
    }

    /**
     * @dataProvider falseProvider
     *
     * @param mixed $false
     */
    public function testFalse($false): void
    {
        $this->assertFalse($false);
    }
}
