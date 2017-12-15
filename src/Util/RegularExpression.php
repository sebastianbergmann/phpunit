<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

final class RegularExpression
{
    /**
     * @param string $pattern
     * @param string $subject
     * @param array  $matches
     * @param int    $flags
     * @param int    $offset
     *
     * @throws \Exception
     *
     * @return false|int
     */
    public static function safeMatch(string $pattern, string $subject, ?array $matches = null, int $flags = 0, int $offset = 0)
    {
        $handler_terminator = ErrorHandler::handleErrorOnce();
        $match              = \preg_match($pattern, $subject, $matches, $flags, $offset);
        $handler_terminator();

        return $match;
    }
}
