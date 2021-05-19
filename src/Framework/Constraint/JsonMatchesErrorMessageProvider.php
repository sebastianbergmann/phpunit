<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Constraint;

use const JSON_ERROR_CTRL_CHAR;
use const JSON_ERROR_DEPTH;
use const JSON_ERROR_NONE;
use const JSON_ERROR_STATE_MISMATCH;
use const JSON_ERROR_SYNTAX;
use const JSON_ERROR_UTF8;
use function strtolower;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class JsonMatchesErrorMessageProvider
{
    /**
     * Translates JSON error to a human readable string.
     */
    public static function determineJsonError(int $error, string $prefix = ''): ?string
    {
        return match ($error) {
            JSON_ERROR_NONE           => null,
            JSON_ERROR_DEPTH          => $prefix . 'Maximum stack depth exceeded',
            JSON_ERROR_STATE_MISMATCH => $prefix . 'Underflow or the modes mismatch',
            JSON_ERROR_CTRL_CHAR      => $prefix . 'Unexpected control character found',
            JSON_ERROR_SYNTAX         => $prefix . 'Syntax error, malformed JSON',
            JSON_ERROR_UTF8           => $prefix . 'Malformed UTF-8 characters, possibly incorrectly encoded',
            default                   => $prefix . 'Unknown error',
        };
    }

    /**
     * Translates a given type to a human readable message prefix.
     */
    public static function translateTypeToPrefix(string $type): string
    {
        return match (strtolower($type)) {
            'expected' => 'Expected value JSON decode error - ',
            'actual'   => 'Actual value JSON decode error - ',
            default    => '',
        };
    }
}
