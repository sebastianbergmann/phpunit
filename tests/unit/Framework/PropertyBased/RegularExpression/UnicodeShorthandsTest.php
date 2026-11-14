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

use function intdiv;
use function max;
use function mb_check_encoding;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(UnicodeShorthands::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class UnicodeShorthandsTest extends TestCase
{
    private const int NUMBER_OF_CHARACTERS_THAT_ARE_CHECKED = 500;

    /**
     * @return array<string, array{non-empty-string}>
     */
    public static function shorthandProvider(): array
    {
        return [
            '\d'         => ['\d'],
            '\w'         => ['\w'],
            '\s'         => ['\s'],
            '[:alpha:]'  => ['[:alpha:]'],
            '[:digit:]'  => ['[:digit:]'],
            '[:alnum:]'  => ['[:alnum:]'],
            '[:upper:]'  => ['[:upper:]'],
            '[:lower:]'  => ['[:lower:]'],
            '[:space:]'  => ['[:space:]'],
            '[:blank:]'  => ['[:blank:]'],
            '[:cntrl:]'  => ['[:cntrl:]'],
            '[:graph:]'  => ['[:graph:]'],
            '[:print:]'  => ['[:print:]'],
            '[:punct:]'  => ['[:punct:]'],
            '[:xdigit:]' => ['[:xdigit:]'],
            '[:word:]'   => ['[:word:]'],
            '[:ascii:]'  => ['[:ascii:]'],
        ];
    }

    /**
     * @return array<string, array{non-empty-string}>
     */
    public static function shorthandThatMatchesCharactersThatAreNotAsciiCharactersProvider(): array
    {
        return [
            '\d'        => ['\d'],
            '\w'        => ['\w'],
            '\s'        => ['\s'],
            '[:alpha:]' => ['[:alpha:]'],
            '[:upper:]' => ['[:upper:]'],
            '[:punct:]' => ['[:punct:]'],
        ];
    }

    /**
     * @param non-empty-string $shorthand
     */
    #[DataProvider('shorthandProvider')]
    public function testContainsCharactersThatAreMatchedWithTheUModifier(string $shorthand): void
    {
        $matched = UnicodeShorthands::matchedBy($shorthand);

        $this->assertFalse($matched->isEmpty());

        foreach ($this->membersOf($matched) as $character) {
            $this->assertTrue(mb_check_encoding($character, 'UTF-8'));
            $this->assertMatchesRegularExpression('/\A[' . $shorthand . ']\z/u', $character);
        }
    }

    /**
     * @param non-empty-string $shorthand
     */
    #[DataProvider('shorthandProvider')]
    public function testDoesNotContainCharactersThatAreNotMatchedWithTheUModifier(string $shorthand): void
    {
        $notMatched = UnicodeShorthands::matchedBy($shorthand)->complementWithin(CharacterSet::all(true));

        $this->assertFalse($notMatched->isEmpty());

        foreach ($this->membersOf($notMatched) as $character) {
            $this->assertMatchesRegularExpression('/\A[^' . $shorthand . ']\z/u', $character);
        }
    }

    /**
     * @param non-empty-string $shorthand
     */
    #[DataProvider('shorthandThatMatchesCharactersThatAreNotAsciiCharactersProvider')]
    public function testContainsCharactersThatAreNotAsciiCharacters(string $shorthand): void
    {
        $matched = UnicodeShorthands::matchedBy($shorthand);

        $this->assertGreaterThan($matched->numberOfAsciiMembers(), $matched->size());
    }

    public function testContainsTheAsciiCharactersThatAreMatchedWithoutTheUModifier(): void
    {
        $this->assertSame(10, UnicodeShorthands::matchedBy('\d')->numberOfAsciiMembers());
        $this->assertSame(63, UnicodeShorthands::matchedBy('\w')->numberOfAsciiMembers());
        $this->assertSame(6, UnicodeShorthands::matchedBy('\s')->numberOfAsciiMembers());
        $this->assertSame(128, UnicodeShorthands::matchedBy('[:ascii:]')->size());
    }

    public function testTheCharactersAreOnlyDeterminedOnce(): void
    {
        $this->assertSame(UnicodeShorthands::matchedBy('\d'), UnicodeShorthands::matchedBy('\d'));
    }

    /**
     * The ASCII characters of the set, and characters that are evenly spread
     * over its other ones.
     *
     * @return list<non-empty-string>
     */
    private function membersOf(CharacterSet $set): array
    {
        $members = [];

        for ($index = 0; $index < $set->numberOfAsciiMembers(); $index++) {
            $members[] = $set->member($index);
        }

        $others = $set->size() - $set->numberOfAsciiMembers();
        $step   = max(1, intdiv($others, self::NUMBER_OF_CHARACTERS_THAT_ARE_CHECKED));

        for ($index = $set->numberOfAsciiMembers(); $index < $set->size(); $index += $step) {
            $members[] = $set->member($index);
        }

        // the last character
        if ($others > 0) {
            $members[] = $set->member($set->size() - 1);
        }

        return $members;
    }
}
