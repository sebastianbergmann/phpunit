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

use Fidry\CpuCoreCounter\Executor\ProcessExecutor;
use function sprintf;

/**
 * The number of (logical) cores.
 *
 * @see https://github.com/infection/infection/blob/fbd8c44/src/Resource/Processor/CpuCoresCountProvider.php#L69-L82
 * @see https://unix.stackexchange.com/questions/146051/number-of-processors-in-proc-cpuinfo
 */
final class NProcFinder extends ProcOpenBasedFinder
{
    /**
     * @var bool
     */
    private $all;

    /**
     * @param bool $all If disabled, counts the cores available to the current process. If
     *                  enabled, also counts offline or reserved cores, e.g. a 6-vCPU VMware VM
     *                  with CPU hot-add can report 128. It is therefore disabled by default.
     */
    public function __construct(
        bool $all = false,
        ?ProcessExecutor $executor = null
    ) {
        parent::__construct($executor);

        $this->all = $all;
    }

    public function toString(): string
    {
        return sprintf(
            'NProcFinder(all=%s)',
            $this->all ? 'true' : 'false'
        );
    }

    protected function getCommand(): string
    {
        return 'nproc'.($this->all ? ' --all' : '');
    }
}
