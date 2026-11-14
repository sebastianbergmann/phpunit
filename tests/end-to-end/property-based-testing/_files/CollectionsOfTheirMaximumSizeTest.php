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

use function count;
use function max;
use function mb_strlen;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\ForAll;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\TestCase;

final class CollectionsOfTheirMaximumSizeTest extends TestCase
{
    /**
     * @var list<int>
     */
    private static array $lengths = [];

    /**
     * @var list<int>
     */
    private static array $sizes = [];

    /**
     * @return ValueGenerator<string>
     */
    public static function stringsOfAtMostFiftyCharacters(): ValueGenerator
    {
        return Generators::strings(maximumLength: 50);
    }

    /**
     * @return ValueGenerator<list<int>>
     */
    public static function listsOfAtMostOneHundredIntegers(): ValueGenerator
    {
        return Generators::lists(Generators::integers(), 0, 100);
    }

    #[Property(examples: 1000)]
    public function testString(#[ForAll('stringsOfAtMostFiftyCharacters')] string $string): void
    {
        self::$lengths[] = mb_strlen($string);

        $this->assertLessThanOrEqual(50, mb_strlen($string));
    }

    /**
     * @param list<int> $list
     */
    #[Property(examples: 1000)]
    public function testList(#[ForAll('listsOfAtMostOneHundredIntegers')] array $list): void
    {
        self::$sizes[] = count($list);

        $this->assertLessThanOrEqual(100, count($list));
    }

    #[Depends('testString')]
    public function testSomeStringsHaveTheMaximumLength(): void
    {
        $this->assertCount(1000, self::$lengths);
        $this->assertSame(50, max(self::$lengths));
    }

    #[Depends('testList')]
    public function testSomeListsHaveTheMaximumSize(): void
    {
        $this->assertCount(1000, self::$sizes);
        $this->assertSame(100, max(self::$sizes));
    }
}
