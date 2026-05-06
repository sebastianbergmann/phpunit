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
     * Replaces Unicode bidirectional formatting characters with their visible \u{NNNN} escape sequence.
     *
     * Matches the UTF-8 byte sequences for U+202A-U+202E and U+2066-U+2069 directly so that
     * the function is safe to call on strings that are not valid UTF-8.
     *
     * @see https://github.com/sebastianbergmann/phpunit/issues/6605
     */
    public static function sanitizeBidirectionalControlCharacters(string $value): string
    {
        return preg_replace_callback(
            '/\xE2\x80[\xAA-\xAE]|\xE2\x81[\xA6-\xA9]/',
            static fn (array $matches) => sprintf('\u{%04X}', mb_ord($matches[0], 'UTF-8')),
            $value,
        );
    }
}
