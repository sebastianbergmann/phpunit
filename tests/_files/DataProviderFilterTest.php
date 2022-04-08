<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use PHPUnit\Framework\TestCase;

class DataProviderFilterTest extends TestCase
{
    public static function truthProvider()
    {
        return [
            [true],
            [true],
            [true],
            [true],
        ];
    }

    public static function falseProvider()
    {
        return [
            'false test'        => [false],
            'false test 2'      => [false],
            'other false test'  => [false],
            'other false test2' => [false],
        ];
    }

    /**
     * @dataProvider truthProvider
     */
    public function testTrue($truth): void
    {
        $this->assertTrue($truth);
    }

    /**
     * @dataProvider falseProvider
     */
    public function testFalse($false): void
    {
        $this->assertFalse($false);
    }
}
