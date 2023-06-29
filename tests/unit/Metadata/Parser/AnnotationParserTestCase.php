<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata\Parser;

use function assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\RequiresPhp;
use PHPUnit\Metadata\RequiresPhpExtension;
use PHPUnit\Metadata\RequiresPhpunit;
use PHPUnit\Metadata\RequiresSetting;
use PHPUnit\Metadata\Version\ComparisonRequirement;
use PHPUnit\Metadata\Version\ConstraintRequirement;
use PHPUnit\TestFixture\Metadata\Annotation\AnotherTest;
use PHPUnit\TestFixture\Metadata\Annotation\BackupGlobalsTest;
use PHPUnit\TestFixture\Metadata\Annotation\BackupStaticPropertiesTest;
use PHPUnit\TestFixture\Metadata\Annotation\CoversTest;
use PHPUnit\TestFixture\Metadata\Annotation\DependencyTest;
use PHPUnit\TestFixture\Metadata\Annotation\DoesNotPerformAssertionsTest;
use PHPUnit\TestFixture\Metadata\Annotation\GroupTest;
use PHPUnit\TestFixture\Metadata\Annotation\LargeTest;
use PHPUnit\TestFixture\Metadata\Annotation\MediumTest;
use PHPUnit\TestFixture\Metadata\Annotation\PreserveGlobalStateTest;
use PHPUnit\TestFixture\Metadata\Annotation\ProcessIsolationTest;
use PHPUnit\TestFixture\Metadata\Annotation\RequiresFunctionTest;
use PHPUnit\TestFixture\Metadata\Annotation\RequiresOperatingSystemFamilyTest;
use PHPUnit\TestFixture\Metadata\Annotation\RequiresOperatingSystemTest;
use PHPUnit\TestFixture\Metadata\Annotation\RequiresPhp2Test;
use PHPUnit\TestFixture\Metadata\Annotation\RequiresPhpExtensionTest;
use PHPUnit\TestFixture\Metadata\Annotation\RequiresPhpTest;
use PHPUnit\TestFixture\Metadata\Annotation\RequiresPhpunit2Test;
use PHPUnit\TestFixture\Metadata\Annotation\RequiresPhpunitTest;
use PHPUnit\TestFixture\Metadata\Annotation\RequiresSettingTest;
use PHPUnit\TestFixture\Metadata\Annotation\SmallTest;
use PHPUnit\TestFixture\Metadata\Annotation\TestDoxTest;
use PHPUnit\TestFixture\Metadata\Annotation\UsesTest;

abstract class AnnotationParserTestCase extends TestCase
{
    public static function provideRequiresPhpTestMethods(): array
    {
        return [['testOne'], ['testTwo']];
    }

    public function test_Parses_backupGlobals_annotation_on_class(): void
    {
        $metadata = $this->parser()->forClass(BackupGlobalsTest::class)->isBackupGlobals();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBackupGlobals());
        $this->assertTrue($metadata->asArray()[0]->enabled());
    }

    public function test_Parses_backupStaticAttributes_annotation_on_class(): void
    {
        $metadata = $this->parser()->forClass(BackupStaticPropertiesTest::class)->isBackupStaticProperties();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBackupStaticProperties());
        $this->assertTrue($metadata->asArray()[0]->enabled());
    }

    public function test_Parses_covers_annotation_on_class(): void
    {
        $metadata = $this->parser()->forClass(CoversTest::class)->isCovers();

        $this->assertCount(2, $metadata);

        $this->assertTrue($metadata->asArray()[0]->isCovers());
        $this->assertSame('::\PHPUnit\TestFixture\Metadata\Annotation\f', $metadata->asArray()[0]->target());

        $this->assertTrue($metadata->asArray()[1]->isCovers());
        $this->assertSame('\PHPUnit\TestFixture\Metadata\Annotation\Example', $metadata->asArray()[1]->target());
    }

    public function test_Parses_coversDefaultClass_annotation_on_class(): void
    {
        $metadata = $this->parser()->forClass(CoversTest::class)->isCoversDefaultClass();

        $this->assertCount(1, $metadata);

        $this->assertTrue($metadata->asArray()[0]->isCoversDefaultClass());
        $this->assertSame('\PHPUnit\TestFixture\Metadata\Annotation\Example', $metadata->asArray()[0]->className());
    }

    public function test_Parses_coversNothing_annotation_on_class(): void
    {
        $metadata = $this->parser()->forClass(CoversTest::class)->isCoversNothing();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isCoversNothing());
    }

    public function test_Parses_doesNotPerformAssertions_annotation_on_class(): void
    {
        $metadata = $this->parser()->forClass(DoesNotPerformAssertionsTest::class)->isDoesNotPerformAssertions();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isDoesNotPerformAssertions());
    }

    public function test_Parses_group_annotation_on_class(): void
    {
        $metadata = $this->parser()->forClass(GroupTest::class)->isGroup();

        $this->assertCount(2, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('group', $metadata->asArray()[0]->groupName());
    }

    public function test_Parses_large_annotation_on_class(): void
    {
        $metadata = $this->parser()->forClass(LargeTest::class)->isGroup();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('large', $metadata->asArray()[0]->groupName());
    }

    public function test_Parses_medium_annotation_on_class(): void
    {
        $metadata = $this->parser()->forClass(MediumTest::class)->isGroup();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('medium', $metadata->asArray()[0]->groupName());
    }

    public function test_Parses_preserveGlobalState_annotation_on_class(): void
    {
        $metadata = $this->parser()->forClass(PreserveGlobalStateTest::class)->isPreserveGlobalState();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isPreserveGlobalState());
        $this->assertTrue($metadata->asArray()[0]->enabled());
    }

    public function test_Parses_requiresFunction_annotation_with_function_on_class(): void
    {
        $metadata = $this->parser()->forClass(RequiresFunctionTest::class)->isRequiresFunction();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresFunction());
        $this->assertSame('f', $metadata->asArray()[0]->functionName());
    }

    public function test_Parses_requiresFunction_annotation_with_method_on_class(): void
    {
        $metadata = $this->parser()->forClass(RequiresFunctionTest::class)->isRequiresMethod();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresMethod());
        $this->assertSame('SomeClass', $metadata->asArray()[0]->className());
        $this->assertSame('someMethod', $metadata->asArray()[0]->methodName());
    }

    public function test_Parses_requiresOperatingSystem_annotation_on_class(): void
    {
        $metadata = $this->parser()->forClass(RequiresOperatingSystemTest::class)->isRequiresOperatingSystem();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresOperatingSystem());
        $this->assertSame('Linux', $metadata->asArray()[0]->operatingSystem());
    }

    public function test_Parses_requiresOperatingSystemFamily_annotation_on_class(): void
    {
        $metadata = $this->parser()->forClass(RequiresOperatingSystemFamilyTest::class)->isRequiresOperatingSystemFamily();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresOperatingSystemFamily());
        $this->assertSame('Linux', $metadata->asArray()[0]->operatingSystemFamily());
    }

    public function test_Parses_requiresPhp_annotation_on_class_with_version_comparison(): void
    {
        $metadata = $this->parser()->forClass(RequiresPhpTest::class)->isRequiresPhp();

        $this->assertCount(1, $metadata);

        $requirement = $metadata->asArray()[0];

        assert($requirement instanceof RequiresPhp);

        $this->assertTrue($requirement->isRequiresPhp());

        $versionRequirement = $requirement->versionRequirement();

        assert($versionRequirement instanceof ComparisonRequirement);

        $this->assertSame('>= 8.0.0', $versionRequirement->asString());
    }

    public function test_Parses_requiresPhp_annotation_on_class_with_version_constraint(): void
    {
        $metadata = $this->parser()->forClass(RequiresPhp2Test::class)->isRequiresPhp();

        $this->assertCount(1, $metadata);

        $requirement = $metadata->asArray()[0];

        assert($requirement instanceof RequiresPhp);

        $this->assertTrue($requirement->isRequiresPhp());

        $versionRequirement = $requirement->versionRequirement();

        assert($versionRequirement instanceof ConstraintRequirement);

        $this->assertSame('^8.0', $versionRequirement->asString());
    }

    public function test_Parses_requiresPhpExtension_annotation_on_class(): void
    {
        $metadata = $this->parser()->forClass(RequiresPhpExtensionTest::class)->isRequiresPhpExtension();

        $this->assertCount(2, $metadata);

        $requirement = $metadata->asArray()[0];

        $this->assertTrue($requirement->isRequiresPhpExtension());
        $this->assertSame('bar', $requirement->extension());
        $this->assertFalse($requirement->hasVersionRequirement());

        $requirement = $metadata->asArray()[1];

        $this->assertTrue($requirement->isRequiresPhpExtension());
        $this->assertSame('foo', $requirement->extension());
        $this->assertTrue($requirement->hasVersionRequirement());

        $versionRequirement = $requirement->versionRequirement();

        assert($versionRequirement instanceof ComparisonRequirement);

        $this->assertSame('>= 1.0.0', $versionRequirement->asString());
    }

    public function test_Parses_requiresPhpunit_annotation_on_class_with_version_comparison(): void
    {
        $metadata = $this->parser()->forClass(RequiresPhpunitTest::class)->isRequiresPhpunit();

        $this->assertCount(1, $metadata);

        $requirement = $metadata->asArray()[0];

        assert($requirement instanceof RequiresPhpunit);

        $this->assertTrue($requirement->isRequiresPhpunit());

        $versionRequirement = $requirement->versionRequirement();

        assert($versionRequirement instanceof ComparisonRequirement);

        $this->assertSame('>= 10.0.0', $versionRequirement->asString());
    }

    public function test_Parses_requiresPhpunit_annotation_on_class_with_version_constraint(): void
    {
        $metadata = $this->parser()->forClass(RequiresPhpunit2Test::class)->isRequiresPhpunit();

        $this->assertCount(1, $metadata);

        $requirement = $metadata->asArray()[0];

        assert($requirement instanceof RequiresPhpunit);

        $this->assertTrue($requirement->isRequiresPhpunit());

        $versionRequirement = $requirement->versionRequirement();

        assert($versionRequirement instanceof ConstraintRequirement);

        $this->assertSame('^10.0', $versionRequirement->asString());
    }

    public function test_Parses_requiresSetting_annotation_on_class(): void
    {
        $metadata = $this->parser()->forClass(RequiresSettingTest::class)->isRequiresSetting();

        $this->assertCount(1, $metadata);

        $requirement = $metadata->asArray()[0];

        $this->assertTrue($requirement->isRequiresSetting());

        assert($requirement instanceof RequiresSetting);

        $this->assertSame('foo', $requirement->setting());
        $this->assertSame('bar', $requirement->value());
    }

    public function test_Parses_runClassInSeparateProcess_annotation_on_class(): void
    {
        $metadata = $this->parser()->forClass(ProcessIsolationTest::class)->isRunClassInSeparateProcess();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRunClassInSeparateProcess());
    }

    public function test_Parses_runTestsInSeparateProcesses_annotation_on_class(): void
    {
        $metadata = $this->parser()->forClass(ProcessIsolationTest::class)->isRunTestsInSeparateProcesses();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRunTestsInSeparateProcesses());
    }

    public function test_Parses_small_annotation_on_class(): void
    {
        $metadata = $this->parser()->forClass(SmallTest::class)->isGroup();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('small', $metadata->asArray()[0]->groupName());
    }

    public function test_Parses_testdox_annotation_on_class(): void
    {
        $metadata = $this->parser()->forClass(TestDoxTest::class)->isTestDox();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isTestDox());
        $this->assertSame('text', $metadata->asArray()[0]->text());
    }

    public function test_Parses_ticket_annotation_on_class(): void
    {
        $metadata = $this->parser()->forClass(GroupTest::class)->isGroup();

        $this->assertCount(2, $metadata);
        $this->assertTrue($metadata->asArray()[1]->isGroup());
        $this->assertSame('ticket', $metadata->asArray()[1]->groupName());
    }

    public function test_Parses_uses_annotation_on_class(): void
    {
        $metadata = $this->parser()->forClass(UsesTest::class)->isUses();

        $this->assertCount(2, $metadata);

        $this->assertTrue($metadata->asArray()[0]->isUses());
        $this->assertSame('::\PHPUnit\TestFixture\Metadata\Annotation\f', $metadata->asArray()[0]->target());

        $this->assertTrue($metadata->asArray()[1]->isUses());
        $this->assertSame('\PHPUnit\TestFixture\Metadata\Annotation\Example', $metadata->asArray()[1]->target());
    }

    public function test_Parses_usesDefaultClass_annotation_on_class(): void
    {
        $metadata = $this->parser()->forClass(UsesTest::class)->isUsesDefaultClass();

        $this->assertCount(1, $metadata);

        $this->assertTrue($metadata->asArray()[0]->isUsesDefaultClass());
        $this->assertSame('\PHPUnit\TestFixture\Metadata\Annotation\Example', $metadata->asArray()[0]->className());
    }

    public function test_Parses_after_annotation_on_method(): void
    {
        $metadata = $this->parser()->forMethod(SmallTest::class, 'afterTest')->isAfter();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isAfter());
    }

    public function test_Parses_afterClass_annotation_on_method(): void
    {
        $metadata = $this->parser()->forMethod(SmallTest::class, 'afterTests')->isAfterClass();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isAfterClass());
    }

    public function test_Parses_backupGlobals_annotation_on_method(): void
    {
        $metadata = $this->parser()->forMethod(BackupGlobalsTest::class, 'testOne')->isBackupGlobals();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBackupGlobals());
        $this->assertFalse($metadata->asArray()[0]->enabled());
    }

    public function test_Parses_backupStaticProperties_annotation_on_method(): void
    {
        $metadata = $this->parser()->forMethod(BackupStaticPropertiesTest::class, 'testOne')->isBackupStaticProperties();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBackupStaticProperties());
        $this->assertFalse($metadata->asArray()[0]->enabled());
    }

    public function test_Parses_before_annotation_on_method(): void
    {
        $metadata = $this->parser()->forMethod(SmallTest::class, 'beforeTest')->isBefore();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBefore());
    }

    public function test_Parses_beforeClass_annotation_on_method(): void
    {
        $metadata = $this->parser()->forMethod(SmallTest::class, 'beforeTests')->isBeforeClass();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBeforeClass());
    }

    public function test_Parses_covers_annotation_on_method(): void
    {
        $metadata = $this->parser()->forMethod(CoversTest::class, 'testTwo')->isCovers();

        $this->assertCount(1, $metadata);

        $this->assertTrue($metadata->asArray()[0]->isCovers());
        $this->assertSame('Foo::bar', $metadata->asArray()[0]->target());
    }

    public function test_Parses_coversNothing_annotation_on_method(): void
    {
        $metadata = $this->parser()->forMethod(CoversTest::class, 'testOne')->isCoversNothing();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isCoversNothing());
    }

    public function test_Parses_dataProvider_annotation_on_method_for_method_in_same_class(): void
    {
        $metadata = $this->parser()->forMethod(SmallTest::class, 'testWithDataProvider')->isDataProvider();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isDataProvider());
        $this->assertSame(SmallTest::class, $metadata->asArray()[0]->className());
        $this->assertSame('provider', $metadata->asArray()[0]->methodName());
    }

    public function test_Parses_dataProvider_annotation_on_method_for_method_of_other_class(): void
    {
        $metadata = $this->parser()->forMethod(SmallTest::class, 'testWithDataProviderExternal')->isDataProvider();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isDataProvider());
        $this->assertSame('\\' . SmallTest::class, $metadata->asArray()[0]->className());
        $this->assertSame('provider', $metadata->asArray()[0]->methodName());
    }

    public function test_Parses_depends_annotation_on_method(): void
    {
        $metadata = $this->parser()->forMethod(DependencyTest::class, 'testOne')->isDependsOnMethod();

        $this->assertCount(5, $metadata);

        $this->assertTrue($metadata->asArray()[0]->isDependsOnMethod());
        $this->assertSame(AnotherTest::class, $metadata->asArray()[0]->className());
        $this->assertSame('testOne', $metadata->asArray()[0]->methodName());
        $this->assertFalse($metadata->asArray()[0]->deepClone());
        $this->assertFalse($metadata->asArray()[0]->shallowClone());

        $this->assertTrue($metadata->asArray()[1]->isDependsOnMethod());
        $this->assertSame(AnotherTest::class, $metadata->asArray()[1]->className());
        $this->assertSame('testOne', $metadata->asArray()[1]->methodName());
        $this->assertFalse($metadata->asArray()[1]->deepClone());
        $this->assertFalse($metadata->asArray()[1]->shallowClone());

        $this->assertTrue($metadata->asArray()[2]->isDependsOnMethod());
        $this->assertSame(AnotherTest::class, $metadata->asArray()[2]->className());
        $this->assertSame('testOne', $metadata->asArray()[2]->methodName());
        $this->assertTrue($metadata->asArray()[2]->deepClone());
        $this->assertFalse($metadata->asArray()[2]->shallowClone());

        $this->assertTrue($metadata->asArray()[3]->isDependsOnMethod());
        $this->assertSame(AnotherTest::class, $metadata->asArray()[3]->className());
        $this->assertSame('testOne', $metadata->asArray()[3]->methodName());
        $this->assertFalse($metadata->asArray()[3]->deepClone());
        $this->assertFalse($metadata->asArray()[3]->shallowClone());

        $this->assertTrue($metadata->asArray()[4]->isDependsOnMethod());
        $this->assertSame(AnotherTest::class, $metadata->asArray()[4]->className());
        $this->assertSame('testOne', $metadata->asArray()[4]->methodName());
        $this->assertFalse($metadata->asArray()[4]->deepClone());
        $this->assertTrue($metadata->asArray()[4]->shallowClone());

        $metadata = $this->parser()->forMethod(DependencyTest::class, 'testTwo')->isDependsOnMethod();

        $this->assertCount(5, $metadata);

        $this->assertTrue($metadata->asArray()[0]->isDependsOnMethod());
        $this->assertSame(DependencyTest::class, $metadata->asArray()[0]->className());
        $this->assertSame('testOne', $metadata->asArray()[0]->methodName());
        $this->assertFalse($metadata->asArray()[0]->deepClone());
        $this->assertFalse($metadata->asArray()[0]->shallowClone());

        $this->assertTrue($metadata->asArray()[1]->isDependsOnMethod());
        $this->assertSame(DependencyTest::class, $metadata->asArray()[1]->className());
        $this->assertSame('testOne', $metadata->asArray()[1]->methodName());
        $this->assertFalse($metadata->asArray()[1]->deepClone());
        $this->assertFalse($metadata->asArray()[1]->shallowClone());

        $this->assertTrue($metadata->asArray()[2]->isDependsOnMethod());
        $this->assertSame(DependencyTest::class, $metadata->asArray()[2]->className());
        $this->assertSame('testOne', $metadata->asArray()[2]->methodName());
        $this->assertTrue($metadata->asArray()[2]->deepClone());
        $this->assertFalse($metadata->asArray()[2]->shallowClone());

        $this->assertTrue($metadata->asArray()[3]->isDependsOnMethod());
        $this->assertSame(DependencyTest::class, $metadata->asArray()[3]->className());
        $this->assertSame('testOne', $metadata->asArray()[3]->methodName());
        $this->assertFalse($metadata->asArray()[3]->deepClone());
        $this->assertFalse($metadata->asArray()[3]->shallowClone());

        $this->assertTrue($metadata->asArray()[4]->isDependsOnMethod());
        $this->assertSame(DependencyTest::class, $metadata->asArray()[4]->className());
        $this->assertSame('testOne', $metadata->asArray()[4]->methodName());
        $this->assertFalse($metadata->asArray()[4]->deepClone());
        $this->assertTrue($metadata->asArray()[4]->shallowClone());

        $metadata = $this->parser()->forMethod(DependencyTest::class, 'testThree')->isDependsOnClass();

        $this->assertCount(5, $metadata);

        $this->assertTrue($metadata->asArray()[0]->isDependsOnClass());
        $this->assertSame(AnotherTest::class, $metadata->asArray()[0]->className());
        $this->assertFalse($metadata->asArray()[0]->deepClone());
        $this->assertFalse($metadata->asArray()[0]->shallowClone());

        $this->assertTrue($metadata->asArray()[1]->isDependsOnClass());
        $this->assertSame(AnotherTest::class, $metadata->asArray()[1]->className());
        $this->assertFalse($metadata->asArray()[1]->deepClone());
        $this->assertFalse($metadata->asArray()[1]->shallowClone());

        $this->assertTrue($metadata->asArray()[2]->isDependsOnClass());
        $this->assertSame(AnotherTest::class, $metadata->asArray()[2]->className());
        $this->assertTrue($metadata->asArray()[2]->deepClone());
        $this->assertFalse($metadata->asArray()[2]->shallowClone());

        $this->assertTrue($metadata->asArray()[3]->isDependsOnClass());
        $this->assertSame(AnotherTest::class, $metadata->asArray()[3]->className());
        $this->assertFalse($metadata->asArray()[3]->deepClone());
        $this->assertFalse($metadata->asArray()[3]->shallowClone());

        $this->assertTrue($metadata->asArray()[4]->isDependsOnClass());
        $this->assertSame(AnotherTest::class, $metadata->asArray()[4]->className());
        $this->assertFalse($metadata->asArray()[4]->deepClone());
        $this->assertTrue($metadata->asArray()[4]->shallowClone());
    }

    public function test_Parses_doesNotPerformAssertions_annotation_on_method(): void
    {
        $metadata = $this->parser()->forMethod(DoesNotPerformAssertionsTest::class, 'testOne')->isDoesNotPerformAssertions();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isDoesNotPerformAssertions());
    }

    public function test_Parses_excludeGlobalVariableFromBackup_annotation_on_method(): void
    {
        $metadata = $this->parser()->forMethod(BackupGlobalsTest::class, 'testOne')->isExcludeGlobalVariableFromBackup();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isExcludeGlobalVariableFromBackup());
        $this->assertSame('bar', $metadata->asArray()[0]->globalVariableName());
    }

    public function test_Parses_excludeStaticPropertyFromBackup_annotation_on_method(): void
    {
        $metadata = $this->parser()->forMethod(BackupStaticPropertiesTest::class, 'testOne')->isExcludeStaticPropertyFromBackup();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isExcludeStaticPropertyFromBackup());
        $this->assertSame('anotherClassName', $metadata->asArray()[0]->className());
        $this->assertSame('propertyName', $metadata->asArray()[0]->propertyName());
    }

    public function test_Parses_group_annotation_on_method(): void
    {
        $metadata = $this->parser()->forMethod(GroupTest::class, 'testOne')->isGroup();

        $this->assertCount(2, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('another-group', $metadata->asArray()[0]->groupName());
    }

    public function test_Parses_large_annotation_on_method(): void
    {
        $metadata = $this->parser()->forMethod(LargeTest::class, 'testWithLargeAnnotation')->isGroup();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('large', $metadata->asArray()[0]->groupName());
    }

    public function test_Parses_medium_annotation_on_method(): void
    {
        $metadata = $this->parser()->forMethod(MediumTest::class, 'testWithMediumAnnotation')->isGroup();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('medium', $metadata->asArray()[0]->groupName());
    }

    public function test_Parses_postCondition_annotation_on_method(): void
    {
        $metadata = $this->parser()->forMethod(SmallTest::class, 'postCondition')->isPostCondition();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isPostCondition());
    }

    public function test_Parses_preCondition_annotation_on_method(): void
    {
        $metadata = $this->parser()->forMethod(SmallTest::class, 'preCondition')->isPreCondition();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isPreCondition());
    }

    public function test_Parses_preserveGlobalState_annotation_on_method(): void
    {
        $metadata = $this->parser()->forMethod(PreserveGlobalStateTest::class, 'testOne')->isPreserveGlobalState();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isPreserveGlobalState());
        $this->assertFalse($metadata->asArray()[0]->enabled());
    }

    public function test_Parses_requiresFunction_annotation_with_function_on_method(): void
    {
        $metadata = $this->parser()->forMethod(RequiresFunctionTest::class, 'testOne')->isRequiresFunction();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresFunction());
        $this->assertSame('g', $metadata->asArray()[0]->functionName());
    }

    public function test_Parses_requiresFunction_annotation_with_method_on_method(): void
    {
        $metadata = $this->parser()->forMethod(RequiresFunctionTest::class, 'testOne')->isRequiresMethod();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresMethod());
        $this->assertSame('SomeOtherClass', $metadata->asArray()[0]->className());
        $this->assertSame('someOtherMethod', $metadata->asArray()[0]->methodName());
    }

    public function test_Parses_requiresOperatingSystem_annotation_on_method(): void
    {
        $metadata = $this->parser()->forMethod(RequiresOperatingSystemTest::class, 'testOne')->isRequiresOperatingSystem();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresOperatingSystem());
        $this->assertSame('Linux', $metadata->asArray()[0]->operatingSystem());
    }

    public function test_Parses_requiresOperatingSystemFamily_annotation_on_method(): void
    {
        $metadata = $this->parser()->forMethod(RequiresOperatingSystemFamilyTest::class, 'testOne')->isRequiresOperatingSystemFamily();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresOperatingSystemFamily());
        $this->assertSame('Linux', $metadata->asArray()[0]->operatingSystemFamily());
    }

    #[DataProvider('provideRequiresPhpTestMethods')]
    public function test_Parses_requiresPhp_annotation_on_method_with_version_comparison(string $method): void
    {
        $metadata = $this->parser()->forMethod(RequiresPhpTest::class, $method)->isRequiresPhp();

        $this->assertCount(1, $metadata);

        $requirement = $metadata->asArray()[0];

        $this->assertTrue($requirement->isRequiresPhp());

        assert($requirement instanceof RequiresPhp);

        $versionRequirement = $requirement->versionRequirement();

        assert($versionRequirement instanceof ComparisonRequirement);

        $this->assertSame('< 9.0.0', $versionRequirement->asString());
    }

    public function test_Parses_requiresPhp_annotation_on_method_with_version_constraint(): void
    {
        $metadata = $this->parser()->forMethod(RequiresPhp2Test::class, 'testOne')->isRequiresPhp();

        $this->assertCount(1, $metadata);

        $requirement = $metadata->asArray()[0];

        $this->assertTrue($requirement->isRequiresPhp());

        assert($requirement instanceof RequiresPhp);

        $versionRequirement = $requirement->versionRequirement();

        assert($versionRequirement instanceof ConstraintRequirement);

        $this->assertSame('^8.0', $versionRequirement->asString());
    }

    public function test_Parses_requiresPhpExtension_annotation_on_method(): void
    {
        $metadata = $this->parser()->forMethod(RequiresPhpExtensionTest::class, 'testOne')->isRequiresPhpExtension();

        $this->assertCount(2, $metadata);

        $requirement = $metadata->asArray()[0];

        $this->assertTrue($requirement->isRequiresPhpExtension());

        assert($requirement instanceof RequiresPhpExtension);

        $this->assertSame('foo', $requirement->extension());
        $this->assertFalse($requirement->hasVersionRequirement());

        $requirement = $metadata->asArray()[1];

        $this->assertTrue($requirement->isRequiresPhpExtension());

        assert($requirement instanceof RequiresPhpExtension);

        $this->assertSame('bar', $requirement->extension());
        $this->assertTrue($requirement->hasVersionRequirement());

        $versionRequirement = $requirement->versionRequirement();

        assert($versionRequirement instanceof ComparisonRequirement);

        $this->assertSame('>= 1.0.0', $versionRequirement->asString());

        $metadata = $this->parser()->forMethod(RequiresPhpExtensionTest::class, 'testTwo');

        $this->assertCount(1, $metadata);

        $requirement = $metadata->asArray()[0];

        $this->assertTrue($requirement->isRequiresPhpExtension());

        assert($requirement instanceof RequiresPhpExtension);

        $this->assertSame('baz', $requirement->extension());
        $this->assertTrue($requirement->hasVersionRequirement());

        $versionRequirement = $requirement->versionRequirement();

        assert($versionRequirement instanceof ComparisonRequirement);

        $this->assertSame('< 2.0.0', $versionRequirement->asString());
    }

    public function test_Parses_requiresPhpunit_annotation_on_method_with_version_comparison(): void
    {
        $metadata = $this->parser()->forMethod(RequiresPhpunitTest::class, 'testOne')->isRequiresPhpunit();

        $this->assertCount(1, $metadata);

        $requirement = $metadata->asArray()[0];

        $this->assertTrue($requirement->isRequiresPhpunit());

        assert($requirement instanceof RequiresPhpunit);

        $versionRequirement = $requirement->versionRequirement();

        assert($versionRequirement instanceof ComparisonRequirement);

        $this->assertSame('< 11.0.0', $versionRequirement->asString());
    }

    public function test_Parses_requiresPhpunit_annotation_on_method_with_version_constraint(): void
    {
        $metadata = $this->parser()->forMethod(RequiresPhpunit2Test::class, 'testOne')->isRequiresPhpunit();

        $this->assertCount(1, $metadata);

        $requirement = $metadata->asArray()[0];

        $this->assertTrue($requirement->isRequiresPhpunit());

        assert($requirement instanceof RequiresPhpunit);

        $versionRequirement = $requirement->versionRequirement();

        assert($versionRequirement instanceof ConstraintRequirement);

        $this->assertSame('^11.0', $versionRequirement->asString());
    }

    public function test_Parses_requiresSetting_annotation_on_method(): void
    {
        $metadata = $this->parser()->forMethod(RequiresSettingTest::class, 'testOne')->isRequiresSetting();

        $this->assertCount(1, $metadata);

        $requirement = $metadata->asArray()[0];

        $this->assertTrue($requirement->isRequiresSetting());

        assert($requirement instanceof RequiresSetting);

        $this->assertSame('bar', $requirement->setting());
        $this->assertSame('baz', $requirement->value());
    }

    public function test_Parses_runInSeparateProcess_annotation_on_method(): void
    {
        $metadata = $this->parser()->forMethod(ProcessIsolationTest::class, 'testOne')->isRunInSeparateProcess();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRunInSeparateProcess());
    }

    public function test_Parses_small_annotation_on_method(): void
    {
        $metadata = $this->parser()->forMethod(SmallTest::class, 'testWithSmallAnnotation')->isGroup();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('small', $metadata->asArray()[0]->groupName());
    }

    public function test_Parses_test_annotation_on_method(): void
    {
        $metadata = $this->parser()->forMethod(SmallTest::class, 'one')->isTest();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isTest());
    }

    public function test_Parses_testdox_annotation_on_method(): void
    {
        $metadata = $this->parser()->forMethod(TestDoxTest::class, 'testOne')->isTestDox();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isTestDox());
        $this->assertSame('text', $metadata->asArray()[0]->text());
    }

    public function test_Parses_ticket_annotation_on_method(): void
    {
        $metadata = $this->parser()->forMethod(GroupTest::class, 'testOne')->isGroup();

        $this->assertCount(2, $metadata);
        $this->assertTrue($metadata->asArray()[1]->isGroup());
        $this->assertSame('another-ticket', $metadata->asArray()[1]->groupName());
    }

    public function test_Parses_uses_annotation_on_method(): void
    {
        $metadata = $this->parser()->forMethod(UsesTest::class, 'testOne')->isUses();

        $this->assertCount(1, $metadata);

        $this->assertTrue($metadata->asArray()[0]->isUses());
        $this->assertSame('Foo::bar', $metadata->asArray()[0]->target());
    }

    public function test_Merges_class_level_and_method_level_annotations(): void
    {
        $metadata = $this->parser()->forClassAndMethod(SmallTest::class, 'testWithDataProvider');

        $this->assertCount(2, $metadata);

        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertTrue($metadata->asArray()[1]->isDataProvider());
    }

    abstract protected function parser(): Parser;
}
