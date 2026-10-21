<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased\Generator;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\PropertyBased\InvalidGeneratorArgumentException;
use PHPUnit\Framework\TestCase;

#[CoversClass(CodePointRanges::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class CodePointRangesTest extends TestCase
{
    /**
     * @return array<string, array{array<mixed>}>
     */
    public static function invalidRangesProvider(): array
    {
        return [
            'not a list'              => [[['first' => 0x41, 'last' => 0x5A]]],
            'not two code points'     => [[[0x41]]],
            'not an array'            => [[0x41]],
            'not integers'            => [[['A', 'Z']]],
            'negative code point'     => [[[-1, 0x41]]],
            'first greater than last' => [[[0x5A, 0x41]]],
            'code point out of range' => [[[0x41, 0x110000]]],
        ];
    }

    public function testSortsTheRangesAndMergesRangesThatOverlapOrAdjoinEachOther(): void
    {
        $ranges = CodePointRanges::from([[0x61, 0x7A], [0x30, 0x39], [0x35, 0x40], [0x41, 0x5A], [0x100, 0x1FF]]);

        $this->assertSame('30-5A,61-7A,100-1FF', $ranges->asString());
        $this->assertSame(43 + 26 + 256, $ranges->numberOfCodePoints());
    }

    public function testLeavesOutSurrogates(): void
    {
        $this->assertSame('D700-D7FF,E000-E000', CodePointRanges::from([[0xD700, 0xE000]])->asString());
        $this->assertSame('41-41', CodePointRanges::from([[0xD800, 0xDFFF], [0x41, 0x41]])->asString());
    }

    public function testNumbersTheCodePointsFromTheLowestOne(): void
    {
        $ranges = CodePointRanges::from([[0x41, 0x5A], [0x30, 0x39]]);

        $this->assertSame(0x30, $ranges->codePoint(0));
        $this->assertSame(0x39, $ranges->codePoint(9));
        $this->assertSame(0x41, $ranges->codePoint(10));
        $this->assertSame(0x5A, $ranges->codePoint(35));
    }

    public function testAnIndexBeyondTheHighestCodePointStandsForTheHighestCodePoint(): void
    {
        $this->assertSame(0x5A, CodePointRanges::from([[0x41, 0x5A], [0x30, 0x39]])->codePoint(36));
    }

    public function testHasTheRangesOfTheIndexesOfTheCodePointsOfEachRange(): void
    {
        $this->assertSame(
            [
                ['minimum' => 0, 'maximum' => 9],
                ['minimum' => 10, 'maximum' => 35],
            ],
            CodePointRanges::from([[0x41, 0x5A], [0x30, 0x39]])->indexRanges(),
        );
    }

    public function testAtLeastOneRangeMustBeSpecified(): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessage('At least one code point range must be specified');

        CodePointRanges::from([]);
    }

    /**
     * @param array<mixed> $ranges
     */
    #[DataProvider('invalidRangesProvider')]
    public function testARangeMustBeAListOfItsFirstAndItsLastCodePoint(array $ranges): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessage('A code point range must be a list of its first and its last code point, from 0 to 1114111');

        CodePointRanges::from($ranges);
    }

    public function testTheRangesMustContainCodePointsThatAreNotSurrogates(): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessage('The code point ranges contain only surrogates, which are not characters');

        CodePointRanges::from([[0xD800, 0xDBFF], [0xDC00, 0xDFFF]]);
    }
}
