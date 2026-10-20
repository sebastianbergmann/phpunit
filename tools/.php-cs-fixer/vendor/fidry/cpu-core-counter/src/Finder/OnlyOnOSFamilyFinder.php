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

use function implode;
use function in_array;
use function sprintf;
use const PHP_OS_FAMILY;

final class OnlyOnOSFamilyFinder implements CpuCoreFinder
{
    /**
     * @var list<string>
     */
    private $skippedOSFamilies;

    /**
     * @var CpuCoreFinder
     */
    private $decoratedFinder;

    /**
     * @param string|list<string> $skippedOSFamilyOrFamilies
     */
    public function __construct(
        $skippedOSFamilyOrFamilies,
        CpuCoreFinder $decoratedFinder
    ) {
        $this->skippedOSFamilies = (array) $skippedOSFamilyOrFamilies;
        $this->decoratedFinder = $decoratedFinder;
    }

    public static function forWindows(CpuCoreFinder $decoratedFinder): self
    {
        return new self(
            'Windows',
            $decoratedFinder
        );
    }

    public static function forBSD(CpuCoreFinder $decoratedFinder): self
    {
        return new self(
            'BSD',
            $decoratedFinder
        );
    }

    public static function forDarwin(CpuCoreFinder $decoratedFinder): self
    {
        return new self(
            'Darwin',
            $decoratedFinder
        );
    }

    public static function forSolaris(CpuCoreFinder $decoratedFinder): self
    {
        return new self(
            'Solaris',
            $decoratedFinder
        );
    }

    public static function forLinux(CpuCoreFinder $decoratedFinder): self
    {
        return new self(
            'Linux',
            $decoratedFinder
        );
    }

    public function diagnose(): string
    {
        return $this->skip()
            ? sprintf(
                'Skipped platform detected ("%s").',
                PHP_OS_FAMILY
            )
            : $this->decoratedFinder->diagnose();
    }

    public function find(): ?int
    {
        return $this->skip()
            ? null
            : $this->decoratedFinder->find();
    }

    public function toString(): string
    {
        return sprintf(
            'OnlyOnOSFamilyFinder(only=(%s),%s)',
            implode(',', $this->skippedOSFamilies),
            $this->decoratedFinder->toString()
        );
    }

    private function skip(): bool
    {
        return !in_array(PHP_OS_FAMILY, $this->skippedOSFamilies, true);
    }
}
