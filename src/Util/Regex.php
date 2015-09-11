<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Error handler that converts PHP errors and warnings to exceptions.
 *
 * @since Class available since Release 4.2.0
 */
class PHPUnit_Util_Regex
{
    public static function pregMatchSafe($pattern, $subject, $matches = null, $flags = 0, $offset = 0)
    {
        $handler_terminator = PHPUnit_Util_ErrorHandler::handleErrorOnce(E_WARNING);
        $match              = preg_match($pattern, $subject, $matches, $flags, $offset);
        $handler_terminator(); // cleaning

        return $match;
    }
}
