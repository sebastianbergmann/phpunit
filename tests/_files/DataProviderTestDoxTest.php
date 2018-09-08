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

class DataProviderTestDoxTest extends TestCase
{
    /**
     * @dataProvider provider
     * @testdox Does something with
     */
    public function testOne(): void
    {
        $this->assertTrue(true);
    }

    /**
     * @dataProvider provider
     */
    public function testDoesSomethingElseWith(): void
    {
        $this->assertTrue(true);
    }

    /**
     * @dataProvider placeHolderprovider
     * @testdox ... $value ...
     */
    public function testWithPlaceholders($value): void
    {
        $this->assertTrue(true);
    }

    public function provider()
    {
        return [
            'one' => [1],
            'two' => [2],
        ];
    }

    public function placeHolderprovider(): array
    {
        return [
            'boolean'          => [true],
            'integer'          => [1],
            'float'            => [1.0],
            'string'           => ['string'],
            'array'            => [[1, 2, 3]],
            'object'           => [new \stdClass],
            'stringableObject' => [new class {
                public function __toString()
                {
                    return 'string';
                }
            }],
            'resource'         => [\fopen(__FILE__, 'rb')],
            'null'             => [null],
        ];
    }
}
