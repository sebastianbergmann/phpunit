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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Source::class)]
#[UsesClass(FilterDirectory::class)]
#[UsesClass(FilterDirectoryCollection::class)]
#[UsesClass(FilterDirectoryCollectionIterator::class)]
#[UsesClass(File::class)]
#[UsesClass(FileCollection::class)]
#[UsesClass(FileCollectionIterator::class)]
#[Small]
final class SourceTest extends TestCase
{
    public function testHasIncludeDirectories(): void
    {
        $includeDirectories = FilterDirectoryCollection::fromArray([]);

        $source = new Source(
            null,
            false,
            $includeDirectories,
            FileCollection::fromArray([]),
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            [
                'functions' => [],
                'methods'   => [],
            ],
            false,
            false,
            false,
        );

        $this->assertSame($includeDirectories, $source->includeDirectories());
    }

    public function testHasIncludeFiles(): void
    {
        $includeFiles = FileCollection::fromArray([]);

        $source = new Source(
            null,
            false,
            FilterDirectoryCollection::fromArray([]),
            $includeFiles,
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            [
                'functions' => [],
                'methods'   => [],
            ],
            false,
            false,
            false,
        );

        $this->assertSame($includeFiles, $source->includeFiles());
    }

    public function testHasExcludeDirectories(): void
    {
        $excludeDirectories = FilterDirectoryCollection::fromArray([]);

        $source = new Source(
            null,
            false,
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            $excludeDirectories,
            FileCollection::fromArray([]),
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            [
                'functions' => [],
                'methods'   => [],
            ],
            false,
            false,
            false,
        );

        $this->assertSame($excludeDirectories, $source->excludeDirectories());
    }

    public function testHasExcludeFiles(): void
    {
        $excludeFiles = FileCollection::fromArray([]);

        $source = new Source(
            null,
            false,
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            FilterDirectoryCollection::fromArray([]),
            $excludeFiles,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            [
                'functions' => [],
                'methods'   => [],
            ],
            false,
            false,
            false,
        );

        $this->assertSame($excludeFiles, $source->excludeFiles());
    }

    public function testMayHaveBaseline(): void
    {
        $baseline = 'baseline.xml';

        $source = new Source(
            $baseline,
            false,
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            [
                'functions' => [],
                'methods'   => [],
            ],
            false,
            false,
            false,
        );

        $this->assertSame($baseline, $source->baseline());
        $this->assertTrue($source->hasBaseline());
        $this->assertTrue($source->useBaseline());
    }

    public function testMayNotHaveBaseline(): void
    {
        $source = new Source(
            null,
            false,
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            [
                'functions' => [],
                'methods'   => [],
            ],
            false,
            false,
            false,
        );

        $this->assertFalse($source->hasBaseline());
        $this->assertFalse($source->useBaseline());

        $this->expectException(NoBaselineException::class);

        $source->baseline();
    }

    public function testRestrictionOfDeprecationsMayBeDisabled(): void
    {
        $source = new Source(
            null,
            false,
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            [
                'functions' => [],
                'methods'   => [],
            ],
            false,
            false,
            false,
        );

        $this->assertFalse($source->restrictDeprecations());
    }

    public function testRestrictionOfDeprecationsMayBeEnabled(): void
    {
        $source = new Source(
            null,
            false,
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            true,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            [
                'functions' => [],
                'methods'   => [],
            ],
            false,
            false,
            false,
        );

        $this->assertTrue($source->restrictDeprecations());
    }

    public function testRestrictionOfNoticesMayBeDisabled(): void
    {
        $source = new Source(
            null,
            false,
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            [
                'functions' => [],
                'methods'   => [],
            ],
            false,
            false,
            false,
        );

        $this->assertFalse($source->restrictNotices());
    }

    public function testRestrictionOfNoticesMayBeEnabled(): void
    {
        $source = new Source(
            null,
            false,
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            false,
            true,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            [
                'functions' => [],
                'methods'   => [],
            ],
            false,
            false,
            false,
        );

        $this->assertTrue($source->restrictNotices());
    }

    public function testRestrictionOfWarningsMayBeDisabled(): void
    {
        $source = new Source(
            null,
            false,
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            [
                'functions' => [],
                'methods'   => [],
            ],
            false,
            false,
            false,
        );

        $this->assertFalse($source->restrictWarnings());
    }

    public function testRestrictionOfWarningsMayBeEnabled(): void
    {
        $source = new Source(
            null,
            false,
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            false,
            false,
            true,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            [
                'functions' => [],
                'methods'   => [],
            ],
            false,
            false,
            false,
        );

        $this->assertTrue($source->restrictWarnings());
    }

    public function testIgnoringOfSuppressedDeprecationsMayBeDisabled(): void
    {
        $source = new Source(
            null,
            false,
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            [
                'functions' => [],
                'methods'   => [],
            ],
            false,
            false,
            false,
        );

        $this->assertFalse($source->ignoreSuppressionOfDeprecations());
    }

    public function testIgnoringOfSuppressedDeprecationsMayBeEnabled(): void
    {
        $source = new Source(
            null,
            false,
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            false,
            false,
            false,
            true,
            false,
            false,
            false,
            false,
            false,
            false,
            [
                'functions' => [],
                'methods'   => [],
            ],
            false,
            false,
            false,
        );

        $this->assertTrue($source->ignoreSuppressionOfDeprecations());
    }

    public function testIgnoringOfSuppressedPhpDeprecationsMayBeDisabled(): void
    {
        $source = new Source(
            null,
            false,
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            [
                'functions' => [],
                'methods'   => [],
            ],
            false,
            false,
            false,
        );

        $this->assertFalse($source->ignoreSuppressionOfPhpDeprecations());
    }

    public function testIgnoringOfSuppressedPhpDeprecationsMayBeEnabled(): void
    {
        $source = new Source(
            null,
            false,
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            false,
            false,
            false,
            false,
            true,
            false,
            false,
            false,
            false,
            false,
            [
                'functions' => [],
                'methods'   => [],
            ],
            false,
            false,
            false,
        );

        $this->assertTrue($source->ignoreSuppressionOfPhpDeprecations());
    }

    public function testIgnoringOfSuppressedErrorsMayBeDisabled(): void
    {
        $source = new Source(
            null,
            false,
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            [
                'functions' => [],
                'methods'   => [],
            ],
            false,
            false,
            false,
        );

        $this->assertFalse($source->ignoreSuppressionOfErrors());
    }

    public function testIgnoringOfSuppressedErrorsMayBeEnabled(): void
    {
        $source = new Source(
            null,
            false,
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            false,
            false,
            false,
            false,
            false,
            true,
            false,
            false,
            false,
            false,
            [
                'functions' => [],
                'methods'   => [],
            ],
            false,
            false,
            false,
        );

        $this->assertTrue($source->ignoreSuppressionOfErrors());
    }

    public function testIgnoringOfSuppressedNoticesMayBeDisabled(): void
    {
        $source = new Source(
            null,
            false,
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            [
                'functions' => [],
                'methods'   => [],
            ],
            false,
            false,
            false,
        );

        $this->assertFalse($source->ignoreSuppressionOfNotices());
    }

    public function testIgnoringOfSuppressedNoticesMayBeEnabled(): void
    {
        $source = new Source(
            null,
            false,
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            false,
            false,
            false,
            false,
            false,
            false,
            true,
            false,
            false,
            false,
            [
                'functions' => [],
                'methods'   => [],
            ],
            false,
            false,
            false,
        );

        $this->assertTrue($source->ignoreSuppressionOfNotices());
    }

    public function testIgnoringOfSuppressedPhpNoticesMayBeDisabled(): void
    {
        $source = new Source(
            null,
            false,
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            [
                'functions' => [],
                'methods'   => [],
            ],
            false,
            false,
            false,
        );

        $this->assertFalse($source->ignoreSuppressionOfPhpNotices());
    }

    public function testIgnoringOfSuppressedPhpNoticesMayBeEnabled(): void
    {
        $source = new Source(
            null,
            false,
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            true,
            false,
            false,
            [
                'functions' => [],
                'methods'   => [],
            ],
            false,
            false,
            false,
        );

        $this->assertTrue($source->ignoreSuppressionOfPhpNotices());
    }

    public function testIgnoringOfSuppressedWarningsMayBeDisabled(): void
    {
        $source = new Source(
            null,
            false,
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            [
                'functions' => [],
                'methods'   => [],
            ],
            false,
            false,
            false,
        );

        $this->assertFalse($source->ignoreSuppressionOfWarnings());
    }

    public function testIgnoringOfSuppressedWarningsMayBeEnabled(): void
    {
        $source = new Source(
            null,
            false,
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            true,
            false,
            [
                'functions' => [],
                'methods'   => [],
            ],
            false,
            false,
            false,
        );

        $this->assertTrue($source->ignoreSuppressionOfWarnings());
    }

    public function testIgnoringOfSuppressedPhpWarningsMayBeDisabled(): void
    {
        $source = new Source(
            null,
            false,
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            [
                'functions' => [],
                'methods'   => [],
            ],
            false,
            false,
            false,
        );

        $this->assertFalse($source->ignoreSuppressionOfPhpWarnings());
    }

    public function testIgnoringOfSuppressedPhpWarningsMayBeEnabled(): void
    {
        $source = new Source(
            null,
            false,
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            true,
            [
                'functions' => [],
                'methods'   => [],
            ],
            false,
            false,
            false,
        );

        $this->assertTrue($source->ignoreSuppressionOfPhpWarnings());
    }

    public function testMayBeEmpty(): void
    {
        $source = new Source(
            null,
            false,
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            [
                'functions' => [],
                'methods'   => [],
            ],
            false,
            false,
            false,
        );

        $this->assertFalse($source->notEmpty());
    }

    public function testMayNotBeEmpty(): void
    {
        $source = new Source(
            null,
            false,
            FilterDirectoryCollection::fromArray(
                [
                    new FilterDirectory(
                        'path',
                        'prefix',
                        'suffix',
                    ),
                ],
            ),
            FileCollection::fromArray([]),
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            [
                'functions' => [],
                'methods'   => [],
            ],
            false,
            false,
            false,
        );

        $this->assertTrue($source->notEmpty());
    }
}
