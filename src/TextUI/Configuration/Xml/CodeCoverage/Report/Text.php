<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Report;

use PHPUnit\TextUI\Configuration\File;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @psalm-immutable
 */
final readonly class Text
{
    private File $target;
    private bool $showUncoveredFiles;
    private bool $showOnlySummary;

    public function __construct(File $target, bool $showUncoveredFiles, bool $showOnlySummary)
    {
        $this->target             = $target;
        $this->showUncoveredFiles = $showUncoveredFiles;
        $this->showOnlySummary    = $showOnlySummary;
    }

    public function target(): File
    {
        return $this->target;
    }

    public function showUncoveredFiles(): bool
    {
        return $this->showUncoveredFiles;
    }

    public function showOnlySummary(): bool
    {
        return $this->showOnlySummary;
    }
}
