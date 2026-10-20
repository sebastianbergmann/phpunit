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

use function sprintf;

/**
 * This finder returns whatever value you gave to it. This is useful for testing
 * or as a fallback to avoid to catch the NumberOfCpuCoreNotFound exception.
 */
final class DummyCpuCoreFinder implements CpuCoreFinder
{
    /**
     * @var positive-int
     */
    private $count;

    public function diagnose(): string
    {
        return sprintf(
            'Will return "%d".',
            $this->count
        );
    }

    /**
     * @param positive-int $count
     */
    public function __construct(int $count)
    {
        $this->count = $count;
    }

    /** @phpstan-ignore return.unusedType */
    public function find(): ?int
    {
        return $this->count;
    }

    public function toString(): string
    {
        return sprintf(
            'DummyCpuCoreFinder(value=%d)',
            $this->count
        );
    }
}
