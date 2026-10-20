<?php

/*
 * This file is part of composer/pcre.
 *
 * (c) Composer <https://github.com/composer>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Composer\Pcre;

class PcreException extends \RuntimeException
{
    /**
     * @param  string $function
     * @param  string|string[] $pattern
     * @return self
     */
    public static function fromFunction($function, $pattern)
    {
        $code = preg_last_error();

        if (is_array($pattern)) {
            $pattern = implode(', ', $pattern);
        }

        return new PcreException($function.'(): failed executing "'.$pattern.'": '.self::pcreLastErrorMessage($code), $code);
    }

    /**
     * @param  int $code
     * @return string
     */
    private static function pcreLastErrorMessage($code)
    {
        if (function_exists('preg_last_error_msg')) {
            return preg_last_error_msg();
        }

        $constants = get_defined_constants(true);
        if (!isset($constants['pcre']) || !is_array($constants['pcre'])) {
            return 'UNDEFINED_ERROR';
        }

        foreach ($constants['pcre'] as $const => $val) {
            if ($val === $code && substr($const, -6) === '_ERROR') {
                return $const;
            }
        }

        return 'UNDEFINED_ERROR';
    }
}
