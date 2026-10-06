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
 * And the files that, when they are added, can only affect a test through a
 * file that was changed to use them: such a file that no test is recorded as
 * depending on does not make every test run.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @immutable
 */
final readonly class TestImpactAnalysis
{
    private FilterDirectoryCollection $watchedDirectories;
    private FilterFileCollection $watchedFiles;
    private AddedFilesAreReachedThroughChanges $addedFilesAreReachedThroughChanges;

    public function __construct(FilterDirectoryCollection $watchedDirectories, FilterFileCollection $watchedFiles, AddedFilesAreReachedThroughChanges $addedFilesAreReachedThroughChanges)
    {
        $this->watchedDirectories                 = $watchedDirectories;
        $this->watchedFiles                       = $watchedFiles;
        $this->addedFilesAreReachedThroughChanges = $addedFilesAreReachedThroughChanges;
    }

    public function watchedDirectories(): FilterDirectoryCollection
    {
        return $this->watchedDirectories;
    }

    public function watchedFiles(): FilterFileCollection
    {
        return $this->watchedFiles;
    }

    public function addedFilesAreReachedThroughChanges(): AddedFilesAreReachedThroughChanges
    {
        return $this->addedFilesAreReachedThroughChanges;
    }
}
