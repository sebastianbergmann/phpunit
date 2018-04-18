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

    public function provider()
    {
        return [
            'one' => [1],
            'two' => [2]
        ];
    }
}
