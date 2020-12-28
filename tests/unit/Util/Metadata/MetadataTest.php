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
 * @covers \PHPUnit\Util\Metadata\CoversClass
 * @covers \PHPUnit\Util\Metadata\CoversMethod
 * @covers \PHPUnit\Util\Metadata\CoversNothing
 * @covers \PHPUnit\Util\Metadata\DoesNotPerformAssertions
 * @covers \PHPUnit\Util\Metadata\Group
 * @covers \PHPUnit\Util\Metadata\Metadata
 * @covers \PHPUnit\Util\Metadata\PostCondition
 * @covers \PHPUnit\Util\Metadata\PreCondition
 * @covers \PHPUnit\Util\Metadata\PreserveGlobalState
 * @covers \PHPUnit\Util\Metadata\RunInSeparateProcess
 * @covers \PHPUnit\Util\Metadata\RunTestsInSeparateProcesses
 * @covers \PHPUnit\Util\Metadata\Test
 * @covers \PHPUnit\Util\Metadata\UsesClass
 * @covers \PHPUnit\Util\Metadata\UsesMethod
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
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesMethod());
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
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesMethod());
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
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesMethod());

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
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesMethod());

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
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesMethod());
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
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesMethod());
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
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesMethod());
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
        $this->assertTrue($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesMethod());

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
        $this->assertFalse($metadata->isCoversClass());
        $this->assertTrue($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesMethod());

        $this->assertSame(self::class, $metadata->className());
        $this->assertSame(__METHOD__, $metadata->methodName());
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
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertTrue($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesMethod());
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
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertTrue($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesMethod());
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
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertTrue($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesMethod());

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
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertTrue($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesMethod());
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
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertTrue($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesMethod());
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
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertTrue($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesMethod());
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
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertTrue($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesMethod());
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
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertTrue($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesMethod());
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
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertTrue($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesMethod());

        $this->assertFalse($metadata->enabled());
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
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertTrue($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesMethod());

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
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertTrue($metadata->isUsesMethod());

        $this->assertSame(self::class, $metadata->className());
        $this->assertSame(__METHOD__, $metadata->methodName());
    }
}
