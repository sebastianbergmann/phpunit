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

use function array_map;
use function array_merge;
use function array_shift;
use function array_slice;
use function count;
use function current;
use function explode;
use function key;
use function next;
use function preg_replace;
use function reset;
use function sort;
use function strlen;
use function strpos;
use function strstr;
use function substr;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Getopt
{
    /**
     * @throws Exception
     */
    public static function parse(array $args, string $short_options, array $long_options = null): array
    {
        if (empty($args)) {
            return [[], []];
        }

        $opts     = [];
        $non_opts = [];

        if ($long_options) {
            sort($long_options);
        }

        if (isset($args[0][0]) && $args[0][0] !== '-') {
            array_shift($args);
        }

        reset($args);

        $args = array_map('trim', $args);

        /* @noinspection ComparisonOperandsOrderInspection */
        while (false !== $arg = current($args)) {
            $i = key($args);
            next($args);

            if ($arg === '') {
                continue;
            }

            if ($arg === '--') {
                $non_opts = array_merge($non_opts, array_slice($args, $i + 1));

                break;
            }

            if ($arg[0] !== '-' || (strlen($arg) > 1 && $arg[1] === '-' && !$long_options)) {
                $non_opts[] = $arg;

                continue;
            }

            if (strlen($arg) > 1 && $arg[1] === '-') {
                self::parseLongOption(
                    substr($arg, 2),
                    $long_options,
                    $opts,
                    $args
                );
            } else {
                self::parseShortOption(
                    substr($arg, 1),
                    $short_options,
                    $opts,
                    $args
                );
            }
        }

        return [$opts, $non_opts];
    }

    /**
     * @throws Exception
     */
    private static function parseShortOption(string $arg, string $short_options, array &$opts, array &$args): void
    {
        $argLen = strlen($arg);

        for ($i = 0; $i < $argLen; $i++) {
            $opt     = $arg[$i];
            $opt_arg = null;

            if ($arg[$i] === ':' || ($spec = strstr($short_options, $opt)) === false) {
                throw new Exception(
                    "unrecognized option -- {$opt}"
                );
            }

            if (strlen($spec) > 1 && $spec[1] === ':') {
                if ($i + 1 < $argLen) {
                    $opts[] = [$opt, substr($arg, $i + 1)];

                    break;
                }

                if (!(strlen($spec) > 2 && $spec[2] === ':')) {
                    /* @noinspection ComparisonOperandsOrderInspection */
                    if (false === $opt_arg = current($args)) {
                        throw new Exception(
                            "option requires an argument -- {$opt}"
                        );
                    }

                    next($args);
                }
            }

            $opts[] = [$opt, $opt_arg];
        }
    }

    /**
     * @throws Exception
     */
    private static function parseLongOption(string $arg, array $long_options, array &$opts, array &$args): void
    {
        $count   = count($long_options);
        $list    = explode('=', $arg);
        $opt     = $list[0];
        $opt_arg = null;

        if (count($list) > 1) {
            $opt_arg = $list[1];
        }

        $opt_len = strlen($opt);

        foreach ($long_options as $i => $long_opt) {
            $opt_start = substr($long_opt, 0, $opt_len);

            if ($opt_start !== $opt) {
                continue;
            }

            $opt_rest = substr($long_opt, $opt_len);

            if ($opt_rest !== '' && $i + 1 < $count && $opt[0] !== '=' && strpos($long_options[$i + 1], $opt) === 0) {
                throw new Exception(
                    "option --{$opt} is ambiguous"
                );
            }

            if (substr($long_opt, -1) === '=') {
                /* @noinspection StrlenInEmptyStringCheckContextInspection */
                if (substr($long_opt, -2) !== '==' && !strlen((string) $opt_arg)) {
                    /* @noinspection ComparisonOperandsOrderInspection */
                    if (false === $opt_arg = current($args)) {
                        throw new Exception(
                            "option --{$opt} requires an argument"
                        );
                    }

                    next($args);
                }
            } elseif ($opt_arg) {
                throw new Exception(
                    "option --{$opt} doesn't allow an argument"
                );
            }

            $full_option = '--' . preg_replace('/={1,2}$/', '', $long_opt);
            $opts[]      = [$full_option, $opt_arg];

            return;
        }

        throw new Exception("unrecognized option --{$opt}");
    }
}
