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

use function mb_ord;
use function preg_replace_callback;
use function sprintf;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Sanitizer
{
    /**
     * Replaces control characters with their visible \u{NNNN} escape sequence so that user-supplied
     * strings cannot distort or hide output that is read by a human:
     *
     * - C0 control characters (U+0000-U+001F) and DEL (U+007F), except for line feed, horizontal tab,
     *   and carriage return when it is followed by line feed
     * - C1 control characters (U+0080-U+009F)
     * - Unicode bidirectional formatting characters (U+202A-U+202E and U+2066-U+2069)
     *
     * ANSI escape sequences (CSI, OSC, ...) are neutralized because each of them begins with
     * ESC (U+001B) or a C1 control character.
     *
     * Matches UTF-8 byte sequences directly so that the function is safe to call on strings that
     * are not valid UTF-8.
     *
     * @see https://github.com/sebastianbergmann/phpunit/issues/6605
     */
    public static function sanitizeControlCharacters(string $value): string
    {
        return preg_replace_callback(
            '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]|\r(?!\n)|\xC2[\x80-\x9F]|\xE2\x80[\xAA-\xAE]|\xE2\x81[\xA6-\xA9]/',
            static fn (array $matches) => sprintf('\u{%04X}', mb_ord($matches[0], 'UTF-8')),
            $value,
        );
    }
}
