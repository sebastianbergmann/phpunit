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

use function array_shift;
use function debug_backtrace;
use function flush;
use function implode;
use function ob_end_clean;
use function ob_get_contents;
use function ob_get_level;
use function print_r;
use function rtrim;

class DevTool
{
    /**
     * print vars and caller infos then die.
     *
     * @param mixed $var
     */
    public static function print_rr($var): void
    {
        $obs  = 0;
        $buff = [];

        while (ob_get_level()) {
            $obs++;
            $buff[] = ob_get_contents();
            ob_end_clean();
            flush();
        }

        print "\n\n-- ";
        print rtrim(print_r($var, true), "\n");
        print " --\n";
        flush();

        $arr   = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
        $lines = [];
        $aline = '';

        foreach ($arr as $trace) {
            //if (stristr($trace['function'], 'print_rr')) continue;
            if (empty($trace['file']) || empty($trace['line'])) {
                continue;
            }
            $lines[] = $trace['file'] . ' -- ' . $trace['line'];

            if (empty($aline)) {
                $aline = $trace['file'] . ' -- ' . $trace['line'];
            }
        }
        print "\n\nCALLER-print_rr: {$aline}" . (empty($buff) ? '' : "\n\n\n" . implode("\n\n", $buff) . "\n\n\n");
    }

    /**
     * print vars and caller infos then die.
     *
     * @param mixed $var
     * @param mixed $msg
     */
    public static function print_rdie($var = '', $msg = ''): void
    {
        $obs  = 0;
        $buff = [];

        while (ob_get_level() > 0) {
            $obs++;
            $buff[] = ob_get_contents();
            ob_end_clean();
            flush();
        }

        print "\n\n";
        print null === $var ? rtrim(var_export($var, true)) : rtrim(print_r($var, true)) . "\n\t{$msg} \n";
        flush();

        // print caller
        $arr = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);

        $strs = ["\n\nCALLER-print_rdie: "];

        foreach ($arr as $trc) {
            if (isset($trc['file'], $trc['line'])) {
                $strs[] = $trc['file'] . ' -- ' . $trc['line'];

                if (count($strs) > 3) {
                    break;
                }
            }
        }

        $strs[] = "\n" . date('c');
        $strs[] = time();
        print implode("\n", $strs) . (empty($buff) ? "\n\n" : "\n\n\n" . implode("\n\n", $buff) . "\n\n\n");
        flush();

        exit();
    }

    public static function print_rdiefile(): void
    {
        $arr = debug_backtrace();
        $arr = array_shift($arr);
        $arr = [$arr['file'], $arr['line']];
        $str = implode(' ', $arr);
        self::print_rdie($str);
    }
}
