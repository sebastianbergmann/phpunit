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

use PHPUnit\TextUI\Configuration\Directory;
use PHPUnit\TextUI\Configuration\NoCustomCssFileException;
use PHPUnit\TextUI\Configuration\NoHtmlCoverageTargetException;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @immutable
 */
final readonly class Html
{
    private ?Directory $target;
    private int $lowUpperBound;
    private int $highLowerBound;
    private string $colorSuccessLow;
    private string $colorSuccessLowDark;
    private string $colorSuccessMedium;
    private string $colorSuccessMediumDark;
    private string $colorSuccessHigh;
    private string $colorSuccessHighDark;
    private string $colorSuccessBar;
    private string $colorSuccessBarDark;
    private string $colorWarning;
    private string $colorWarningDark;
    private string $colorWarningBar;
    private string $colorWarningBarDark;
    private string $colorDanger;
    private string $colorDangerDark;
    private string $colorDangerBar;
    private string $colorDangerBarDark;
    private string $colorBreadcrumbs;
    private string $colorBreadcrumbsDark;
    private ?string $customCssFile;

    public function __construct(?Directory $target, int $lowUpperBound, int $highLowerBound, string $colorSuccessLow, string $colorSuccessLowDark, string $colorSuccessMedium, string $colorSuccessMediumDark, string $colorSuccessHigh, string $colorSuccessHighDark, string $colorSuccessBar, string $colorSuccessBarDark, string $colorWarning, string $colorWarningDark, string $colorWarningBar, string $colorWarningBarDark, string $colorDanger, string $colorDangerDark, string $colorDangerBar, string $colorDangerBarDark, string $colorBreadcrumbs, string $colorBreadcrumbsDark, ?string $customCssFile)
    {
        $this->target                 = $target;
        $this->lowUpperBound          = $lowUpperBound;
        $this->highLowerBound         = $highLowerBound;
        $this->colorSuccessLow        = $colorSuccessLow;
        $this->colorSuccessLowDark    = $colorSuccessLowDark;
        $this->colorSuccessMedium     = $colorSuccessMedium;
        $this->colorSuccessMediumDark = $colorSuccessMediumDark;
        $this->colorSuccessHigh       = $colorSuccessHigh;
        $this->colorSuccessHighDark   = $colorSuccessHighDark;
        $this->colorSuccessBar        = $colorSuccessBar;
        $this->colorSuccessBarDark    = $colorSuccessBarDark;
        $this->colorWarning           = $colorWarning;
        $this->colorWarningDark       = $colorWarningDark;
        $this->colorWarningBar        = $colorWarningBar;
        $this->colorWarningBarDark    = $colorWarningBarDark;
        $this->colorDanger            = $colorDanger;
        $this->colorDangerDark        = $colorDangerDark;
        $this->colorDangerBar         = $colorDangerBar;
        $this->colorDangerBarDark     = $colorDangerBarDark;
        $this->colorBreadcrumbs       = $colorBreadcrumbs;
        $this->colorBreadcrumbsDark   = $colorBreadcrumbsDark;
        $this->customCssFile          = $customCssFile;
    }

    /**
     * @phpstan-assert-if-true !null $this->target
     */
    public function hasTarget(): bool
    {
        return $this->target !== null;
    }

    /**
     * @throws NoHtmlCoverageTargetException
     */
    public function target(): Directory
    {
        if (!$this->hasTarget()) {
            throw new NoHtmlCoverageTargetException;
        }

        return $this->target;
    }

    public function lowUpperBound(): int
    {
        return $this->lowUpperBound;
    }

    public function highLowerBound(): int
    {
        return $this->highLowerBound;
    }

    public function colorSuccessLow(): string
    {
        return $this->colorSuccessLow;
    }

    public function colorSuccessLowDark(): string
    {
        return $this->colorSuccessLowDark;
    }

    public function colorSuccessMedium(): string
    {
        return $this->colorSuccessMedium;
    }

    public function colorSuccessMediumDark(): string
    {
        return $this->colorSuccessMediumDark;
    }

    public function colorSuccessHigh(): string
    {
        return $this->colorSuccessHigh;
    }

    public function colorSuccessHighDark(): string
    {
        return $this->colorSuccessHighDark;
    }

    public function colorSuccessBar(): string
    {
        return $this->colorSuccessBar;
    }

    public function colorSuccessBarDark(): string
    {
        return $this->colorSuccessBarDark;
    }

    public function colorWarning(): string
    {
        return $this->colorWarning;
    }

    public function colorWarningDark(): string
    {
        return $this->colorWarningDark;
    }

    public function colorWarningBar(): string
    {
        return $this->colorWarningBar;
    }

    public function colorWarningBarDark(): string
    {
        return $this->colorWarningBarDark;
    }

    public function colorDanger(): string
    {
        return $this->colorDanger;
    }

    public function colorDangerDark(): string
    {
        return $this->colorDangerDark;
    }

    public function colorDangerBar(): string
    {
        return $this->colorDangerBar;
    }

    public function colorDangerBarDark(): string
    {
        return $this->colorDangerBarDark;
    }

    public function colorBreadcrumbs(): string
    {
        return $this->colorBreadcrumbs;
    }

    public function colorBreadcrumbsDark(): string
    {
        return $this->colorBreadcrumbsDark;
    }

    /**
     * @phpstan-assert-if-true !null $this->customCssFile
     */
    public function hasCustomCssFile(): bool
    {
        return $this->customCssFile !== null;
    }

    /**
     * @throws NoCustomCssFileException
     */
    public function customCssFile(): string
    {
        if (!$this->hasCustomCssFile()) {
            throw new NoCustomCssFileException;
        }

        return $this->customCssFile;
    }
}
