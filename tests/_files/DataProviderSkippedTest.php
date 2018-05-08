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

class DataProviderSkippedTest extends TestCase
{
    public static function providerMethod()
    {
        return [
          [0, 0, 0],
          [0, 1, 1],
        ];
    }

    /**
     * @dataProvider skippedTestProviderMethod
     *
     * @param mixed $a
     * @param mixed $b
     * @param mixed $c
     */
    public function testSkipped($a, $b, $c): void
    {
        $this->assertTrue(true);
    }

    /**
     * @dataProvider providerMethod
     *
     * @param mixed $a
     * @param mixed $b
     * @param mixed $c
     */
    public function testAdd($a, $b, $c): void
    {
        $this->assertEquals($c, $a + $b);
    }

    public function skippedTestProviderMethod()
    {
        $this->markTestSkipped('skipped');

        return [
          [0, 0, 0],
          [0, 1, 1],
        ];
    }
}
