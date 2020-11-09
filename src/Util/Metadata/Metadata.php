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
    public function isAfter(): bool
    {
        return false;
    }

    public function isAfterClass(): bool
    {
        return false;
    }

    public function isBackupGlobals(): bool
    {
        return false;
    }

    public function isBackupStaticProperties(): bool
    {
        return false;
    }

    public function isBeforeClass(): bool
    {
        return false;
    }

    public function isBefore(): bool
    {
        return false;
    }

    public function isCodeCoverageIgnore(): bool
    {
        return false;
    }

    public function isCoversNothing(): bool
    {
        return false;
    }

    public function isDoesNotPerformAssertions(): bool
    {
        return false;
    }

    public function isGroup(): bool
    {
        return false;
    }

    public function isRunTestsInSeparateProcesses(): bool
    {
        return false;
    }

    public function isRunInSeparateProcess(): bool
    {
        return false;
    }

    public function isTest(): bool
    {
        return false;
    }

    public function isPreCondition(): bool
    {
        return false;
    }

    public function isPostCondition(): bool
    {
        return false;
    }

    public function isPreserveGlobalState(): bool
    {
        return false;
    }
}
