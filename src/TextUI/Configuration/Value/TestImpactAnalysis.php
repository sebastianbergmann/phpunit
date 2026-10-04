<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Configuration;

/**
 * The files outside of the code that is subject to code coverage analysis that
 * the tests can depend on in a way that executing them does not show, such as
 * configuration files that are read rather than executed: a change to one of
 * them that no test is recorded as depending on is a change nothing is known
 * about, and every test is run.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @immutable
 */
final readonly class TestImpactAnalysis
{
    private FilterDirectoryCollection $watchedDirectories;
    private FilterFileCollection $watchedFiles;

    public function __construct(FilterDirectoryCollection $watchedDirectories, FilterFileCollection $watchedFiles)
    {
        $this->watchedDirectories = $watchedDirectories;
        $this->watchedFiles       = $watchedFiles;
    }

    public function watchedDirectories(): FilterDirectoryCollection
    {
        return $this->watchedDirectories;
    }

    public function watchedFiles(): FilterFileCollection
    {
        return $this->watchedFiles;
    }
}
