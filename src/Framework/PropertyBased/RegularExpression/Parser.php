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

use function array_slice;
use function assert;
use function chr;
use function count;
use function hexdec;
use function implode;
use function is_int;
use function ltrim;
use function mb_chr;
use function mb_ord;
use function mb_str_split;
use function octdec;
use function ord;
use function rtrim;
use function sprintf;
use function str_contains;
use function str_split;
use function strlen;
use function strrpos;
use function strtoupper;
use function substr;
use PHPUnit\Framework\PropertyBased\InvalidGeneratorArgumentException;

/**
 * Parses a regular expression, with its delimiters and modifiers, into the
 * nodes from which strings are generated that the regular expression matches
 * as a whole.
 *
 * Supported are literal characters, escaped characters, the dot, character
 * classes with ranges, negation, escape sequences, and POSIX classes, the
 * escape sequences \d, \D, \w, \W, \s, \S, \h, \H, \v, and \V, quantifiers,
 * lazy and possessive ones included, capturing, non-capturing, named,
 * atomic, and branch reset groups, alternation, comments, \Q...\E, and all
 * modifiers. Anchors and the other assertions that consume no characters,
 * such as ^, $, \A, \z, and \b, are generated as the empty string.
 *
 * Not supported are lookahead and lookbehind assertions, backreferences,
 * recursion, conditional subpatterns, inline options, Unicode properties, and
 * the other escape sequences that do not stand for a character or a class of
 * characters.
 *
 * The escape sequences \d, \w, and \s only generate ASCII characters, which
 * they match with and without the u modifier, and \D, \W, and \S, as well as
 * negated POSIX classes, only generate ASCII characters with the u modifier,
 * because it makes them not match other characters, such as letters, that
 * they do not match without it.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Parser
{
    private const string ALPHANUMERIC_CHARACTERS = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    private const string HEXADECIMAL_DIGITS      = '0123456789abcdefABCDEF';
    private const string OCTAL_DIGITS            = '01234567';
    private const string WHITESPACE              = " \t\n\r\f\v";

    /**
     * The delimiters of the regular expressions that check a generated
     * character, of which the first one that does not occur in the character
     * class is used.
     *
     * @var list<non-empty-string>
     */
    private const array DELIMITERS = ['/', '#', '~', '%', '@', '!', ';', '`'];
    private readonly string $regularExpression;

    /**
     * @var list<string>
     */
    private array $characters = [];
    private int $position     = 0;
    private string $modifiers = '';
    private bool $utf8        = false;
    private bool $extended    = false;
    private bool $dotAll      = false;

    /**
     * @param non-empty-string $regularExpression a valid regular expression
     */
    public function __construct(string $regularExpression)
    {
        $this->regularExpression = $regularExpression;
    }

    /**
     * @throws InvalidGeneratorArgumentException
     */
    public function parse(): Node
    {
        $trimmed          = ltrim($this->regularExpression);
        $openingDelimiter = $trimmed[0];

        $closingDelimiter = match ($openingDelimiter) {
            '('     => ')',
            '['     => ']',
            '{'     => '}',
            '<'     => '>',
            default => $openingDelimiter,
        };

        $end = strrpos($trimmed, $closingDelimiter);

        assert($end !== false && $end > 0);

        $body = substr($trimmed, 1, $end - 1);

        $this->modifiers = rtrim(substr($trimmed, $end + 1));
        $this->utf8      = str_contains($this->modifiers, 'u');
        $this->extended  = str_contains($this->modifiers, 'x');
        $this->dotAll    = str_contains($this->modifiers, 's');

        if ($this->utf8) {
            $this->characters = mb_str_split($body, 1, 'UTF-8');
        } else {
            $this->characters = str_split($body);
        }

        $this->position = 0;

        return $this->alternation();
    }

    /**
     * @throws InvalidGeneratorArgumentException
     */
    private function alternation(): Node
    {
        $alternatives = [$this->sequence()];

        while ($this->peek() === '|') {
            $this->position++;

            $alternatives[] = $this->sequence();
        }

        if (count($alternatives) === 1) {
            return $alternatives[0];
        }

        return new AlternationNode($alternatives);
    }

    /**
     * @throws InvalidGeneratorArgumentException
     */
    private function sequence(): Node
    {
        $nodes = [];

        while (true) {
            $this->skipWhitespaceAndComments();

            $character = $this->peek();

            if ($character === null || $character === '|' || $character === ')') {
                break;
            }

            $nodes[] = $this->quantified($this->atom());
        }

        if (count($nodes) === 1) {
            return $nodes[0];
        }

        return new SequenceNode($nodes);
    }

    private function quantified(Node $node): Node
    {
        while (true) {
            $this->skipWhitespaceAndComments();

            $character = $this->peek();

            if ($character === '*') {
                $bounds = [0, null];

                $this->position++;
            } elseif ($character === '+') {
                $bounds = [1, null];

                $this->position++;
            } elseif ($character === '?') {
                $bounds = [0, 1];

                $this->position++;
            } elseif ($character === '{') {
                $bounds = $this->bounds();

                if ($bounds === null) {
                    return $node;
                }
            } else {
                return $node;
            }

            // a lazy or possessive quantifier matches the same strings
            if ($this->peek() === '?' || $this->peek() === '+') {
                $this->position++;
            }

            $node = new QuantifiedNode($node, $bounds[0], $bounds[1]);
        }
    }

    /**
     * Parses a quantifier in braces, {n}, {n,}, {n,m}, or {,m}. A brace that
     * does not start a quantifier is a literal character.
     *
     * @return ?array{non-negative-int, ?non-negative-int}
     */
    private function bounds(): ?array
    {
        $offset  = 1;
        $minimum = $this->digits($offset);
        $maximum = $minimum;
        $comma   = false;

        if ($this->peek($offset) === ',') {
            $comma = true;

            $offset++;

            $maximum = $this->digits($offset);
        }

        if ($this->peek($offset) !== '}' || ($minimum === '' && $maximum === '')) {
            return null;
        }

        $this->position += $offset + 1;

        if ($comma && $maximum === '') {
            return [$this->number($minimum), null];
        }

        return [$this->number($minimum), $this->number($maximum)];
    }

    /**
     * @return string the decimal digits that start at the offset, which is advanced past them
     */
    private function digits(int &$offset): string
    {
        $digits = '';

        while (($character = $this->peek($offset)) !== null && $character !== '' && str_contains('0123456789', $character)) {
            $digits .= $character;

            $offset++;
        }

        return $digits;
    }

    /**
     * @return non-negative-int
     */
    private function number(string $digits): int
    {
        $number = (int) $digits;

        assert($number >= 0);

        return $number;
    }

    /**
     * @throws InvalidGeneratorArgumentException
     */
    private function atom(): Node
    {
        $character = $this->next();

        return match ($character) {
            '('      => $this->group(),
            '['      => $this->characterClass(),
            '.'      => $this->dot(),
            '^', '$' => new LiteralNode(''),
            '\\'     => $this->escapeOutsideOfCharacterClass(),
            default  => new LiteralNode($character),
        };
    }

    /**
     * @throws InvalidGeneratorArgumentException
     */
    private function group(): Node
    {
        if ($this->peek() === '*') {
            throw $this->unsupported('backtracking control verbs are not supported');
        }

        if ($this->peek() === '?') {
            $this->position++;

            $kind = $this->next();

            if ($kind === '<' && ($this->peek() === '=' || $this->peek() === '!')) {
                throw $this->unsupported('lookbehind assertions are not supported');
            }

            if ($kind === '<') {
                $this->skipTo('>');
            } elseif ($kind === '\'') {
                $this->skipTo('\'');
            } elseif ($kind === 'P' && $this->peek() === '<') {
                $this->skipTo('>');
            } elseif ($kind === 'P' && $this->peek() === '=') {
                throw $this->unsupported('backreferences are not supported');
            } elseif ($kind === '=' || $kind === '!') {
                throw $this->unsupported('lookahead assertions are not supported');
            } elseif ($kind === '#') {
                $this->skipTo(')');

                return new LiteralNode('');
            } elseif ($kind !== ':' && $kind !== '>' && $kind !== '|') {
                throw $this->unsupported(
                    sprintf(
                        'inline options, recursion, and conditional subpatterns are not supported, (?%s was found',
                        $kind,
                    ),
                );
            }
        }

        $node = $this->alternation();

        // the closing parenthesis
        $this->position++;

        return $node;
    }

    private function dot(): Node
    {
        if ($this->dotAll) {
            return new CharacterSetNode(CharacterSet::all($this->utf8));
        }

        return new CharacterSetNode(
            CharacterSet::fromCharacters([ord("\n")], $this->utf8)->complementWithin(CharacterSet::all($this->utf8)),
        );
    }

    /**
     * @throws InvalidGeneratorArgumentException
     */
    private function characterClass(): Node
    {
        $start   = $this->position - 1;
        $negated = false;

        if ($this->peek() === '^') {
            $negated = true;

            $this->position++;
        }

        $set   = CharacterSet::fromRanges([], $this->utf8);
        $first = true;

        while (true) {
            $character = $this->next();

            // a closing bracket that is the first character is a literal one
            if ($character === ']' && !$first) {
                break;
            }

            $first  = false;
            $member = $this->memberOfCharacterClass($character);

            if ($member === null) {
                continue;
            }

            if ($member instanceof CharacterSet) {
                $set = $set->union($member);

                continue;
            }

            $next = $this->peek(1);

            if ($this->peek() !== '-' || $next === null || $next === ']') {
                $set = $set->union(CharacterSet::fromCharacters([$member], $this->utf8));

                continue;
            }

            $this->position++;

            // a range cannot end with a class of characters such as \d
            $last = $this->memberOfCharacterClass($this->next());

            assert(is_int($last));

            $set = $set->union(CharacterSet::fromRanges([[$member, $last]], $this->utf8));
        }

        if ($negated) {
            $set = $set->complementWithin(CharacterSet::all($this->utf8));
        }

        if ($set->isEmpty()) {
            throw $this->unsupported('a character class that matches no character cannot be generated from');
        }

        return new CharacterSetNode(
            $set,
            $this->check(implode('', array_slice($this->characters, $start, $this->position - $start))),
        );
    }

    /**
     * @throws InvalidGeneratorArgumentException
     *
     * @return null|CharacterSet|non-negative-int null for \E, which is ignored
     */
    private function memberOfCharacterClass(string $character): null|CharacterSet|int
    {
        if ($character === '[' && $this->peek() === ':') {
            return $this->posixClass();
        }

        if ($character === '\\') {
            return $this->escape(true);
        }

        return $this->codePoint($character);
    }

    private function posixClass(): CharacterSet
    {
        // the colon
        $this->position++;

        $negated = false;

        if ($this->peek() === '^') {
            $negated = true;

            $this->position++;
        }

        $name = '';

        while (($character = $this->next()) !== ':') {
            $name .= $character;
        }

        // the closing bracket
        $this->position++;

        $set = CharacterSet::fromRanges(
            match ($name) {
                'alpha'  => [[0x41, 0x5A], [0x61, 0x7A]],
                'digit'  => [[0x30, 0x39]],
                'alnum'  => [[0x30, 0x39], [0x41, 0x5A], [0x61, 0x7A]],
                'upper'  => [[0x41, 0x5A]],
                'lower'  => [[0x61, 0x7A]],
                'space'  => [[0x09, 0x0D], [0x20, 0x20]],
                'blank'  => [[0x09, 0x09], [0x20, 0x20]],
                'cntrl'  => [[0x00, 0x1F], [0x7F, 0x7F]],
                'graph'  => [[0x21, 0x7E]],
                'print'  => [[0x20, 0x7E]],
                'punct'  => [[0x21, 0x2F], [0x3A, 0x40], [0x5B, 0x60], [0x7B, 0x7E]],
                'xdigit' => [[0x30, 0x39], [0x41, 0x46], [0x61, 0x66]],
                'word'   => [[0x30, 0x39], [0x41, 0x5A], [0x5F, 0x5F], [0x61, 0x7A]],
                default  => [[0x00, 0x7F]],
            },
            $this->utf8,
        );

        if ($negated) {
            return $this->negatedShorthand($set);
        }

        return $set;
    }

    /**
     * @throws InvalidGeneratorArgumentException
     */
    private function escapeOutsideOfCharacterClass(): Node
    {
        if ($this->peek() === 'Q') {
            $this->position++;

            return $this->quoted();
        }

        $escape = $this->escape(false);

        if ($escape === null) {
            return new LiteralNode('');
        }

        if ($escape instanceof CharacterSet) {
            return new CharacterSetNode($escape);
        }

        return new LiteralNode($this->encode($escape));
    }

    /**
     * The characters between \Q and \E, or the end of the regular expression,
     * are literal characters.
     */
    private function quoted(): Node
    {
        $literal = '';

        while (($character = $this->peek()) !== null) {
            if ($character === '\\' && $this->peek(1) === 'E') {
                $this->position += 2;

                break;
            }

            $literal .= $character;

            $this->position++;
        }

        return new LiteralNode($literal);
    }

    /**
     * @throws InvalidGeneratorArgumentException
     *
     * @return null|CharacterSet|non-negative-int a set for an escape sequence such as \d, the code point of an escaped character, or null for an assertion such as \b
     */
    private function escape(bool $inCharacterClass): null|CharacterSet|int
    {
        $character = $this->next();

        switch ($character) {
            case 'd':
                return $this->digitCharacters();

            case 'D':
                return $this->negatedShorthand($this->digitCharacters());

            case 'w':
                return $this->wordCharacters();

            case 'W':
                return $this->negatedShorthand($this->wordCharacters());

            case 's':
                return $this->whitespaceCharacters();

            case 'S':
                return $this->negatedShorthand($this->whitespaceCharacters());

            case 'h':
                return $this->horizontalWhitespaceCharacters();

            case 'H':
                return $this->negatedShorthand($this->horizontalWhitespaceCharacters());

            case 'v':
                return $this->verticalWhitespaceCharacters();

            case 'V':
                return $this->negatedShorthand($this->verticalWhitespaceCharacters());

            case 't':
                return 0x09;

            case 'n':
                return 0x0A;

            case 'r':
                return 0x0D;

            case 'f':
                return 0x0C;

            case 'e':
                return 0x1B;

            case 'a':
                return 0x07;

            case '0':
                return $this->octalCharacter();

            case 'o':
                return $this->inBase(octdec($this->inBraces()));

            case 'x':
                return $this->hexadecimalCharacter();

            case 'c':
                return $this->inBase(ord(strtoupper($this->next())) ^ 0x40);

            case 'b':
                if ($inCharacterClass) {
                    return 0x08;
                }

                return null;

                // assertions, which are not allowed in a character class, and
                // \E without \Q, which is ignored
            case 'B':
            case 'A':
            case 'z':
            case 'Z':
            case 'G':
            case 'E':
                return null;

                // \Q outside of a character class is parsed before
            case 'Q':
                throw $this->unsupported('\\Q...\\E is not supported in a character class');

            case 'p':
            case 'P':
            case 'X':
                throw $this->unsupported('Unicode properties are not supported');

            case 'g':
            case 'k':
                throw $this->unsupported('backreferences are not supported');
        }

        if (str_contains('123456789', $character)) {
            throw $this->unsupported('backreferences and octal escape sequences other than \\0 and \\o{...} are not supported');
        }

        if (str_contains(self::ALPHANUMERIC_CHARACTERS, $character)) {
            throw $this->unsupported(sprintf('the escape sequence \\%s is not supported', $character));
        }

        return $this->codePoint($character);
    }

    /**
     * \0 followed by up to two further octal digits.
     *
     * @return non-negative-int
     */
    private function octalCharacter(): int
    {
        $digits = '0';

        while (strlen($digits) < 3 && ($character = $this->peek()) !== null && $character !== '' && str_contains(self::OCTAL_DIGITS, $character)) {
            $digits .= $character;

            $this->position++;
        }

        return $this->inBase(octdec($digits));
    }

    /**
     * \x{...} or \x followed by one or two hexadecimal digits.
     *
     * @return non-negative-int
     */
    private function hexadecimalCharacter(): int
    {
        if ($this->peek() === '{') {
            return $this->inBase(hexdec($this->inBraces()));
        }

        $digits = '';

        while (strlen($digits) < 2 && ($character = $this->peek()) !== null && $character !== '' && str_contains(self::HEXADECIMAL_DIGITS, $character)) {
            $digits .= $character;

            $this->position++;
        }

        return $this->inBase(hexdec($digits));
    }

    /**
     * @return non-negative-int
     */
    private function inBase(float|int $number): int
    {
        assert(is_int($number) && $number >= 0);

        return $number;
    }

    /**
     * @return string the characters between the braces that follow
     */
    private function inBraces(): string
    {
        // the opening brace
        $this->position++;

        $characters = '';

        while (($character = $this->next()) !== '}') {
            $characters .= $character;
        }

        return $characters;
    }

    private function digitCharacters(): CharacterSet
    {
        return CharacterSet::fromRanges([[0x30, 0x39]], $this->utf8);
    }

    private function wordCharacters(): CharacterSet
    {
        return CharacterSet::fromRanges([[0x30, 0x39], [0x41, 0x5A], [0x5F, 0x5F], [0x61, 0x7A]], $this->utf8);
    }

    private function whitespaceCharacters(): CharacterSet
    {
        return CharacterSet::fromRanges([[0x09, 0x0D], [0x20, 0x20]], $this->utf8);
    }

    private function horizontalWhitespaceCharacters(): CharacterSet
    {
        $ranges = [[0x09, 0x09], [0x20, 0x20], [0xA0, 0xA0]];

        if ($this->utf8) {
            $ranges = [...$ranges, [0x1680, 0x1680], [0x180E, 0x180E], [0x2000, 0x200A], [0x202F, 0x202F], [0x205F, 0x205F], [0x3000, 0x3000]];
        }

        return CharacterSet::fromRanges($ranges, $this->utf8);
    }

    private function verticalWhitespaceCharacters(): CharacterSet
    {
        $ranges = [[0x0A, 0x0D], [0x85, 0x85]];

        if ($this->utf8) {
            $ranges[] = [0x2028, 0x2029];
        }

        return CharacterSet::fromRanges($ranges, $this->utf8);
    }

    /**
     * The characters that an escape sequence such as \D or a negated POSIX
     * class matches: with the u modifier, only the ASCII ones, because other
     * characters, such as letters, may be matched by the escape sequence that
     * is negated.
     */
    private function negatedShorthand(CharacterSet $set): CharacterSet
    {
        if ($this->utf8) {
            return $set->complementWithin(CharacterSet::ascii(true));
        }

        return $set->complementWithin(CharacterSet::all(false));
    }

    /**
     * A regular expression that matches exactly the characters that a
     * character class matches, with the modifiers of the regular expression.
     *
     * @return non-empty-string
     */
    private function check(string $characterClass): string
    {
        $delimiter = '/';

        foreach (self::DELIMITERS as $candidate) {
            if (!str_contains($characterClass, $candidate)) {
                $delimiter = $candidate;

                break;
            }
        }

        return $delimiter . '\A' . $characterClass . '\z' . $delimiter . $this->modifiers;
    }

    private function skipWhitespaceAndComments(): void
    {
        if (!$this->extended) {
            return;
        }

        while (($character = $this->peek()) !== null) {
            if ($character !== '' && str_contains(self::WHITESPACE, $character)) {
                $this->position++;

                continue;
            }

            if ($character !== '#') {
                return;
            }

            while (($character = $this->peek()) !== null && $character !== "\n") {
                $this->position++;
            }
        }
    }

    private function skipTo(string $delimiter): void
    {
        while ($this->next() !== $delimiter) {
        }
    }

    /**
     * @return non-negative-int
     */
    private function codePoint(string $character): int
    {
        if ($this->utf8) {
            $codePoint = mb_ord($character, 'UTF-8');

            assert($codePoint !== false && $codePoint >= 0);

            return $codePoint;
        }

        return ord($character);
    }

    /**
     * @param non-negative-int $codePoint
     */
    private function encode(int $codePoint): string
    {
        if ($this->utf8) {
            $character = mb_chr($codePoint, 'UTF-8');

            assert($character !== false);

            return $character;
        }

        // without the u modifier, PCRE rejects a code point that is greater
        // than 0xFF, so the regular expression has none
        assert($codePoint <= 0xFF);

        return chr($codePoint);
    }

    private function peek(int $offset = 0): ?string
    {
        return $this->characters[$this->position + $offset] ?? null;
    }

    private function next(): string
    {
        $character = $this->peek();

        assert($character !== null);

        $this->position++;

        return $character;
    }

    private function unsupported(string $reason): InvalidGeneratorArgumentException
    {
        return new InvalidGeneratorArgumentException(
            sprintf(
                'Strings cannot be generated from the regular expression %s: %s',
                $this->regularExpression,
                $reason,
            ),
        );
    }
}
