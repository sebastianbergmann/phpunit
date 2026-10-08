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

use function ord;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(CodePoints::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class CodePointsTest extends TestCase
{
    /**
     * @return array<string, array{int<0, 1112063>, int<0, 1114111>}>
     */
    public static function indexProvider(): array
    {
        return [
            'lowercase letter a'     => [0, ord('a')],
            'lowercase letter z'     => [25, ord('z')],
            'uppercase letter A'     => [26, ord('A')],
            'digit 0'                => [52, ord('0')],
            'space'                  => [62, ord(' ')],
            'tilde'                  => [94, ord('~')],
            'NUL'                    => [95, 0x00],
            'DEL'                    => [127, 0x7F],
            'first non-ASCII'        => [128, 0x80],
            'last before surrogates' => [0xD7FF, 0xD7FF],
            'first after surrogates' => [0xD800, 0xE000],
            'largest code point'     => [CodePoints::LARGEST_INDEX, 0x10FFFF],
        ];
    }

    /**
     * @param int<0, 1112063> $index
     * @param int<0, 1114111> $codePoint
     */
    #[DataProvider('indexProvider')]
    public function testMapsIndicesToCodePointsAndBack(int $index, int $codePoint): void
    {
        $this->assertSame($codePoint, CodePoints::codePoint($index));
        $this->assertSame($index, CodePoints::index($codePoint));
    }

    public function testMapsEveryAsciiIndexToADifferentCodePoint(): void
    {
        $codePoints = [];

        for ($index = 0; $index < 128; $index++) {
            $codePoints[CodePoints::codePoint($index)] = true;
        }

        $this->assertCount(128, $codePoints);
    }
}
