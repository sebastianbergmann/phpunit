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

use function preg_match;
use PHPUnit\Util\Error\Handler;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class RegularExpression
{
    public static function safeMatch(string $pattern, string $subject): false|int
    {
        return Handler::invokeIgnoringWarnings(
            static function () use ($pattern, $subject)
            {
                return preg_match($pattern, $subject);
            }
        );
    }
}
