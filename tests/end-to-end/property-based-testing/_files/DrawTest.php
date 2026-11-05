<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting;

use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\TestCase;

final class DrawTest extends TestCase
{
    /**
     * @param int<1, 10> $size
     */
    #[Property]
    public function testDrawsValuesThatDependOnTheArgumentAndOnEachOther(int $size): void
    {
        $list  = $this->draw(Generators::lists(Generators::integers(0, 9), $size, $size), 'list');
        $index = $this->draw(Generators::integers(0, $size - 1), 'index');

        $this->assertNotSame(5, $list[$index]);
    }

    #[Property]
    public function testDrawsValuesWithoutParameters(): void
    {
        $a = $this->draw(Generators::integers(0, 100));
        $b = $this->draw(Generators::integers(0, 100));

        $this->assertLessThan(100, $a + $b);
    }

    #[Property]
    public function testDrawsValuesInALoop(): void
    {
        $count = $this->draw(Generators::integers(0, 5), 'count');
        $sum   = 0;

        for ($i = 0; $i < $count; $i++) {
            $sum += $this->draw(Generators::integers(0, 10), 'summand');
        }

        $this->assertLessThanOrEqual(50, $sum);
    }
}
