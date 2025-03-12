<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use PHPUnit\Exception;
use RuntimeException;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 * @phpstan-type token array{self::T_*,string}
 */
final readonly class FileMatcher
{
    private const T_BRACKET_OPEN = 'bracket_open';
    private const T_BRACKET_CLOSE = 'bracket_close';
    private const T_BANG = 'bang';
    private const T_HYPHEN = 'hyphen';
    private const T_ASTERIX = 'asterix';
    private const T_SLASH = 'slash';
    private const T_BACKSLASH = 'backslash';
    private const T_CHAR = 'char';
    private const T_GREEDY_GLOBSTAR = 'greedy_globstar';
    private const T_QUERY = 'query';
    private const T_GLOBSTAR = 'globstar';


    public static function match(string $path, FileMatcherPattern $pattern): bool
    {
        self::assertIsAbsolute($path);

        $regex = self::toRegEx($pattern->path);

        return preg_match($regex, $path) !== 0;
    }

    /**
     * Based on webmozart/glob
     *
     * @return string The regular expression for matching the glob.
     */
    public static function toRegEx($glob, $flags = 0): string
    {
        self::assertIsAbsolute($glob);

        $tokens = self::tokenize($glob);

        $regex = '';

        foreach ($tokens as $token) {
            $type = $token[0];
            $regex .= match ($type) {
                // literal char
                self::T_CHAR => preg_quote($token[1]),

                // literal directory separator
                self::T_SLASH => '/',
                self::T_QUERY => '.',

                // match any segment up until the next directory separator
                self::T_ASTERIX => '[^/]*',
                self::T_GREEDY_GLOBSTAR => '.*',
                self::T_GLOBSTAR => '/([^/]+/)*',
                self::T_BRACKET_OPEN => '[',
                self::T_BRACKET_CLOSE => ']',
                self::T_HYPHEN => '-',
                default => '',
            };
        }
        $regex .= '(/|$)';
        dump($tokens);
        dump($regex);

        return '{^'.$regex.'}';
    }

    private static function assertIsAbsolute(string $path): void
    {
        if (substr($path, 0, 1) !== '/') {
            throw new RuntimeException(sprintf(
                'Path "%s" must be absolute',
                $path
            ));
        }
    }

    /**
     * @return list<token>
     */
    private static function tokenize(string $glob): array
    {
        $length = strlen($glob);
        
        $tokens = [];
        
        for ($i = 0; $i < $length; ++$i) {
            $c = $glob[$i];
        
            $tokens[] = match ($c) {
                '[' => [self::T_BRACKET_OPEN, $c],
                ']' => [self::T_BRACKET_CLOSE, $c],
                '?' => [self::T_QUERY, $c],
                '-' => [self::T_HYPHEN, $c],
                '!' => [self::T_BANG, $c],
                '*' => [self::T_ASTERIX, $c],
                '/' => [self::T_SLASH, $c],
                '\\' => [self::T_BACKSLASH, $c],
                default => [self::T_CHAR, $c],
            };
        }

        return self::processTokens($tokens);
    }

    /**
     * @param list<token> $tokens
     * @return list<token>
     */
    private static function processTokens(array $tokens): array
    {
        $resolved = [];
        $escaped = false;
        $brackets = [];
        for ($offset = 0; $offset < count($tokens); $offset++) {
            [$type, $char] = $tokens[$offset];

            if ($type === self::T_BACKSLASH && false === $escaped) {
                $escaped = true;
                continue;
            }

            if ($escaped === true) {
                $resolved[] = [self::T_CHAR, $char];
                continue;
            }

            // normal globstar
            if (
                $type === self::T_SLASH &&
                ($tokens[$offset + 1][0] ?? null) === self::T_ASTERIX && ($tokens[$offset + 2][0] ?? null) === self::T_ASTERIX && ($tokens[$offset + 3][0] ?? null) === self::T_SLASH
            ) {
                $resolved[] = [self::T_GLOBSTAR, '**'];

                // we eat the two `*` in addition to the slash
                $offset += 3;
                continue;
            }

            // greedy globstar (trailing?)
            if (
                $type === self::T_SLASH &&
                ($tokens[$offset + 1][0] ?? null) === self::T_ASTERIX && ($tokens[$offset + 2][0] ?? null) === self::T_ASTERIX
            ) {
                $resolved[] = [self::T_GREEDY_GLOBSTAR, '**'];

                // we eat the two `*` in addition to the slash
                $offset += 2;
                continue;
            }

            if ($type === self::T_ASTERIX && ($tokens[$offset + 1][0] ?? null) === self::T_ASTERIX) {
                $resolved[] = [self::T_CHAR, $char];
                $resolved[] = [self::T_CHAR, $char];
                continue;
            }

            if ($type === self::T_BRACKET_OPEN) {
                $brackets[] = $offset;
            }
            if ($type === self::T_BRACKET_CLOSE) {
                array_pop($brackets);
            }

            $resolved[] = [$type, $char];
        }
        foreach ($brackets as $unterminatedBracket) {
            $resolved[$unterminatedBracket] = [self::T_CHAR, '['];
        }
        return $resolved;
    }
}
