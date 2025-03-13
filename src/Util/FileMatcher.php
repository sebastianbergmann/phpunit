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

use function array_key_last;
use function array_pop;
use function count;
use function ctype_alpha;
use function preg_quote;
use function sprintf;
use function strlen;
use RuntimeException;

/**
 * FileMatcher ultimately attempts to emulate the behavior `php-file-iterator`
 * which *mostly* comes down to emulating PHP's glob function on file paths
 * based on POSIX.2:
 *
 * - https://en.wikipedia.org/wiki/Glob_(programming)
 * - https://man7.org/linux/man-pages/man7/glob.7.html
 *
 * The file matcher compiles the regex in three passes:
 *
 * - Tokenise interesting chars in the glob grammar.
 * - Process the tokens and reorient them to produce regex.
 * - Map the processed tokens to regular expression segments.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @phpstan-type token array{self::T_*,string}
 */
final readonly class FileMatcher
{
    private const T_BRACKET_OPEN    = 'bracket_open';
    private const T_BRACKET_CLOSE   = 'bracket_close';
    private const T_BANG            = 'bang';
    private const T_HYPHEN          = 'hyphen';
    private const T_ASTERIX         = 'asterix';
    private const T_SLASH           = 'slash';
    private const T_BACKSLASH       = 'backslash';
    private const T_CHAR            = 'char';
    private const T_GREEDY_GLOBSTAR = 'greedy_globstar';
    private const T_QUERY           = 'query';
    private const T_GLOBSTAR        = 'globstar';
    private const T_COLON           = 'colon';
    private const T_CHAR_CLASS      = 'char_class';

    /**
     * Compile a regex for the given glob.
     */
    public static function toRegEx(string $glob): FileMatcherRegex
    {
        $tokens = self::tokenize($glob);
        $tokens = self::processTokens($tokens);

        return self::mapToRegex($tokens);
    }

    /**
     * @param list<token> $tokens
     */
    private static function mapToRegex(array $tokens): FileMatcherRegex
    {
        $regex = '';

        foreach ($tokens as $token) {
            $type = $token[0];
            $regex .= match ($type) {
                // literal char
                self::T_CHAR => preg_quote($token[1]),

                // literal directory separator
                self::T_SLASH => '/',
                self::T_QUERY => '.',
                self::T_BANG  => '^',

                // match any segment up until the next directory separator
                self::T_ASTERIX         => '[^/]*',
                self::T_GREEDY_GLOBSTAR => '.*',
                self::T_GLOBSTAR        => '/([^/]+/)*',
                self::T_BRACKET_OPEN    => '[',
                self::T_BRACKET_CLOSE   => ']',
                self::T_HYPHEN          => '-',
                self::T_CHAR_CLASS      => '[:' . $token[1] . ':]',
                default                 => throw new RuntimeException(sprintf(
                    'Unhandled token type: %s - this should not happen',
                    $type,
                )),
            };
        }
        $regex .= '(/|$)';

        return new FileMatcherRegex('{^' . $regex . '}');
    }

    /**
     * @return list<token>
     */
    private static function tokenize(string $glob): array
    {
        $length = strlen($glob);

        $tokens = [];

        for ($i = 0; $i < $length; $i++) {
            $c = $glob[$i];

            $tokens[] = match ($c) {
                '['     => [self::T_BRACKET_OPEN, $c],
                ']'     => [self::T_BRACKET_CLOSE, $c],
                '?'     => [self::T_QUERY, $c],
                '-'     => [self::T_HYPHEN, $c],
                '!'     => [self::T_BANG, $c],
                '*'     => [self::T_ASTERIX, $c],
                '/'     => [self::T_SLASH, $c],
                '\\'    => [self::T_BACKSLASH, $c],
                ':'     => [self::T_COLON, $c],
                default => [self::T_CHAR, $c],
            };
        }

        return $tokens;
    }

    /**
     * @param list<token> $tokens
     *
     * @return list<token>
     */
    private static function processTokens(array $tokens): array
    {
        $resolved    = [];
        $escaped     = false;
        $bracketOpen = false;
        $brackets    = [];

        for ($offset = 0; $offset < count($tokens); $offset++) {
            [$type, $char] = $tokens[$offset];
            $nextType      = $tokens[$offset + 1][0] ?? null;

            if ($type === self::T_BACKSLASH && false === $escaped) {
                // skip the backslash and set flag to escape next token
                $escaped = true;

                continue;
            }

            if ($escaped === true) {
                // escaped flag is set, so make this a literal char and unset
                // the escaped flag
                $resolved[] = [self::T_CHAR, $char];
                $escaped    = false;

                continue;
            }

            // globstar must be preceded by and succeeded by a directory separator
            if (
                $type === self::T_SLASH &&
                $nextType === self::T_ASTERIX && ($tokens[$offset + 2][0] ?? null) === self::T_ASTERIX && ($tokens[$offset + 3][0] ?? null) === self::T_SLASH
            ) {
                $resolved[] = [self::T_GLOBSTAR, '**'];

                // we eat the two `*` and the trailing slash
                $offset += 3;

                continue;
            }

            // greedy globstar (trailing?)
            // TODO: this should probably only apply at the end of the string according to the webmozart implementation and therefore would be "T_TRAILING_GLOBSTAR"
            if (
                $type === self::T_SLASH &&
                ($tokens[$offset + 1][0] ?? null) === self::T_ASTERIX && ($tokens[$offset + 2][0] ?? null) === self::T_ASTERIX
            ) {
                $resolved[] = [self::T_GREEDY_GLOBSTAR, '**'];

                // we eat the two `*` in addition to the slash
                $offset += 2;

                continue;
            }

            // two consecutive ** which are not surrounded by `/` are invalid and
            // we interpret them as literals.
            if ($type === self::T_ASTERIX && ($tokens[$offset + 1][0] ?? null) === self::T_ASTERIX) {
                $resolved[] = [self::T_CHAR, $char];
                $resolved[] = [self::T_CHAR, $char];

                continue;
            }

            // complementation - only parse BANG if it is at the start of a character group
            if ($type === self::T_BANG && isset($resolved[array_key_last($resolved)]) && $resolved[array_key_last($resolved)][0] === self::T_BRACKET_OPEN) {
                $resolved[] = [self::T_BANG, '!'];

                continue;
            }

            // if this was _not_ a bang preceded by a `[` token then convert it
            // to a literal char
            if ($type === self::T_BANG) {
                $resolved[] = [self::T_CHAR, $char];

                continue;
            }

            // https://man7.org/linux/man-pages/man7/glob.7.html
            // > The string enclosed by the brackets cannot be empty; therefore
            // > ']' can be allowed between the brackets, provided that it is
            // > the first character.
            if ($type === self::T_BRACKET_OPEN && $nextType === self::T_BRACKET_CLOSE) {
                $bracketOpen = true;
                $resolved[]  = [self::T_BRACKET_OPEN, '['];
                $brackets[]  = array_key_last($resolved);
                $resolved[]  = [self::T_CHAR, ']'];
                $offset++;

                continue;
            }

            // if we're already in a bracket and the next two chars are [: then
            // start parsing a character class...
            if ($bracketOpen && $type === self::T_BRACKET_OPEN && $nextType === self::T_COLON) {
                // this looks like a named [:character:] class
                $class = '';
                $offset += 2;

                // parse the character class name
                while (ctype_alpha($tokens[$offset][1])) {
                    $class .= $tokens[$offset++][1];
                }

                // if followed by a `:` then it's a character class
                if ($tokens[$offset][0] === self::T_COLON) {
                    $offset++;
                    $resolved[] = [self::T_CHAR_CLASS, $class];

                    continue;
                }

                // otherwise it's a harmless literal
                $resolved[] = [self::T_CHAR, ':' . $class];
            }

            // if bracket is already open and we have another open bracket
            // interpret it as a literal
            if ($bracketOpen === true && $type === self::T_BRACKET_OPEN) {
                $resolved[] = [self::T_CHAR, $char];

                continue;
            }

            // if we are NOT in an open bracket and we have an open bracket
            // then pop the bracket on the stack and enter bracket-mode.
            if ($bracketOpen === false && $type === self::T_BRACKET_OPEN) {
                $bracketOpen = true;
                $resolved[]  = [$type, $char];
                $brackets[]  = array_key_last($resolved);

                continue;
            }

            // if are in a bracket and we get to bracket close then
            // pop the last open bracket off the stack and continue
            //
            // TODO: $bracketOpen === true below is not tested
            if ($bracketOpen === true && $type === self::T_BRACKET_CLOSE) {
                // TODO: this is not tested
                $bracketOpen = false;

                array_pop($brackets);
                $resolved[] = [$type, $char];

                continue;
            }

            $resolved[] = [$type, $char];
        }

        // foreach unterminated bracket replace it with a literal char
        foreach ($brackets as $unterminatedBracket) {
            $resolved[$unterminatedBracket] = [self::T_CHAR, '['];
        }

        return $resolved;
    }
}
