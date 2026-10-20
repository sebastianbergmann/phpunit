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

namespace Fidry\CpuCoreCounter\Executor;

/**
 * @method string|null getUnavailabilityReason() The reason why the executor cannot be used, or null if it can. Not implementing it is deprecated: it will be added to this interface in the next major version.
 */
interface ProcessExecutor
{
    /**
     * @return array{string, string}|null STDOUT & STDERR tuple
     */
    public function execute(string $command): ?array;
}
