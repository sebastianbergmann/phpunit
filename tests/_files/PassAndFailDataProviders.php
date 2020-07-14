<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;

class PassAndFailDataProviders extends TestCase
{
    public static function numberProvider(): array
    {
        return [
            'one'   => [1],
            'two'   => [2],
            'three' => [3],
        ];
    }

    public function testWithoutDataProvider(): void
    {
        $this->assertTrue(true);
    }

    /**
     * @dataProvider numberProvider
     */
    public function testIsPositive(int $number): void
    {
        $this->assertTrue($number > 0);
    }

    /**
     * @dataProvider numberProvider
     */
    public function testIsEven(int $number): void
    {
        $this->assertTrue($number % 2 === 0);
    }

    /**
     * @testdox Annotation parameter named $_dataName with $number is odd
     * @dataProvider numberProvider
     */
    public function testIsOdd(int $number): void
    {
        $this->assertTrue($number % 2 === 1);
    }

    /**
     * @dataProvider numberProvider
     */
    public function testIsNegative(int $number): void
    {
        $this->assertTrue($number < 0);
    }
}
