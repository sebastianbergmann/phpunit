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
 * @covers \PHPUnit\Util\Metadata\MetadataCollection
 * @covers \PHPUnit\Util\Metadata\MetadataCollectionIterator
 *
 * @uses \PHPUnit\Util\Metadata\After
 * @uses \PHPUnit\Util\Metadata\AfterClass
 * @uses \PHPUnit\Util\Metadata\BackupGlobals
 * @uses \PHPUnit\Util\Metadata\BackupStaticProperties
 * @uses \PHPUnit\Util\Metadata\Before
 * @uses \PHPUnit\Util\Metadata\BeforeClass
 * @uses \PHPUnit\Util\Metadata\CodeCoverageIgnore
 * @uses \PHPUnit\Util\Metadata\CoversClass
 * @uses \PHPUnit\Util\Metadata\CoversDefaultClass
 * @uses \PHPUnit\Util\Metadata\CoversFunction
 * @uses \PHPUnit\Util\Metadata\CoversMethod
 * @uses \PHPUnit\Util\Metadata\CoversNothing
 * @uses \PHPUnit\Util\Metadata\DataProvider
 * @uses \PHPUnit\Util\Metadata\Depends
 * @uses \PHPUnit\Util\Metadata\DoesNotPerformAssertions
 * @uses \PHPUnit\Util\Metadata\Group
 * @uses \PHPUnit\Util\Metadata\Metadata
 * @uses \PHPUnit\Util\Metadata\PostCondition
 * @uses \PHPUnit\Util\Metadata\PreCondition
 * @uses \PHPUnit\Util\Metadata\PreserveGlobalState
 * @uses \PHPUnit\Util\Metadata\RequiresFunction
 * @uses \PHPUnit\Util\Metadata\RequiresOperatingSystem
 * @uses \PHPUnit\Util\Metadata\RequiresOperatingSystemFamily
 * @uses \PHPUnit\Util\Metadata\RequiresPhp
 * @uses \PHPUnit\Util\Metadata\RequiresPhpExtension
 * @uses \PHPUnit\Util\Metadata\RequiresPhpunit
 * @uses \PHPUnit\Util\Metadata\RunInSeparateProcess
 * @uses \PHPUnit\Util\Metadata\RunTestsInSeparateProcesses
 * @uses \PHPUnit\Util\Metadata\Test
 * @uses \PHPUnit\Util\Metadata\TestDox
 * @uses \PHPUnit\Util\Metadata\TestWith
 * @uses \PHPUnit\Util\Metadata\UsesClass
 * @uses \PHPUnit\Util\Metadata\UsesDefaultClass
 * @uses \PHPUnit\Util\Metadata\UsesFunction
 * @uses \PHPUnit\Util\Metadata\UsesMethod
 *
 * @small
 */
final class MetadataCollectionTest extends TestCase
{
    public function testCanBeEmpty(): void
    {
        $collection = MetadataCollection::fromArray([]);

        $this->assertCount(0, $collection);
        $this->assertTrue($collection->isEmpty());
        $this->assertFalse($collection->isNotEmpty());
    }

    public function testCanBeCreatedFromArray(): void
    {
        $metadata = new Test;

        $collection = MetadataCollection::fromArray([$metadata]);

        $this->assertContains($metadata, $collection);
    }

    public function testIsCountable(): void
    {
        $metadata = new Test;

        $collection = MetadataCollection::fromArray([$metadata]);

        $this->assertCount(1, $collection);
        $this->assertFalse($collection->isEmpty());
        $this->assertTrue($collection->isNotEmpty());
    }

    public function testIsIterable(): void
    {
        $metadata = new Test;

        foreach (MetadataCollection::fromArray([$metadata]) as $key => $value) {
            $this->assertSame(0, $key);
            $this->assertSame($metadata, $value);
        }
    }

    public function testCanBeMerged(): void
    {
        $a = MetadataCollection::fromArray([new Before]);
        $b = MetadataCollection::fromArray([new After]);
        $c = $a->mergeWith($b);

        $this->assertCount(2, $c);
        $this->assertTrue($c->asArray()[0]->isBefore());
        $this->assertTrue($c->asArray()[1]->isAfter());
    }

    public function test_Can_be_filtered_for_AfterClass(): void
    {
        $collection = $this->collectionWithOneOfEach()->isAfterClass();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isAfterClass());
    }

    public function test_Can_be_filtered_for_After(): void
    {
        $collection = $this->collectionWithOneOfEach()->isAfter();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isAfter());
    }

    public function test_Can_be_filtered_for_BackupGlobals(): void
    {
        $collection = $this->collectionWithOneOfEach()->isBackupGlobals();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isBackupGlobals());
    }

    public function test_Can_be_filtered_for_BackupStaticProperties(): void
    {
        $collection = $this->collectionWithOneOfEach()->isBackupStaticProperties();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isBackupStaticProperties());
    }

    public function test_Can_be_filtered_for_BeforeClass(): void
    {
        $collection = $this->collectionWithOneOfEach()->isBeforeClass();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isBeforeClass());
    }

    public function test_Can_be_filtered_for_Before(): void
    {
        $collection = $this->collectionWithOneOfEach()->isBefore();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isBefore());
    }

    public function test_Can_be_filtered_for_CodeCoverageIgnore(): void
    {
        $collection = $this->collectionWithOneOfEach()->isCodeCoverageIgnore();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isCodeCoverageIgnore());
    }

    public function test_Can_be_filtered_for_CoversClass(): void
    {
        $collection = $this->collectionWithOneOfEach()->isCoversClass();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isCoversClass());
    }

    public function test_Can_be_filtered_for_CoversDefaultClass(): void
    {
        $collection = $this->collectionWithOneOfEach()->isCoversDefaultClass();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isCoversDefaultClass());
    }

    public function test_Can_be_filtered_for_CoversFunction(): void
    {
        $collection = $this->collectionWithOneOfEach()->isCoversFunction();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isCoversFunction());
    }

    public function test_Can_be_filtered_for_CoversMethod(): void
    {
        $collection = $this->collectionWithOneOfEach()->isCoversMethod();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isCoversMethod());
    }

    public function test_Can_be_filtered_for_CoversNothing(): void
    {
        $collection = $this->collectionWithOneOfEach()->isCoversNothing();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isCoversNothing());
    }

    public function test_Can_be_filtered_for_DataProvider(): void
    {
        $collection = $this->collectionWithOneOfEach()->isDataProvider();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isDataProvider());
    }

    public function test_Can_be_filtered_for_Depends(): void
    {
        $collection = $this->collectionWithOneOfEach()->isDepends();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isDepends());
    }

    public function test_Can_be_filtered_for_DoesNotPerformAssertions(): void
    {
        $collection = $this->collectionWithOneOfEach()->isDoesNotPerformAssertions();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isDoesNotPerformAssertions());
    }

    public function test_Can_be_filtered_for_Group(): void
    {
        $collection = $this->collectionWithOneOfEach()->isGroup();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isGroup());
    }

    public function test_Can_be_filtered_for_PostCondition(): void
    {
        $collection = $this->collectionWithOneOfEach()->isPostCondition();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isPostCondition());
    }

    public function test_Can_be_filtered_for_PreCondition(): void
    {
        $collection = $this->collectionWithOneOfEach()->isPreCondition();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isPreCondition());
    }

    public function test_Can_be_filtered_for_PreserveGlobalState(): void
    {
        $collection = $this->collectionWithOneOfEach()->isPreserveGlobalState();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isPreserveGlobalState());
    }

    public function test_Can_be_filtered_for_RequiresFunction(): void
    {
        $collection = $this->collectionWithOneOfEach()->isRequiresFunction();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isRequiresFunction());
    }

    public function test_Can_be_filtered_for_RequiresOperatingSystemFamily(): void
    {
        $collection = $this->collectionWithOneOfEach()->isRequiresOperatingSystemFamily();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isRequiresOperatingSystemFamily());
    }

    public function test_Can_be_filtered_for_RequiresOperatingSystem(): void
    {
        $collection = $this->collectionWithOneOfEach()->isRequiresOperatingSystem();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isRequiresOperatingSystem());
    }

    public function test_Can_be_filtered_for_RequiresPhpExtension(): void
    {
        $collection = $this->collectionWithOneOfEach()->isRequiresPhpExtension();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isRequiresPhpExtension());
    }

    public function test_Can_be_filtered_for_RequiresPhp(): void
    {
        $collection = $this->collectionWithOneOfEach()->isRequiresPhp();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isRequiresPhp());
    }

    public function test_Can_be_filtered_for_RequiresPhpunit(): void
    {
        $collection = $this->collectionWithOneOfEach()->isRequiresPhpunit();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isRequiresPhpunit());
    }

    public function test_Can_be_filtered_for_RunInSeparateProcess(): void
    {
        $collection = $this->collectionWithOneOfEach()->isRunInSeparateProcess();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isRunInSeparateProcess());
    }

    public function test_Can_be_filtered_for_RunTestsInSeparateProcesses(): void
    {
        $collection = $this->collectionWithOneOfEach()->isRunTestsInSeparateProcesses();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isRunTestsInSeparateProcesses());
    }

    public function test_Can_be_filtered_for_TestDox(): void
    {
        $collection = $this->collectionWithOneOfEach()->isTestDox();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isTestDox());
    }

    public function test_Can_be_filtered_for_Test(): void
    {
        $collection = $this->collectionWithOneOfEach()->isTest();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isTest());
    }

    public function test_Can_be_filtered_for_TestWith(): void
    {
        $collection = $this->collectionWithOneOfEach()->isTestWith();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isTestWith());
    }

    public function test_Can_be_filtered_for_UsesClass(): void
    {
        $collection = $this->collectionWithOneOfEach()->isUsesClass();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isUsesClass());
    }

    public function test_Can_be_filtered_for_UsesDefaultClass(): void
    {
        $collection = $this->collectionWithOneOfEach()->isUsesDefaultClass();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isUsesDefaultClass());
    }

    public function test_Can_be_filtered_for_UsesFunction(): void
    {
        $collection = $this->collectionWithOneOfEach()->isUsesFunction();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isUsesFunction());
    }

    public function test_Can_be_filtered_for_UsesMethod(): void
    {
        $collection = $this->collectionWithOneOfEach()->isUsesMethod();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isUsesMethod());
    }

    private function collectionWithOneOfEach(): MetadataCollection
    {
        return MetadataCollection::fromArray(
            [
                new AfterClass,
                new After,
                new BackupGlobals(true),
                new BackupStaticProperties(true),
                new BeforeClass,
                new Before,
                new CodeCoverageIgnore,
                new CoversClass(''),
                new CoversDefaultClass(''),
                new CoversFunction(''),
                new CoversMethod('', ''),
                new CoversNothing,
                new DataProvider('', ''),
                new Depends('', '', false, false),
                new DoesNotPerformAssertions,
                new Group(''),
                new PostCondition,
                new PreCondition,
                new PreserveGlobalState(true),
                new RequiresFunction(''),
                new RequiresOperatingSystemFamily(''),
                new RequiresOperatingSystem(''),
                new RequiresPhpExtension('', ''),
                new RequiresPhp(''),
                new RequiresPhpunit(''),
                new RunInSeparateProcess,
                new RunTestsInSeparateProcesses,
                new TestDox(''),
                new Test,
                new TestWith([]),
                new UsesClass(''),
                new UsesDefaultClass(''),
                new UsesFunction(''),
                new UsesMethod('', ''),
            ]
        );
    }
}
