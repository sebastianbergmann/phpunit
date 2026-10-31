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

use function array_map;
use function mb_check_encoding;
use function ord;
use function preg_match;
use function strlen;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\PropertyBased\ChoiceSource;
use PHPUnit\Framework\PropertyBased\ExampleRejectedException;
use PHPUnit\Framework\PropertyBased\Generator\AbstractGenerator;
use PHPUnit\Framework\PropertyBased\Generator\RegularExpressionGenerator;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\InvalidGeneratorArgumentException;
use PHPUnit\Framework\TestCase;
use Random\Engine\Xoshiro256StarStar;
use Random\Randomizer;
use SebastianBergmann\ChoiceSequence\ChoiceSequence;

#[CoversClass(RegularExpressionGenerator::class)]
#[CoversClass(Parser::class)]
#[CoversClass(AlternationNode::class)]
#[CoversClass(CharacterSetNode::class)]
#[CoversClass(LiteralNode::class)]
#[CoversClass(QuantifiedNode::class)]
#[CoversClass(SequenceNode::class)]
#[CoversClass(Generators::class)]
#[CoversClass(ExampleRejectedException::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class RegularExpressionGeneratorTest extends TestCase
{
    /**
     * @return array<string, array{non-empty-string}>
     */
    public static function regularExpressionProvider(): array
    {
        return [
            'character classes and quantifiers'  => ['/\A[a-z]+-\d{2,4}\z/'],
            'alternation in a repeated group'    => ['/^(foo|bar|baz)+$/'],
            'escape sequences for classes'       => ['/^\w+@\w+\.(com|org)$/'],
            'negated character class'            => ['/[^a-z]/'],
            'dot with the u modifier'            => ['/^.{3}$/u'],
            'dot with the s modifier'            => ['/^.{3}$/s'],
            'negated escape sequences'           => ['/^\W\D\S$/u'],
            'negated escape sequences in bytes'  => ['/^\W\D\S$/'],
            'POSIX classes'                      => ['/^[[:alpha:]][[:^digit:]][[:xdigit:][:punct:]]$/'],
            'POSIX classes with the u modifier'  => ['/^[[:^space:]][[:word:][:blank:][:cntrl:]]$/u'],
            'more POSIX classes'                 => ['/^[[:alnum:][:upper:][:lower:][:graph:][:print:][:ascii:]]$/'],
            'lazy quantifier'                    => ['/^(?:ab|c){2,}?$/'],
            'named groups'                       => ['/^(?<year>\d{4})-(?P<month>\d\d)-(?\'day\'\d\d)$/'],
            'other delimiters'                   => ['#^/path/[^/]+$#'],
            'bracket delimiters'                 => ['{^a{2}$}'],
            'parentheses as delimiters'          => ['(^a(b)$)i'],
            'square brackets as delimiters'      => ['[^a$]'],
            'angle brackets as delimiters'       => ['<^a$>'],
            'whitespace'                         => ['/^\s\S$/'],
            'extended modifier'                  => ["/^a b # comment\n c\\ d$/x"],
            'case-insensitive class'             => ['/^[A-Z]$/i'],
            'case-insensitive negated class'     => ['/^[^a]$/i'],
            'word boundaries'                    => ['/\bword\b/'],
            'quoted characters'                  => ['/^\QA.B*\E$/'],
            'quoted characters at the end'       => ['/^x\Q.*/'],
            'escaped hyphen in a class'          => ['/^[\d\-x]$/'],
            'closing bracket first in a class'   => ['/^[]a]$/'],
            'escaped closing bracket in a class' => ['/^[\]]$/'],
            'hyphen at the end of a class'       => ['/^[a-]$/'],
            'optional character'                 => ['/^colou?r$/'],
            'possessive quantifiers'             => ['/^a++b*+c?+$/'],
            'empty alternative'                  => ['/^(a|)$/'],
            'empty regular expression'           => ['/^$/'],
            'atomic and branch reset groups'     => ['/^(?>a|b)(?|(c)|(d))$/'],
            'comment group'                      => ['/^(?#comment)x$/'],
            'multibyte range'                    => ['/^[é\x{e0}-\x{ef}]$/u'],
            'emoji range'                        => ['/^[\x{1F600}-\x{1F64F}]$/u'],
            'horizontal and vertical whitespace' => ['/^\h\v\H\V$/u'],
            'whitespace in bytes'                => ['/^\h\v\H\V$/'],
            'escaped characters'                 => ['/^\x41\x{42}\x4\0\012\o{103}\cA\t\n\r\f\e\a$/u'],
            '\E without \Q'                      => ['/^a\Eb[c\E]$/'],
            'backspace in a class'               => ['/^[\b]$/'],
            'escape sequences in a range'        => ['/^[\x41-\x{5A}]$/u'],
            'brace that is not a quantifier'     => ['/x{a}/'],
            'quantifier with an upper bound'     => ['/^x{1,3}$/'],
            'escaped delimiter'                  => ['/^\/$/'],
            'anchors'                            => ['/\Aa\z|\Ab\Z/'],
            'dollar end only'                    => ['/^[a-c]\\n$/D'],
            'multiline'                          => ['/^a$/m'],
        ];
    }

    /**
     * @return array<string, array{non-empty-string, string}>
     */
    public static function simplestStringProvider(): array
    {
        return [
            'character classes and quantifiers' => ['/\A[a-z]+-\d{2,4}\z/', 'a-00'],
            'alternation'                       => ['/^(foo|bar)+$/', 'foo'],
            'escape sequences for classes'      => ['/^\w+@\w+\.(com|org)$/', 'a@a.com'],
            'optional character'                => ['/^colou?r$/', 'color'],
            'negated class'                     => ['/^[^a-z]$/', 'A'],
            'dot'                               => ['/^.$/u', 'a'],
            'negated escape sequence'           => ['/^\W$/u', ' '],
            'digits'                            => ['/^\d{3}$/', '000'],
            'emoji'                             => ['/^[\x{1F600}-\x{1F64F}]$/u', "\u{1F600}"],
            'escaped characters'                => ['/^\x41\x{42}\0\012$/', "AB\0\n"],
            'quoted characters'                 => ['/^\QA.B*\E$/', 'A.B*'],
            'extended modifier'                 => ["/^a b # comment\n c$/x", 'abc'],
            'comment group'                     => ['/^(?#comment)x$/', 'x'],
            'closing bracket first in a class'  => ['/^[]a]$/', 'a'],
            'other delimiters'                  => ['#^/path/[^/]+$#', '/path/a'],
            'bracket delimiters'                => ['{^a{2}$}', 'aa'],
            'brace that is not a quantifier'    => ['/x{a}/', 'x{a}'],
            'whitespace'                        => ['/^\h\v$/u', " \n"],
        ];
    }

    /**
     * @return array<string, array{non-empty-string, string}>
     */
    public static function unsupportedRegularExpressionProvider(): array
    {
        return [
            'lookahead assertion'             => ['/^(?=a)a$/', 'lookahead assertions are not supported'],
            'negative lookahead assertion'    => ['/^(?!b)a$/', 'lookahead assertions are not supported'],
            'lookbehind assertion'            => ['/^a(?<=a)$/', 'lookbehind assertions are not supported'],
            'negative lookbehind assertion'   => ['/^a(?<!b)$/', 'lookbehind assertions are not supported'],
            'backreference'                   => ['/^(a)\1$/', 'backreferences and octal escape sequences other than \0 and \o{...} are not supported'],
            'named backreference'             => ['/^(?<a>a)\k<a>$/', 'backreferences are not supported'],
            'named backreference in Python'   => ['/^(?P<a>a)(?P=a)$/', 'backreferences are not supported'],
            'relative backreference'          => ['/^(a)\g{-1}$/', 'backreferences are not supported'],
            'Unicode property'                => ['/^\p{L}$/u', 'Unicode properties are not supported'],
            'inline option'                   => ['/(?i)a/', 'inline options, recursion, and conditional subpatterns are not supported, (?i was found'],
            'recursion'                       => ['/^a(?R)?$/', 'inline options, recursion, and conditional subpatterns are not supported, (?R was found'],
            'backtracking control verb'       => ['/^a(*SKIP)$/', 'backtracking control verbs are not supported'],
            'other escape sequence'           => ['/^a\K$/', 'the escape sequence \K is not supported'],
            'quoted characters in a class'    => ['/^[\Qa]\E]$/', '\Q...\E is not supported in a character class'],
            'class that matches no character' => ['/^[^\x00-\x{10FFFF}]$/u', 'a character class that matches no character cannot be generated from'],
        ];
    }

    /**
     * @param non-empty-string $regularExpression
     */
    #[DataProvider('regularExpressionProvider')]
    public function testGeneratesStringsThatTheRegularExpressionMatches(string $regularExpression): void
    {
        $values = $this->values($regularExpression, 200);

        foreach ($values as $value) {
            $this->assertMatchesRegularExpression($regularExpression, $value);
        }
    }

    /**
     * @param non-empty-string $regularExpression
     */
    #[DataProvider('simplestStringProvider')]
    public function testTheSimplestStringConsistsOfTheSimplestCharactersTheMinimumNumberOfRepetitionsAndTheFirstAlternatives(string $regularExpression, string $expected): void
    {
        $this->assertSame($expected, $this->replay($regularExpression, []));
    }

    public function testRepeatsAQuantifiedPartBetweenItsMinimumAndItsMaximumNumberOfTimes(): void
    {
        $lengths = array_map(strlen(...), $this->values('/^a{2,4}$/', 200));

        $this->assertContains(2, $lengths);
        $this->assertContains(3, $lengths);
        $this->assertContains(4, $lengths);
    }

    public function testGeneratesTheAlternatives(): void
    {
        $values = $this->values('/^(a|b|c)$/', 100);

        $this->assertContains('a', $values);
        $this->assertContains('b', $values);
        $this->assertContains('c', $values);
        $this->assertSame('c', $this->replay('/^(a|b|c)$/', [2]));
    }

    public function testACharacterThatTheCharacterClassDoesNotMatchIsReplacedWithTheNextOneThatItMatches(): void
    {
        // [^a] contains A, whose index is 25, but [^a] does not match it
        // with the i modifier
        $this->assertSame('B', $this->replay('/^[^a]$/i', [25]));
    }

    public function testRejectsTheExampleWhenNoCharacterThatIsTriedIsMatchedByTheCharacterClass(): void
    {
        $node = new CharacterSetNode(CharacterSet::fromRanges([[0x61, 0x7A]], false), '/\A[0-9]\z/');

        $this->expectException(ExampleRejectedException::class);
        $this->expectExceptionMessageIs('None of 64 characters that were tried is matched by the character class');

        $node->generate(ChoiceSource::replay(new ChoiceSequence([])));
    }

    public function testDotDoesNotGenerateANewlineWithoutTheSModifier(): void
    {
        $newline = 95 + ord("\n");

        $this->assertSame("\v", $this->replay('/^.$/', [$newline]));
        $this->assertSame("\n", $this->replay('/^.$/s', [$newline]));
    }

    public function testGeneratesValidUtf8WithTheUModifier(): void
    {
        foreach ($this->values('/^.{5}$/u', 200) as $value) {
            $this->assertTrue(mb_check_encoding($value, 'UTF-8'));
        }
    }

    public function testGeneratesAnyByteWithoutTheUModifier(): void
    {
        $bytes = 0;

        foreach ($this->values('/^.$/s', 500) as $value) {
            if (ord($value) > 0x7F) {
                $bytes++;
            }
        }

        $this->assertGreaterThan(0, $bytes);
    }

    public function testRejectsTheExampleWhenTheRegularExpressionDoesNotMatchTheGeneratedString(): void
    {
        $this->expectException(ExampleRejectedException::class);
        $this->expectExceptionMessageIs('The generated string is not matched by the regular expression /a^b/');

        $this->replay('/a^b/', []);
    }

    /**
     * @param non-empty-string $regularExpression
     */
    #[DataProvider('unsupportedRegularExpressionProvider')]
    public function testReportsConstructsThatAreNotSupported(string $regularExpression, string $reason): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessageIs('Strings cannot be generated from the regular expression ' . $regularExpression . ': ' . $reason);

        Generators::fromRegularExpression($regularExpression);
    }

    public function testTheRegularExpressionMustBeValid(): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessageIs('/[/ is not a valid regular expression');

        Generators::fromRegularExpression('/[/');
    }

    public function testTheRegularExpressionMustNotBeEmpty(): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessageIs(' is not a valid regular expression');

        Generators::fromRegularExpression('');
    }

    /**
     * @param non-empty-string       $regularExpression
     * @param list<non-negative-int> $choices
     */
    private function replay(string $regularExpression, array $choices): string
    {
        $value = AbstractGenerator::from(Generators::fromRegularExpression($regularExpression))->generate(ChoiceSource::replay(new ChoiceSequence($choices)));

        $this->assertIsString($value);

        return $value;
    }

    /**
     * Generates strings, of which none must be rejected.
     *
     * @param non-empty-string $regularExpression
     * @param positive-int     $count
     *
     * @return list<string>
     */
    private function values(string $regularExpression, int $count): array
    {
        $generator  = AbstractGenerator::from(Generators::fromRegularExpression($regularExpression));
        $randomizer = new Randomizer(new Xoshiro256StarStar(1));
        $values     = [];

        for ($i = 0; $i < $count; $i++) {
            $value = $generator->generate(ChoiceSource::random($randomizer));

            $this->assertIsString($value);
            $this->assertSame(1, preg_match($regularExpression, $value));

            $values[] = $value;
        }

        $this->assertCount($count, $values);

        return $values;
    }
}
