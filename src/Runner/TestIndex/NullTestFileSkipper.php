<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\TestIndex;

use Closure;
use Throwable;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class NullTestFileSkipper implements TestFileSkipper
{
    /**
     * @param non-empty-string       $file
     * @param list<non-empty-string> $groupsFromConfiguration
     */
    public function canSkipLoading(string $file, array $groupsFromConfiguration): bool
    {
        return false;
    }

    /**
     * @template T
     *
     * @param non-empty-string $file
     * @param Closure(): T     $load
     *
     * @throws Throwable
     *
     * @return T
     */
    public function record(string $file, Closure $load): mixed
    {
        return $load();
    }

    public function persist(): void
    {
    }
}
