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
 * Where a file that is added can only affect a test through a file that was
 * changed to use it, and not through code that finds it by looking at the
 * file system or by guessing its name from a convention.
 *
 * A file that was added, and that no test is recorded as depending on, makes
 * every test run unless it is one of these files: whether a framework
 * discovers what is in a directory is something only the project knows.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @immutable
 */
final readonly class AddedFilesAreReachedThroughChanges
{
    private FilterDirectoryCollection $includeDirectories;
    private FilterFileCollection $includeFiles;
    private FilterDirectoryCollection $excludeDirectories;
    private FilterFileCollection $excludeFiles;

    public function __construct(FilterDirectoryCollection $includeDirectories, FilterFileCollection $includeFiles, FilterDirectoryCollection $excludeDirectories, FilterFileCollection $excludeFiles)
    {
        $this->includeDirectories = $includeDirectories;
        $this->includeFiles       = $includeFiles;
        $this->excludeDirectories = $excludeDirectories;
        $this->excludeFiles       = $excludeFiles;
    }

    public function includeDirectories(): FilterDirectoryCollection
    {
        return $this->includeDirectories;
    }

    public function includeFiles(): FilterFileCollection
    {
        return $this->includeFiles;
    }

    public function excludeDirectories(): FilterDirectoryCollection
    {
        return $this->excludeDirectories;
    }

    public function excludeFiles(): FilterFileCollection
    {
        return $this->excludeFiles;
    }
}
