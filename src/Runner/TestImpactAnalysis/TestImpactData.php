<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\TestImpactAnalysis;

/**
 * The source files that were executed by each test of a test run.
 *
 * This is what a test executed, and not what it declares that it covers: a
 * test that exercises a class it does not name in its code coverage targets
 * depends on that class all the same.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
interface TestImpactData
{
    /**
     * A test that is run more than once contributes the files it executed on
     * its last run.
     *
     * @param non-empty-string       $test
     * @param list<non-empty-string> $files
     */
    public function record(string $test, array $files): void;

    /**
     * The files that were recorded in this process, and not what an earlier
     * test run recorded: a test that was not run in this process is not
     * something this process learned anything about.
     *
     * @return array<non-empty-string, list<non-empty-string>>
     */
    public function recorded(): array;
}
