<?php

/*
 * This file is part of the Fidry CPUCounter Config package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fidry\CpuCoreCounter\Finder;

use function array_filter;
use function count;
use function explode;
use function trim;
use const PHP_EOL;

/**
 * Find the number of logical CPU cores for Windows.
 *
 * @see https://knowledge.informatica.com/s/article/151521
 */
final class WindowsRegistryLogicalFinder extends ProcOpenBasedFinder
{
    protected function getCommand(): string
    {
        return 'reg query HKEY_LOCAL_MACHINE\HARDWARE\DESCRIPTION\System\CentralProcessor';
    }

    public function toString(): string
    {
        return 'WindowsRegistryLogicalFinder';
    }

    protected function countCpuCores(string $process): ?int
    {
        $count = count(
            array_filter(
                explode(PHP_EOL, $process),
                static function (string $line): bool {
                    return '' !== trim($line);
                }
            )
        );

        return $count > 0 ? $count : null;
    }
}
