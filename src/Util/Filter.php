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

use PHPUnit\Framework\Exception;
use PHPUnit\Framework\SyntheticError;

final class Filter
{
    public static function getFilteredStacktrace(\Throwable $t): string
    {
        $prefix = false;
        $script = \realpath($GLOBALS['_SERVER']['SCRIPT_NAME']);

        if (\defined('__PHPUNIT_PHAR_ROOT__')) {
            $prefix = __PHPUNIT_PHAR_ROOT__;
        }

        $filteredStacktrace = '';

        if ($t instanceof SyntheticError) {
            $eTrace = $t->getSyntheticTrace();
            $eFile  = $t->getSyntheticFile();
            $eLine  = $t->getSyntheticLine();
        } elseif ($t instanceof Exception) {
            $eTrace = $t->getSerializableTrace();
            $eFile  = $t->getFile();
            $eLine  = $t->getLine();
        } else {
            if ($t->getPrevious()) {
                $t = $t->getPrevious();
            }

            $eTrace = $t->getTrace();
            $eFile  = $t->getFile();
            $eLine  = $t->getLine();
        }

        if (!self::frameExists($eTrace, $eFile, $eLine)) {
            \array_unshift(
                $eTrace,
                ['file' => $eFile, 'line' => $eLine]
            );
        }

        $blacklist = new Blacklist;

        foreach ($eTrace as $frame) {
            if (isset($frame['file']) && \is_file($frame['file']) &&
                !$blacklist->isBlacklisted($frame['file']) &&
                ($prefix === false || \strpos($frame['file'], $prefix) !== 0) &&
                $frame['file'] !== $script) {
                $filteredStacktrace .= \sprintf(
                    "%s:%s\n",
                    $frame['file'],
                    $frame['line'] ?? '?'
                );
            }
        }

        return $filteredStacktrace;
    }

    private static function frameExists(array $trace, string $file, int $line): bool
    {
        foreach ($trace as $frame) {
            if (isset($frame['file']) && $frame['file'] === $file &&
                isset($frame['line']) && $frame['line'] === $line) {
                return true;
            }
        }

        return false;
    }
}
