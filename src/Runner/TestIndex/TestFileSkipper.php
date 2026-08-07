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
 * Decides whether a test file has to be loaded while the test suite is built.
 *
 * A file that does not have to be loaded contributes no test at all. This is
 * unrelated to a test that is skipped: a skipped test is part of the test suite
 * and is reported, a test in a file that was not loaded is neither.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This interface is not covered by the backward compatibility promise for PHPUnit
 */
interface TestFileSkipper
{
    /**
     * @param non-empty-string       $file
     * @param list<non-empty-string> $groupsFromConfiguration
     */
    public function canSkipLoading(string $file, array $groupsFromConfiguration): bool;

    /**
     * Loads a test file, and remembers what it contains and whether PHPUnit had
     * something to say about it while it was being loaded.
     *
     * Loading the file is what this is given, and not something the caller does
     * around it, because what PHPUnit has to say about a file is said while the
     * file itself is being executed: a file that is loaded before this is told
     * about it would be remembered as one PHPUnit says nothing about.
     *
     * A file that could not be loaded is not remembered, and must not be: what
     * it contains is not known, and a later run has to load it again in order
     * to fail the same way.
     *
     * @template T
     *
     * @param non-empty-string $file
     * @param Closure(): T     $load
     *
     * @throws Throwable
     *
     * @return T
     */
    public function record(string $file, Closure $load): mixed;

    public function persist(): void;
}
