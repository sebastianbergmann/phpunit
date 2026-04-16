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

    /**
     * @var non-negative-int
     */
    private int $lowUpperBound;

    /**
     * @var non-negative-int
     */
    private int $highLowerBound;

    /**
     * @var non-empty-string
     */
    private string $colorSuccessLow;

    /**
     * @var non-empty-string
     */
    private string $colorSuccessLowDark;

    /**
     * @var non-empty-string
     */
    private string $colorSuccessMedium;

    /**
     * @var non-empty-string
     */
    private string $colorSuccessMediumDark;

    /**
     * @var non-empty-string
     */
    private string $colorSuccessHigh;

    /**
     * @var non-empty-string
     */
    private string $colorSuccessHighDark;

    /**
     * @var non-empty-string
     */
    private string $colorSuccessBar;

    /**
     * @var non-empty-string
     */
    private string $colorSuccessBarDark;

    /**
     * @var non-empty-string
     */
    private string $colorWarning;

    /**
     * @var non-empty-string
     */
    private string $colorWarningDark;

    /**
     * @var non-empty-string
     */
    private string $colorWarningBar;

    /**
     * @var non-empty-string
     */
    private string $colorWarningBarDark;

    /**
     * @var non-empty-string
     */
    private string $colorDanger;

    /**
     * @var non-empty-string
     */
    private string $colorDangerDark;

    /**
     * @var non-empty-string
     */
    private string $colorDangerBar;

    /**
     * @var non-empty-string
     */
    private string $colorDangerBarDark;

    /**
     * @var non-empty-string
     */
    private string $colorBreadcrumbs;

    /**
     * @var non-empty-string
     */
    private string $colorBreadcrumbsDark;

    /**
     * @var ?non-empty-string
     */
    private ?string $customCssFile;

    /**
     * @param non-negative-int  $lowUpperBound
     * @param non-negative-int  $highLowerBound
     * @param non-empty-string  $colorSuccessLow
     * @param non-empty-string  $colorSuccessLowDark
     * @param non-empty-string  $colorSuccessMedium
     * @param non-empty-string  $colorSuccessMediumDark
     * @param non-empty-string  $colorSuccessHigh
     * @param non-empty-string  $colorSuccessHighDark
     * @param non-empty-string  $colorSuccessBar
     * @param non-empty-string  $colorSuccessBarDark
     * @param non-empty-string  $colorWarning
     * @param non-empty-string  $colorWarningDark
     * @param non-empty-string  $colorWarningBar
     * @param non-empty-string  $colorWarningBarDark
     * @param non-empty-string  $colorDanger
     * @param non-empty-string  $colorDangerDark
     * @param non-empty-string  $colorDangerBar
     * @param non-empty-string  $colorDangerBarDark
     * @param non-empty-string  $colorBreadcrumbs
     * @param non-empty-string  $colorBreadcrumbsDark
     * @param ?non-empty-string $customCssFile
     */
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

    /**
     * @return non-negative-int
     */
    public function lowUpperBound(): int
    {
        return $this->lowUpperBound;
    }

    /**
     * @return non-negative-int
     */
    public function highLowerBound(): int
    {
        return $this->highLowerBound;
    }

    /**
     * @return non-empty-string
     */
    public function colorSuccessLow(): string
    {
        return $this->colorSuccessLow;
    }

    /**
     * @return non-empty-string
     */
    public function colorSuccessLowDark(): string
    {
        return $this->colorSuccessLowDark;
    }

    /**
     * @return non-empty-string
     */
    public function colorSuccessMedium(): string
    {
        return $this->colorSuccessMedium;
    }

    /**
     * @return non-empty-string
     */
    public function colorSuccessMediumDark(): string
    {
        return $this->colorSuccessMediumDark;
    }

    /**
     * @return non-empty-string
     */
    public function colorSuccessHigh(): string
    {
        return $this->colorSuccessHigh;
    }

    /**
     * @return non-empty-string
     */
    public function colorSuccessHighDark(): string
    {
        return $this->colorSuccessHighDark;
    }

    /**
     * @return non-empty-string
     */
    public function colorSuccessBar(): string
    {
        return $this->colorSuccessBar;
    }

    /**
     * @return non-empty-string
     */
    public function colorSuccessBarDark(): string
    {
        return $this->colorSuccessBarDark;
    }

    /**
     * @return non-empty-string
     */
    public function colorWarning(): string
    {
        return $this->colorWarning;
    }

    /**
     * @return non-empty-string
     */
    public function colorWarningDark(): string
    {
        return $this->colorWarningDark;
    }

    /**
     * @return non-empty-string
     */
    public function colorWarningBar(): string
    {
        return $this->colorWarningBar;
    }

    /**
     * @return non-empty-string
     */
    public function colorWarningBarDark(): string
    {
        return $this->colorWarningBarDark;
    }

    /**
     * @return non-empty-string
     */
    public function colorDanger(): string
    {
        return $this->colorDanger;
    }

    /**
     * @return non-empty-string
     */
    public function colorDangerDark(): string
    {
        return $this->colorDangerDark;
    }

    /**
     * @return non-empty-string
     */
    public function colorDangerBar(): string
    {
        return $this->colorDangerBar;
    }

    /**
     * @return non-empty-string
     */
    public function colorDangerBarDark(): string
    {
        return $this->colorDangerBarDark;
    }

    /**
     * @return non-empty-string
     */
    public function colorBreadcrumbs(): string
    {
        return $this->colorBreadcrumbs;
    }

    /**
     * @return non-empty-string
     */
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
     *
     * @return non-empty-string
     */
    public function customCssFile(): string
    {
        if (!$this->hasCustomCssFile()) {
            throw new NoCustomCssFileException;
        }

        return $this->customCssFile;
    }
}
