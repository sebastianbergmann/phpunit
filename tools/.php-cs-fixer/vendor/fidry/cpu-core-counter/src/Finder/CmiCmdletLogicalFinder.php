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

/**
 * Find the number of logical CPU cores for Windows leveraging the Get-CimInstance
 * cmdlet, which is a newer version that is recommended over Get-WmiObject.
 */
final class CmiCmdletLogicalFinder extends ProcOpenBasedFinder
{
    protected function getCommand(): string
    {
        // proc_open() runs commands through cmd.exe on Windows, so PowerShell
        // must be called explicitly.
        return 'powershell -NoProfile -NonInteractive -Command "(Get-CimInstance -ClassName Win32_ComputerSystem).NumberOfLogicalProcessors"';
    }

    public function toString(): string
    {
        return 'CmiCmdletLogicalFinder';
    }
}
