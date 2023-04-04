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
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @psalm-immutable
 */
final class Source
{
    private readonly FilterDirectoryCollection $includeDirectories;
    private readonly FileCollection $includeFiles;
    private readonly FilterDirectoryCollection $excludeDirectories;
    private readonly FileCollection $excludeFiles;

    public function __construct(FilterDirectoryCollection $includeDirectories, FileCollection $includeFiles, FilterDirectoryCollection $excludeDirectories, FileCollection $excludeFiles)
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

    public function includeFiles(): FileCollection
    {
        return $this->includeFiles;
    }

    public function excludeDirectories(): FilterDirectoryCollection
    {
        return $this->excludeDirectories;
    }

    public function excludeFiles(): FileCollection
    {
        return $this->excludeFiles;
    }

    public function notEmpty(): bool
    {
        return $this->includeDirectories->notEmpty() || $this->includeFiles->notEmpty();
    }
}
