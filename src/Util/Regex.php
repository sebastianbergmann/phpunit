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

    /**
     * Check if expression starts and end with '/'. If not, {@see pregMatchSafe} will not match, i.e.:
     * static::pregMatchSafe('unit|functional', 'unit') => false
     * static::pregMatchSafe('/unit|functional/', 'functional') => 1
     *
     * @param string $anExpression
     *
     * @return string
     */
    public static function unifyExpression($anExpression)
    {
        $unifiedExpression = '';
        $regexBoundingChar = '/';
        $expressionLength  = strlen($anExpression);

        if (!$expressionLength) {
            return $unifiedExpression;
        }

        if ($anExpression[0] !== $regexBoundingChar) {
            $unifiedExpression .= $regexBoundingChar;
        }
        $unifiedExpression .= $anExpression;

        /**
         * Check if in one of the last 4 chars a '/' is present.
         * There are few flags in regex which can be added on
         * the very end of the expression. For more details please
         * refer to the Regex documentation.
         */
        if (!static::pregMatchSafe('/\/[igm]{0,3}$/i', $anExpression, null, PREG_OFFSET_CAPTURE, 1)) {
            $unifiedExpression .= $regexBoundingChar;
        }

        return $unifiedExpression;
    }
}
