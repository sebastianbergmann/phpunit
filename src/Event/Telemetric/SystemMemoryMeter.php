<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Telemetric;

use function memory_get_peak_usage;
use function memory_get_usage;

final class SystemMemoryMeter implements MemoryMeter
{
    public function usage(): MemoryUsage
    {
        return new MemoryUsage(memory_get_usage(true));
    }

    public function peak(): MemoryUsage
    {
        return new MemoryUsage(memory_get_peak_usage(true));
    }
}
