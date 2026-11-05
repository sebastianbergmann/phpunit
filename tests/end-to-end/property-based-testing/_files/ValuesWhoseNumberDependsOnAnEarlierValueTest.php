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

use function array_search;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\TestCase;

final class ValuesWhoseNumberDependsOnAnEarlierValueTest extends TestCase
{
    /**
     * @param int<1, 10> $size
     */
    #[Property]
    public function testElementIsFoundAtItsIndex(int $size): void
    {
        $list  = $this->draw(Generators::lists(Generators::integers(), $size, $size), 'list');
        $index = $this->draw(Generators::integers(0, $size - 1), 'index');

        $this->assertSame($index, array_search($list[$index], $list, true));
    }

    #[Property]
    public function testSumOfSummandsIsSmall(): void
    {
        $count = $this->draw(Generators::integers(0, 5), 'count');
        $sum   = 0;

        for ($i = 0; $i < $count; $i++) {
            $sum += $this->draw(Generators::integers(0, 10), 'summand');
        }

        $this->assertLessThan(15, $sum);
    }
}
