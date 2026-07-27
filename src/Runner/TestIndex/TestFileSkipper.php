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
     * Called around loading a test file, so that what the file contains, and
     * whether loading it made PHPUnit warn about it, can be remembered.
     *
     * @param non-empty-string $file
     */
    public function startRecording(string $file): void;

    public function stopRecording(): void;

    public function persist(): void;
}
