<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased\Type;

use function array_shift;
use function assert;
use function bindec;
use function chr;
use function count;
use function hexdec;
use function is_int;
use function mb_chr;
use function octdec;
use function preg_match;
use function preg_replace_callback;
use function sprintf;
use function str_contains;
use function str_replace;
use function str_starts_with;
use function strlen;
use function strspn;
use function strtolower;
use function substr;
use PHPUnit\Framework\PropertyBased\InvalidTypeException;

/**
 * Splits a PHPDoc type into tokens. Tokens are read on demand, so that the
 * text that follows a type, for instance the description of a @param tag,
 * is not lexed.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Lexer
{
    private const string FLOAT      = '/\G-?(?:[0-9][0-9_]*\.[0-9_]*(?:[eE][+-]?[0-9]+)?|\.[0-9][0-9_]*(?:[eE][+-]?[0-9]+)?|[0-9][0-9_]*[eE][+-]?[0-9]+)/';
    private const string INTEGER    = '/\G-?(?:0[xX][0-9a-fA-F](?:_?[0-9a-fA-F])*|0[bB][01](?:_?[01])*|0[oO]?[0-7](?:_?[0-7])*|[1-9](?:_?[0-9])*|0)/';
    private const string IDENTIFIER = '/\G\\\\?[A-Za-z_\x80-\xFF][A-Za-z0-9_\x80-\xFF-]*(?:\\\\[A-Za-z_\x80-\xFF][A-Za-z0-9_\x80-\xFF-]*)*/';
    private const string VARIABLE   = '/\G\$[A-Za-z_\x80-\xFF][A-Za-z0-9_\x80-\xFF]*/';
    private const string STRING     = '/\G(?:\'(?:[^\'\\\\]|\\\\.)*\'|"(?:[^"\\\\]|\\\\.)*")/s';
    private const string SYMBOLS    = '<>{}()[],|?:&';
    private const string WHITESPACE = " \t\r\n";
    private readonly string $text;

    /**
     * @var non-negative-int
     */
    private int $offset = 0;

    /**
     * @var list<Token>
     */
    private array $lookahead = [];

    public function __construct(string $text)
    {
        $this->text = $text;
    }

    /**
     * @param non-negative-int $distance
     *
     * @throws InvalidTypeException
     */
    public function peek(int $distance = 0): Token
    {
        while (count($this->lookahead) <= $distance) {
            $this->lookahead[] = $this->lex();
        }

        assert(isset($this->lookahead[$distance]));

        return $this->lookahead[$distance];
    }

    /**
     * @throws InvalidTypeException
     */
    public function next(): Token
    {
        $token = $this->peek();

        array_shift($this->lookahead);

        return $token;
    }

    /**
     * @throws InvalidTypeException
     */
    private function lex(): Token
    {
        $this->offset += strspn($this->text, self::WHITESPACE, $this->offset);

        $offset = $this->offset;

        if ($offset >= strlen($this->text)) {
            return new Token(TokenKind::End, '', '', $offset);
        }

        if (str_starts_with(substr($this->text, $offset, 3), '...')) {
            return $this->token(TokenKind::Symbol, '...', '...');
        }

        if (preg_match(self::FLOAT, $this->text, $matches, 0, $offset) === 1) {
            return $this->token(TokenKind::Float, $matches[0], (float) str_replace('_', '', $matches[0]));
        }

        if (preg_match(self::INTEGER, $this->text, $matches, 0, $offset) === 1) {
            return $this->token(TokenKind::Integer, $matches[0], $this->integer($matches[0], $offset));
        }

        if (preg_match(self::IDENTIFIER, $this->text, $matches, 0, $offset) === 1) {
            return $this->token(TokenKind::Identifier, $matches[0], $matches[0]);
        }

        if (preg_match(self::VARIABLE, $this->text, $matches, 0, $offset) === 1) {
            return $this->token(TokenKind::Variable, $matches[0], substr($matches[0], 1));
        }

        if (preg_match(self::STRING, $this->text, $matches, 0, $offset) === 1) {
            return $this->token(TokenKind::String, $matches[0], $this->string($matches[0]));
        }

        $character = $this->text[$offset];

        if (str_contains(self::SYMBOLS, $character)) {
            return $this->token(TokenKind::Symbol, $character, $character);
        }

        if ($character === "'" || $character === '"') {
            throw new InvalidTypeException(
                sprintf(
                    'Unterminated string literal at offset %d',
                    $offset,
                ),
            );
        }

        throw new InvalidTypeException(
            sprintf(
                'Unexpected character "%s" at offset %d',
                $character,
                $offset,
            ),
        );
    }

    private function token(TokenKind $kind, string $text, float|int|string $value): Token
    {
        $token = new Token($kind, $text, $value, $this->offset);

        $this->offset += strlen($text);

        return $token;
    }

    /**
     * @throws InvalidTypeException
     */
    private function integer(string $text, int $offset): int
    {
        $negative = str_starts_with($text, '-');
        $digits   = strtolower(str_replace('_', '', substr($text, (int) $negative)));

        if (str_starts_with($digits, '0x')) {
            $value = hexdec(substr($digits, 2));
        } elseif (str_starts_with($digits, '0b')) {
            $value = bindec(substr($digits, 2));
        } elseif (str_starts_with($digits, '0o')) {
            $value = octdec(substr($digits, 2));
        } elseif (strlen($digits) > 1 && str_starts_with($digits, '0')) {
            $value = octdec(substr($digits, 1));
        } elseif ($negative) {
            // the magnitude of PHP_INT_MIN cannot be represented as an integer
            $value = (int) ('-' . $digits);

            if ($digits !== '0' && (string) $value !== '-' . $digits) {
                throw $this->integerOutOfRange($text, $offset);
            }

            return $value;
        } else {
            $value = (int) $digits;

            if ((string) $value !== $digits) {
                throw $this->integerOutOfRange($text, $offset);
            }
        }

        if (!is_int($value)) {
            throw $this->integerOutOfRange($text, $offset);
        }

        if ($negative) {
            return -$value;
        }

        return $value;
    }

    private function integerOutOfRange(string $text, int $offset): InvalidTypeException
    {
        return new InvalidTypeException(
            sprintf(
                'Integer literal %s at offset %d is out of range',
                $text,
                $offset,
            ),
        );
    }

    /**
     * Single-quoted strings support the escape sequences \' and \\, double-quoted
     * strings the escape sequences of double-quoted PHP strings.
     */
    private function string(string $text): string
    {
        $quote   = $text[0];
        $content = substr($text, 1, -1);

        if ($quote === "'") {
            $content = preg_replace_callback(
                '/\\\\([\'\\\\])/',
                static fn (array $matches): string => $matches[1],
                $content,
            );

            assert($content !== null);

            return $content;
        }

        $content = preg_replace_callback(
            '/\\\\(u\{[0-9a-fA-F]+\}|x[0-9a-fA-F]{1,2}|[0-7]{1,3}|.)/s',
            static function (array $matches): string
            {
                $escape = $matches[1];

                return match (true) {
                    $escape === 'n'                       => "\n",
                    $escape === 'r'                       => "\r",
                    $escape === 't'                       => "\t",
                    $escape === 'v'                       => "\v",
                    $escape === 'e'                       => "\e",
                    $escape === 'f'                       => "\f",
                    $escape === '\\'                      => '\\',
                    $escape === '$'                       => '$',
                    $escape === '"'                       => '"',
                    str_starts_with($escape, 'u{')        => (string) mb_chr((int) hexdec(substr($escape, 2, -1)), 'UTF-8'),
                    str_starts_with($escape, 'x')         => chr((int) hexdec(substr($escape, 1))),
                    preg_match('/^[0-7]/', $escape) === 1 => chr((int) octdec($escape) & 0xFF),
                    default                               => '\\' . $escape,
                };
            },
            $content,
        );

        assert($content !== null);

        return $content;
    }
}
