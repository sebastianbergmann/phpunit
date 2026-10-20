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

use Fidry\CpuCoreCounter\Env;
use function floor;
use function is_string;
use function max;
use function preg_match;
use function sprintf;
use function var_export;

final class EnvVariableFinder implements CpuCoreFinder
{
    /** @var string */
    private $environmentVariableName;

    public function __construct(string $environmentVariableName)
    {
        $this->environmentVariableName = $environmentVariableName;
    }

    public function diagnose(): string
    {
        $value = Env::get($this->environmentVariableName);

        return sprintf(
            'parse(getenv(%s)=%s)=%s',
            $this->environmentVariableName,
            var_export($value, true),
            self::parse($value) ?? 'null'
        );
    }

    public function find(): ?int
    {
        return self::parse(Env::get($this->environmentVariableName));
    }

    public function toString(): string
    {
        return sprintf(
            'getenv(%s)',
            $this->environmentVariableName
        );
    }

    /**
     * @param string|false $value
     */
    private static function parse($value): ?int
    {
        if (!is_string($value)) {
            return null;
        }

        if (1 === preg_match('/^\d+$/', $value)) {
            $cores = (int) $value;

            return $cores > 0 ? $cores : null;
        }

        if (1 === preg_match('/^(\d+)m$/', $value, $matches)) {
            $cpus = $matches[1] / 1000;
        } elseif (1 === preg_match('/^\d+\.\d+$/', $value)) {
            $cpus = (float) $value;
        } else {
            return null;
        }

        // A fractional limit below one core, e.g. 500m, still allows one core.
        return $cpus > 0
            ? max(1, (int) floor($cpus))
            : null;
    }
}
