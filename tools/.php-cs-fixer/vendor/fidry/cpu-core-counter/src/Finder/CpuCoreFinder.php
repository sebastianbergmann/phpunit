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

interface CpuCoreFinder
{
    /**
     * Provides an explanation which may offer some insight as to what the finder
     * will be able to find.
     *
     * This is practical to have an idea of what each finder will find collect
     * information for the unit tests, since integration tests are quite complicated
     * as dependent on complex infrastructures.
     */
    public function diagnose(): string;

    /**
     * Find the number of CPU cores. If it could not find it, returns null. The
     * means used to find the cores are at the implementation discretion.
     *
     * @return positive-int|null
     */
    public function find(): ?int;

    public function toString(): string;
}
