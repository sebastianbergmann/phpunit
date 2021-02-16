<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\Metadata;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 * @psalm-immutable
 */
abstract class Metadata
{
    /**
     * @psalm-assert-if-true After $this
     */
    public function isAfter(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true AfterClass $this
     */
    public function isAfterClass(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true BackupGlobals $this
     */
    public function isBackupGlobals(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true BackupStaticProperties $this
     */
    public function isBackupStaticProperties(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true BeforeClass $this
     */
    public function isBeforeClass(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true Before $this
     */
    public function isBefore(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true CodeCoverageIgnore $this
     */
    public function isCodeCoverageIgnore(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true Covers $this
     */
    public function isCovers(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true CoversClass $this
     */
    public function isCoversClass(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true CoversDefaultClass $this
     */
    public function isCoversDefaultClass(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true CoversMethod $this
     */
    public function isCoversMethod(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true CoversFunction $this
     */
    public function isCoversFunction(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true CoversNothing $this
     */
    public function isCoversNothing(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true DataProvider $this
     */
    public function isDataProvider(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true Depends $this
     */
    public function isDepends(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true DoesNotPerformAssertions $this
     */
    public function isDoesNotPerformAssertions(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true Group $this
     */
    public function isGroup(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true RunClassInSeparateProcess $this
     */
    public function isRunClassInSeparateProcess(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true RunInSeparateProcess $this
     */
    public function isRunInSeparateProcess(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true RunTestsInSeparateProcesses $this
     */
    public function isRunTestsInSeparateProcesses(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true Test $this
     */
    public function isTest(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true PreCondition $this
     */
    public function isPreCondition(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true PostCondition $this
     */
    public function isPostCondition(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true PreserveGlobalState $this
     */
    public function isPreserveGlobalState(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true RequiresFunction $this
     */
    public function isRequiresFunction(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true RequiresOperatingSystem $this
     */
    public function isRequiresOperatingSystem(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true RequiresOperatingSystemFamily $this
     */
    public function isRequiresOperatingSystemFamily(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true RequiresPhp $this
     */
    public function isRequiresPhp(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true RequiresPhpExtension $this
     */
    public function isRequiresPhpExtension(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true RequiresPhpunit $this
     */
    public function isRequiresPhpunit(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true TestDox $this
     */
    public function isTestDox(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true TestWith $this
     */
    public function isTestWith(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true Todo $this
     */
    public function isTodo(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true Uses $this
     */
    public function isUses(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true UsesClass $this
     */
    public function isUsesClass(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true UsesDefaultClass $this
     */
    public function isUsesDefaultClass(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true UsesMethod $this
     */
    public function isUsesMethod(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true UsesFunction $this
     */
    public function isUsesFunction(): bool
    {
        return false;
    }
}
