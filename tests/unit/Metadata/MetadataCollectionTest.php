<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\Version\ComparisonRequirement;
use PHPUnit\Util\VersionComparisonOperator;

#[CoversClass(MetadataCollection::class)]
#[CoversClass(MetadataCollectionIterator::class)]
#[UsesClass(After::class)]
#[UsesClass(AfterClass::class)]
#[UsesClass(BackupGlobals::class)]
#[UsesClass(BackupStaticProperties::class)]
#[UsesClass(Before::class)]
#[UsesClass(BeforeClass::class)]
#[UsesClass(Covers::class)]
#[UsesClass(\PHPUnit\Metadata\CoversClass::class)]
#[UsesClass(CoversDefaultClass::class)]
#[UsesClass(CoversFunction::class)]
#[UsesClass(CoversNothing::class)]
#[UsesClass(DataProvider::class)]
#[UsesClass(DependsOnClass::class)]
#[UsesClass(DependsOnMethod::class)]
#[UsesClass(DoesNotPerformAssertions::class)]
#[UsesClass(Group::class)]
#[UsesClass(Metadata::class)]
#[UsesClass(PostCondition::class)]
#[UsesClass(PreCondition::class)]
#[UsesClass(PreserveGlobalState::class)]
#[UsesClass(RequiresFunction::class)]
#[UsesClass(RequiresMethod::class)]
#[UsesClass(RequiresOperatingSystem::class)]
#[UsesClass(RequiresOperatingSystemFamily::class)]
#[UsesClass(RequiresPhp::class)]
#[UsesClass(RequiresPhpExtension::class)]
#[UsesClass(RequiresPhpunit::class)]
#[UsesClass(RequiresSetting::class)]
#[UsesClass(RunClassInSeparateProcess::class)]
#[UsesClass(RunInSeparateProcess::class)]
#[UsesClass(RunTestsInSeparateProcesses::class)]
#[UsesClass(Test::class)]
#[UsesClass(TestDox::class)]
#[UsesClass(TestWith::class)]
#[UsesClass(Uses::class)]
#[UsesClass(\PHPUnit\Metadata\UsesClass::class)]
#[UsesClass(UsesDefaultClass::class)]
#[UsesClass(UsesFunction::class)]
#[Small]
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
        $metadata = Metadata::test();

        $collection = MetadataCollection::fromArray([$metadata]);

        $this->assertContains($metadata, $collection);
    }

    public function testIsCountable(): void
    {
        $metadata = Metadata::test();

        $collection = MetadataCollection::fromArray([$metadata]);

        $this->assertCount(1, $collection);
        $this->assertFalse($collection->isEmpty());
        $this->assertTrue($collection->isNotEmpty());
    }

    public function testIsIterable(): void
    {
        $metadata = Metadata::test();

        foreach (MetadataCollection::fromArray([$metadata]) as $key => $value) {
            $this->assertSame(0, $key);
            $this->assertSame($metadata, $value);
        }
    }

    public function testCanBeMerged(): void
    {
        $a = MetadataCollection::fromArray([Metadata::before()]);
        $b = MetadataCollection::fromArray([Metadata::after()]);
        $c = $a->mergeWith($b);

        $this->assertCount(2, $c);
        $this->assertTrue($c->asArray()[0]->isBefore());
        $this->assertTrue($c->asArray()[1]->isAfter());
    }

    public function test_Can_be_filtered_for_class_level_metadata(): void
    {
        $collection = MetadataCollection::fromArray(
            [
                Metadata::coversOnClass(''),
                Metadata::coversOnMethod(''),
            ]
        );

        $this->assertCount(2, $collection);

        $this->assertCount(1, $collection->isClassLevel());
        $this->assertTrue($collection->isClassLevel()->asArray()[0]->isClassLevel());

        $this->assertCount(1, $collection->isMethodLevel());
        $this->assertTrue($collection->isMethodLevel()->asArray()[0]->isMethodLevel());
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

    public function test_Can_be_filtered_for_Covers(): void
    {
        $collection = $this->collectionWithOneOfEach()->isCovers();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isCovers());
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

        $this->assertCount(2, $collection);
        $this->assertTrue($collection->asArray()[0]->isDependsOnClass());
        $this->assertTrue($collection->asArray()[1]->isDependsOnMethod());
    }

    public function test_Can_be_filtered_for_DependsOnClass(): void
    {
        $collection = $this->collectionWithOneOfEach()->isDependsOnClass();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isDependsOnClass());
    }

    public function test_Can_be_filtered_for_DependsOnMethod(): void
    {
        $collection = $this->collectionWithOneOfEach()->isDependsOnMethod();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isDependsOnMethod());
    }

    public function test_Can_be_filtered_for_DoesNotPerformAssertions(): void
    {
        $collection = $this->collectionWithOneOfEach()->isDoesNotPerformAssertions();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isDoesNotPerformAssertions());
    }

    public function test_Can_be_filtered_for_ExcludeGlobalVariableFromBackup(): void
    {
        $collection = $this->collectionWithOneOfEach()->isExcludeGlobalVariableFromBackup();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isExcludeGlobalVariableFromBackup());
    }

    public function test_Can_be_filtered_for_ExcludeStaticPropertyFromBackup(): void
    {
        $collection = $this->collectionWithOneOfEach()->isExcludeStaticPropertyFromBackup();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isExcludeStaticPropertyFromBackup());
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

    public function test_Can_be_filtered_for_RequiresMethod(): void
    {
        $collection = $this->collectionWithOneOfEach()->isRequiresMethod();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isRequiresMethod());
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

    public function test_Can_be_filtered_for_RequiresSetting(): void
    {
        $collection = $this->collectionWithOneOfEach()->isRequiresSetting();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isRequiresSetting());
    }

    public function test_Can_be_filtered_for_RunClassInSeparateProcess(): void
    {
        $collection = $this->collectionWithOneOfEach()->isRunClassInSeparateProcess();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isRunClassInSeparateProcess());
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

    public function test_Can_be_filtered_for_Uses(): void
    {
        $collection = $this->collectionWithOneOfEach()->isUses();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->asArray()[0]->isUses());
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

    private function collectionWithOneOfEach(): MetadataCollection
    {
        return MetadataCollection::fromArray(
            [
                Metadata::afterClass(),
                Metadata::after(),
                Metadata::backupGlobalsOnClass(true),
                Metadata::backupStaticPropertiesOnClass(true),
                Metadata::beforeClass(),
                Metadata::before(),
                Metadata::coversOnClass(''),
                Metadata::coversClass(''),
                Metadata::coversDefaultClass(''),
                Metadata::coversFunction(''),
                Metadata::coversNothingOnClass(),
                Metadata::dataProvider('', ''),
                Metadata::dependsOnClass('', false, false),
                Metadata::dependsOnMethod('', '', false, false),
                Metadata::doesNotPerformAssertionsOnClass(),
                Metadata::excludeGlobalVariableFromBackupOnClass(''),
                Metadata::excludeStaticPropertyFromBackupOnClass('', ''),
                Metadata::groupOnClass(''),
                Metadata::postCondition(),
                Metadata::preCondition(),
                Metadata::preserveGlobalStateOnClass(true),
                Metadata::requiresMethodOnClass('', ''),
                Metadata::requiresFunctionOnClass(''),
                Metadata::requiresOperatingSystemFamilyOnClass(''),
                Metadata::requiresOperatingSystemOnClass(''),
                Metadata::requiresPhpExtensionOnClass('', null),
                Metadata::requiresPhpOnClass(
                    new ComparisonRequirement(
                        '8.0.0',
                        new VersionComparisonOperator('>=')
                    )
                ),
                Metadata::requiresPhpunitOnClass(
                    new ComparisonRequirement(
                        '10.0.0',
                        new VersionComparisonOperator('>=')
                    )
                ),
                Metadata::requiresSettingOnClass('foo', 'bar'),
                Metadata::runClassInSeparateProcess(),
                Metadata::runInSeparateProcess(),
                Metadata::runTestsInSeparateProcesses(),
                Metadata::testDoxOnClass(''),
                Metadata::test(),
                Metadata::testWith([]),
                Metadata::usesOnClass(''),
                Metadata::usesClass(''),
                Metadata::usesDefaultClass(''),
                Metadata::usesFunction(''),
            ]
        );
    }
}
