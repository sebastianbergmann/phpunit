<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased\RegularExpression;

use function chr;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(CharacterSet::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class CharacterSetTest extends TestCase
{
    public function testOrdersTheAsciiMembersLikeCodePointsAndTheOtherMembersInAscendingOrder(): void
    {
        $set = CharacterSet::fromRanges([[0xE9, 0xE9], [0x30, 0x31], [0x61, 0x62]], true);

        $this->assertSame(5, $set->size());
        $this->assertSame(4, $set->numberOfAsciiMembers());
        $this->assertSame('a', $set->member(0));
        $this->assertSame('b', $set->member(1));
        $this->assertSame('0', $set->member(2));
        $this->assertSame('1', $set->member(3));
        $this->assertSame('é', $set->member(4));
    }

    public function testMergesRangesThatOverlapOrTouch(): void
    {
        $set = CharacterSet::fromRanges([[0x61, 0x63], [0x62, 0x65], [0x66, 0x66]], false);

        $this->assertSame(6, $set->size());
        $this->assertTrue($set->contains(0x66));
        $this->assertFalse($set->contains(0x67));
    }

    public function testCanBeCreatedFromCharacters(): void
    {
        $set = CharacterSet::fromCharacters([0x7A, 0x61], false);

        $this->assertSame(2, $set->size());
        $this->assertSame('a', $set->member(0));
        $this->assertSame('z', $set->member(1));
    }

    public function testUnionContainsTheCharactersOfBothSets(): void
    {
        $set = CharacterSet::fromCharacters([0x61], false)->union(CharacterSet::fromCharacters([0x62], false));

        $this->assertSame(2, $set->size());
        $this->assertTrue($set->contains(0x61));
        $this->assertTrue($set->contains(0x62));
    }

    public function testComplementContainsTheCharactersThatAreNotInTheSet(): void
    {
        $set = CharacterSet::fromRanges([[0x00, 0x60], [0x7B, 0x7F]], false)->complementWithin(CharacterSet::ascii(false));

        $this->assertSame(26, $set->size());
        $this->assertSame('a', $set->member(0));
        $this->assertSame('z', $set->member(25));
    }

    public function testComplementWithinASetOfSeveralRangesContainsTheCharactersOfEachRangeThatAreNotInTheSet(): void
    {
        $set = CharacterSet::fromCharacters([0x35, 0x62], false)->complementWithin(CharacterSet::fromRanges([[0x30, 0x39], [0x61, 0x63]], false));

        $this->assertSame(11, $set->size());
        $this->assertFalse($set->contains(0x35));
        $this->assertFalse($set->contains(0x62));
        $this->assertSame('a', $set->member(0));
        $this->assertSame('c', $set->member(1));
        $this->assertSame('0', $set->member(2));
        $this->assertSame('9', $set->member(10));
    }

    public function testComplementOfTheEmptySetIsTheSetItIsTakenWithin(): void
    {
        $this->assertSame(256, CharacterSet::fromRanges([], false)->complementWithin(CharacterSet::all(false))->size());
    }

    public function testAllUnicodeScalarValuesAreAllCodePointsExceptTheSurrogates(): void
    {
        $set = CharacterSet::all(true);

        $this->assertSame(0x110000 - 0x800, $set->size());
        $this->assertFalse($set->contains(0xD800));
        $this->assertFalse($set->contains(0xDFFF));
        $this->assertTrue($set->contains(0xE000));
        $this->assertSame("\u{E000}", $set->member(0xD800));
    }

    public function testAllBytes(): void
    {
        $set = CharacterSet::all(false);

        $this->assertSame(256, $set->size());
        $this->assertSame(chr(0xFF), $set->member(255));
    }

    public function testIsEmptyWhenItHasNoCharacters(): void
    {
        $this->assertTrue(CharacterSet::fromRanges([], true)->isEmpty());
        $this->assertFalse(CharacterSet::ascii(true)->isEmpty());
    }
}
