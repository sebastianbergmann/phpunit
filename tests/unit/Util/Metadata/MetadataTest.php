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

use PHPUnit\Framework\TestCase;

/**
 * @covers \PHPUnit\Util\Metadata\After
 * @covers \PHPUnit\Util\Metadata\AfterClass
 * @covers \PHPUnit\Util\Metadata\BackupGlobals
 * @covers \PHPUnit\Util\Metadata\BackupStaticProperties
 * @covers \PHPUnit\Util\Metadata\Before
 * @covers \PHPUnit\Util\Metadata\BeforeClass
 * @covers \PHPUnit\Util\Metadata\CodeCoverageIgnore
 * @covers \PHPUnit\Util\Metadata\Covers
 * @covers \PHPUnit\Util\Metadata\CoversClass
 * @covers \PHPUnit\Util\Metadata\CoversDefaultClass
 * @covers \PHPUnit\Util\Metadata\CoversFunction
 * @covers \PHPUnit\Util\Metadata\CoversMethod
 * @covers \PHPUnit\Util\Metadata\CoversNothing
 * @covers \PHPUnit\Util\Metadata\DataProvider
 * @covers \PHPUnit\Util\Metadata\Depends
 * @covers \PHPUnit\Util\Metadata\DoesNotPerformAssertions
 * @covers \PHPUnit\Util\Metadata\Group
 * @covers \PHPUnit\Util\Metadata\Metadata
 * @covers \PHPUnit\Util\Metadata\PostCondition
 * @covers \PHPUnit\Util\Metadata\PreCondition
 * @covers \PHPUnit\Util\Metadata\PreserveGlobalState
 * @covers \PHPUnit\Util\Metadata\RequiresFunction
 * @covers \PHPUnit\Util\Metadata\RequiresOperatingSystem
 * @covers \PHPUnit\Util\Metadata\RequiresOperatingSystemFamily
 * @covers \PHPUnit\Util\Metadata\RequiresPhp
 * @covers \PHPUnit\Util\Metadata\RequiresPhpExtension
 * @covers \PHPUnit\Util\Metadata\RequiresPhpunit
 * @covers \PHPUnit\Util\Metadata\RunInSeparateProcess
 * @covers \PHPUnit\Util\Metadata\RunTestsInSeparateProcesses
 * @covers \PHPUnit\Util\Metadata\Test
 * @covers \PHPUnit\Util\Metadata\TestDox
 * @covers \PHPUnit\Util\Metadata\TestWith
 * @covers \PHPUnit\Util\Metadata\Uses
 * @covers \PHPUnit\Util\Metadata\UsesClass
 * @covers \PHPUnit\Util\Metadata\UsesDefaultClass
 * @covers \PHPUnit\Util\Metadata\UsesFunction
 * @covers \PHPUnit\Util\Metadata\UsesMethod
 *
 * @small
 */
final class MetadataTest extends TestCase
{
    public function testCanBeAfter(): void
    {
        $metadata = new After;

        $this->assertTrue($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCodeCoverageIgnore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDepends());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isUsesFunction());
    }

    public function testCanBeAfterClass(): void
    {
        $metadata = new AfterClass;

        $this->assertFalse($metadata->isAfter());
        $this->assertTrue($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCodeCoverageIgnore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDepends());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isUsesFunction());
    }

    public function testCanBeBackupGlobals(): void
    {
        $metadata = new BackupGlobals(false);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertTrue($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCodeCoverageIgnore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDepends());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isUsesFunction());

        $this->assertFalse($metadata->enabled());
    }

    public function testCanBeBackupStaticProperties(): void
    {
        $metadata = new BackupStaticProperties(false);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertTrue($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCodeCoverageIgnore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDepends());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isUsesFunction());

        $this->assertFalse($metadata->enabled());
    }

    public function testCanBeBeforeClass(): void
    {
        $metadata = new BeforeClass;

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertTrue($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCodeCoverageIgnore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDepends());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isUsesFunction());
    }

    public function testCanBeBefore(): void
    {
        $metadata = new Before;

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertTrue($metadata->isBefore());
        $this->assertFalse($metadata->isCodeCoverageIgnore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDepends());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isUsesFunction());
    }

    public function testCanBeCodeCoverageIgnore(): void
    {
        $metadata = new CodeCoverageIgnore;

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertTrue($metadata->isCodeCoverageIgnore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDepends());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isUsesFunction());
    }

    public function testCanBeCovers(): void
    {
        $metadata = new Covers(self::class);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCodeCoverageIgnore());
        $this->assertTrue($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDepends());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isUsesFunction());

        $this->assertSame(self::class, $metadata->target());
    }

    public function testCanBeCoversClass(): void
    {
        $metadata = new CoversClass(self::class);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCodeCoverageIgnore());
        $this->assertFalse($metadata->isCovers());
        $this->assertTrue($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDepends());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isUsesFunction());

        $this->assertSame(self::class, $metadata->className());
        $this->assertSame(self::class, $metadata->asStringForCodeUnitMapper());
    }

    public function testCanBeCoversDefaultClass(): void
    {
        $metadata = new CoversDefaultClass(self::class);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCodeCoverageIgnore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertTrue($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDepends());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isUsesFunction());

        $this->assertSame(self::class, $metadata->className());
    }

    public function testCanBeCoversMethod(): void
    {
        $metadata = new CoversMethod(self::class, __METHOD__);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCodeCoverageIgnore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertTrue($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDepends());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isUsesFunction());

        $this->assertSame(self::class, $metadata->className());
        $this->assertSame(__METHOD__, $metadata->methodName());
        $this->assertSame(self::class . '::' . __METHOD__, $metadata->asStringForCodeUnitMapper());
    }

    public function testCanBeCoversFunction(): void
    {
        $metadata = new CoversFunction('f');

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCodeCoverageIgnore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertTrue($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDepends());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isUsesFunction());

        $this->assertSame('f', $metadata->functionName());
        $this->assertSame('::f', $metadata->asStringForCodeUnitMapper());
    }

    public function testCanBeCoversNothing(): void
    {
        $metadata = new CoversNothing;

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCodeCoverageIgnore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertTrue($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDepends());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isUsesFunction());
    }

    public function testCanBeDataProvider(): void
    {
        $metadata = new DataProvider(self::class, 'method');

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCodeCoverageIgnore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertTrue($metadata->isDataProvider());
        $this->assertFalse($metadata->isDepends());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isUsesFunction());

        $this->assertSame(self::class, $metadata->className());
        $this->assertSame('method', $metadata->methodName());
    }

    public function testCanBeDepends(): void
    {
        $metadata = new Depends(self::class, 'method', false, false);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCodeCoverageIgnore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertTrue($metadata->isDepends());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isUsesFunction());

        $this->assertSame(self::class, $metadata->className());
        $this->assertSame('method', $metadata->methodName());
        $this->assertFalse($metadata->deepClone());
        $this->assertFalse($metadata->shallowClone());
    }

    public function testCanBeDoesNotPerformAssertions(): void
    {
        $metadata = new DoesNotPerformAssertions;

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCodeCoverageIgnore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDepends());
        $this->assertTrue($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isUsesFunction());
    }

    public function testCanBeGroup(): void
    {
        $metadata = new Group('name');

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCodeCoverageIgnore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDepends());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertTrue($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isUsesFunction());

        $this->assertSame('name', $metadata->groupName());
    }

    public function testCanBeRunTestsInSeparateProcesses(): void
    {
        $metadata = new RunTestsInSeparateProcesses;

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCodeCoverageIgnore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDepends());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertTrue($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isUsesFunction());
    }

    public function testCanBeRunInSeparateProcess(): void
    {
        $metadata = new RunInSeparateProcess;

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCodeCoverageIgnore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDepends());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertTrue($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isUsesFunction());
    }

    public function testCanBeTest(): void
    {
        $metadata = new Test;

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCodeCoverageIgnore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDepends());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertTrue($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isUsesFunction());
    }

    public function testCanBePreCondition(): void
    {
        $metadata = new PreCondition;

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCodeCoverageIgnore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDepends());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertTrue($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isUsesFunction());
    }

    public function testCanBePostCondition(): void
    {
        $metadata = new PostCondition;

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCodeCoverageIgnore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDepends());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertTrue($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isUsesFunction());
    }

    public function testCanBePreserveGlobalState(): void
    {
        $metadata = new PreserveGlobalState(false);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCodeCoverageIgnore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDepends());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertTrue($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isUsesFunction());

        $this->assertFalse($metadata->enabled());
    }

    public function testCanBeRequiresFunction(): void
    {
        $metadata = new RequiresFunction('f');

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCodeCoverageIgnore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDepends());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertTrue($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isUsesFunction());

        $this->assertSame('f', $metadata->functionName());
    }

    public function testCanBeRequiresOperatingSystem(): void
    {
        $metadata = new RequiresOperatingSystem('Linux');

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCodeCoverageIgnore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDepends());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertTrue($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isUsesFunction());

        $this->assertSame('Linux', $metadata->regularExpression());
    }

    public function testCanBeRequiresOperatingSystemFamily(): void
    {
        $metadata = new RequiresOperatingSystemFamily('Linux');

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCodeCoverageIgnore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDepends());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertTrue($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isUsesFunction());

        $this->assertSame('Linux', $metadata->operatingSystemFamily());
    }

    public function testCanBeRequiresPhp(): void
    {
        $metadata = new RequiresPhp('8.0.0');

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCodeCoverageIgnore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDepends());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertTrue($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isUsesFunction());

        $this->assertSame('8.0.0', $metadata->versionRequirement());
    }

    public function testCanBeRequiresPhpExtension(): void
    {
        $metadata = new RequiresPhpExtension('test', '8.0.0');

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCodeCoverageIgnore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDepends());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertTrue($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isUsesFunction());

        $this->assertSame('test', $metadata->extension());
        $this->assertSame('8.0.0', $metadata->versionRequirement());
        $this->assertTrue($metadata->hasVersionRequirement());
    }

    public function testCanBeRequiresPhpunit(): void
    {
        $metadata = new RequiresPhpunit('10.0.0');

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCodeCoverageIgnore());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDepends());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertTrue($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isUsesFunction());

        $this->assertSame('10.0.0', $metadata->versionRequirement());
    }

    public function testCanBeTestDox(): void
    {
        $metadata = new TestDox('text');

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCodeCoverageIgnore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDepends());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertTrue($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isUsesFunction());

        $this->assertSame('text', $metadata->text());
    }

    public function testCanBeTestWith(): void
    {
        $metadata = new TestWith(['a', 'b']);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCodeCoverageIgnore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDepends());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isTestDox());
        $this->assertTrue($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isUsesFunction());

        $this->assertSame(['a', 'b'], $metadata->data());
    }

    public function testCanBeUses(): void
    {
        $metadata = new Uses(self::class);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCodeCoverageIgnore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDepends());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertTrue($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isUsesFunction());

        $this->assertSame(self::class, $metadata->target());
    }

    public function testCanBeUsesClass(): void
    {
        $metadata = new UsesClass(self::class);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCodeCoverageIgnore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDepends());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertTrue($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isUsesFunction());

        $this->assertSame(self::class, $metadata->className());
        $this->assertSame(self::class, $metadata->asStringForCodeUnitMapper());
    }

    public function testCanBeUsesDefaultClass(): void
    {
        $metadata = new UsesDefaultClass(self::class);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCodeCoverageIgnore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDepends());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertTrue($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isUsesFunction());

        $this->assertSame(self::class, $metadata->className());
    }

    public function testCanBeUsesMethod(): void
    {
        $metadata = new UsesMethod(self::class, __METHOD__);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCodeCoverageIgnore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDepends());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertTrue($metadata->isUsesMethod());
        $this->assertFalse($metadata->isUsesFunction());

        $this->assertSame(self::class, $metadata->className());
        $this->assertSame(__METHOD__, $metadata->methodName());
        $this->assertSame(self::class . '::' . __METHOD__, $metadata->asStringForCodeUnitMapper());
    }

    public function testCanBeUsesFunction(): void
    {
        $metadata = new UsesFunction('f');

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCodeCoverageIgnore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDepends());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertTrue($metadata->isUsesFunction());

        $this->assertSame('f', $metadata->functionName());
        $this->assertSame('::f', $metadata->asStringForCodeUnitMapper());
    }
}
