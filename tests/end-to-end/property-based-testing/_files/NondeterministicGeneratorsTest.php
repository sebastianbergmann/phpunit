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

use PHPUnit\Framework\Attributes\ForAll;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\TestCase;

final class NondeterministicGeneratorsTest extends TestCase
{
    private static int $maximum = 0;
    private static int $size    = 0;

    /**
     * @return ValueGenerator<int>
     */
    public static function integersUpToAMaximumThatGrows(): ValueGenerator
    {
        return Generators::booleans()->flatMap(
            static function (bool $flag): ValueGenerator
            {
                self::$maximum++;

                return Generators::integers(0, self::$maximum);
            },
        );
    }

    /**
     * @return ValueGenerator<list<int>>
     */
    public static function listsWhoseSizeGrows(): ValueGenerator
    {
        return Generators::integers(0, 9)->flatMap(
            static function (int $value): ValueGenerator
            {
                self::$size++;

                return Generators::lists(Generators::integers(0, 9), self::$size, self::$size);
            },
        );
    }

    #[Property(examples: 20)]
    public function testDrawsFromARangeThatGrows(#[ForAll('integersUpToAMaximumThatGrows')] int $value): void
    {
        $this->assertGreaterThanOrEqual(0, $value);
    }

    /**
     * @param list<int> $values
     */
    #[Property]
    public function testIsFalsifiedByListsWhoseSizeGrows(#[ForAll('listsWhoseSizeGrows')] array $values): void
    {
        $this->assertCount(0, $values);
    }
}
