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

use function array_map;
use function array_values;
use function implode;
use function sprintf;
use const PHP_EOL;

/**
 * Executes the decorated finders in order and returns the first result found,
 * like the list of finders given to CpuCoreCounter. Use it where a single
 * finder is expected, to give a finder one or more fallbacks.
 */
final class FirstCpuCoreFinder implements CpuCoreFinder
{
    /**
     * @var list<CpuCoreFinder>
     */
    private $decoratedFinders;

    public function __construct(CpuCoreFinder ...$decoratedFinders)
    {
        $this->decoratedFinders = array_values($decoratedFinders);
    }

    public function diagnose(): string
    {
        $diagnoses = array_map(
            static function (CpuCoreFinder $finder): string {
                return $finder->toString().':'.PHP_EOL.$finder->diagnose();
            },
            $this->decoratedFinders
        );

        $diagnoses[] = sprintf(
            'Will return "%s".',
            $this->find() ?? 'null'
        );

        return implode(PHP_EOL, $diagnoses);
    }

    public function find(): ?int
    {
        foreach ($this->decoratedFinders as $finder) {
            $cores = $finder->find();

            if (null !== $cores) {
                return $cores;
            }
        }

        return null;
    }

    public function toString(): string
    {
        return sprintf(
            'FirstCpuCoreFinder(%s)',
            implode(
                ',',
                array_map(
                    static function (CpuCoreFinder $finder): string {
                        return $finder->toString();
                    },
                    $this->decoratedFinders
                )
            )
        );
    }
}
