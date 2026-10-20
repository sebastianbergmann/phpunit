<?php

/*
 * This file is part of composer/xdebug-handler.
 *
 * (c) Composer <https://github.com/composer>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Composer\XdebugHandler;

use Composer\Pcre\Preg;

/**
 * Process utility functions
 *
 * @author John Stevenson <john-stevenson@blueyonder.co.uk>
 */
class Process
{
    /**
     * Escapes a string to be used as a shell argument.
     *
     * From https://github.com/johnstevenson/winbox-args
     * MIT Licensed (c) John Stevenson <john-stevenson@blueyonder.co.uk>
     *
     * @param string $arg  The argument to be escaped
     * @param bool $meta Additionally escape cmd.exe meta characters
     * @param bool $module The argument is the module to invoke
     */
    public static function escape(string $arg, bool $meta = true, bool $module = false): string
    {
        if (!defined('PHP_WINDOWS_VERSION_BUILD')) {
            return "'".str_replace("'", "'\\''", $arg)."'";
        }

        $quote = strpbrk($arg, " \t") !== false || $arg === '';

        $arg = Preg::replace('/(\\\\*)"/', '$1$1\\"', $arg, -1, $dquotes);
        $dquotes = (bool) $dquotes;

        if ($meta) {
            $meta = $dquotes || Preg::isMatch('/%[^%]+%/', $arg);

            if (!$meta) {
                $quote = $quote || strpbrk($arg, '^&|<>()') !== false;
            } elseif ($module && !$dquotes && $quote) {
                $meta = false;
            }
        }

        if ($quote) {
            $arg = '"'.(Preg::replace('/(\\\\*)$/', '$1$1', $arg)).'"';
        }

        if ($meta) {
            $arg = Preg::replace('/(["^&|<>()%])/', '^$1', $arg);
        }

        return $arg;
    }

    /**
     * Escapes an array of arguments that make up a shell command
     *
     * @param string[] $args Argument list, with the module name first
     */
    public static function escapeShellCommand(array $args): string
    {
        $command = '';
        $module = array_shift($args);

        if ($module !== null) {
            $command = self::escape($module, true, true);

            foreach ($args as $arg) {
                $command .= ' '.self::escape($arg);
            }
        }

        return $command;
    }

    /**
     * Makes putenv environment changes available in $_SERVER and $_ENV
     *
     * @param string $name
     * @param ?string $value A null value unsets the variable
      */
    public static function setEnv(string $name, ?string $value = null): bool
    {
        $unset = null === $value;

        if (!putenv($unset ? $name : $name.'='.$value)) {
            return false;
        }

        if ($unset) {
            unset($_SERVER[$name]);
        } else {
            $_SERVER[$name] = $value;
        }

        // Update $_ENV if it is being used
        if (false !== stripos((string) ini_get('variables_order'), 'E')) {
            if ($unset) {
                unset($_ENV[$name]);
            } else {
                $_ENV[$name] = $value;
            }
        }

        return true;
    }
}
