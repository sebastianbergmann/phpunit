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

use function assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\Parser\AnnotationParser;
use PHPUnit\Metadata\Version\ComparisonRequirement;
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
use PHPUnit\TestFixture\Metadata\Annotation\RequiresPhpExtensionTest;
use PHPUnit\TestFixture\Metadata\Annotation\RequiresPhpTest;
use PHPUnit\TestFixture\Metadata\Annotation\RequiresPhpunitTest;
use PHPUnit\TestFixture\Metadata\Annotation\SmallTest;
use PHPUnit\TestFixture\Metadata\Annotation\TestDoxTest;
use PHPUnit\TestFixture\Metadata\Annotation\UsesTest;

#[CoversClass(AnnotationParser::class)]
#[Small]
final class AnnotationParserTest extends TestCase
{
    public static function provideRequiresPhpTestMethods(): array
    {
        return [['testOne'], ['testTwo']];
    }

    public function test_Parses_backupGlobals_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(BackupGlobalsTest::class)->isBackupGlobals();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBackupGlobals());
        $this->assertTrue($metadata->asArray()[0]->enabled());
    }

    public function test_Parses_backupStaticAttributes_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(BackupStaticPropertiesTest::class)->isBackupStaticProperties();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBackupStaticProperties());
        $this->assertTrue($metadata->asArray()[0]->enabled());
    }

    public function test_Parses_covers_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(CoversTest::class)->isCovers();

        $this->assertCount(2, $metadata);

        $this->assertTrue($metadata->asArray()[0]->isCovers());
        $this->assertSame('::\PHPUnit\TestFixture\Metadata\Annotation\f', $metadata->asArray()[0]->target());

        $this->assertTrue($metadata->asArray()[1]->isCovers());
        $this->assertSame('\PHPUnit\TestFixture\Metadata\Annotation\Example', $metadata->asArray()[1]->target());
    }

    public function test_Parses_coversDefaultClass_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(CoversTest::class)->isCoversDefaultClass();

        $this->assertCount(1, $metadata);

        $this->assertTrue($metadata->asArray()[0]->isCoversDefaultClass());
        $this->assertSame('\PHPUnit\TestFixture\Metadata\Annotation\Example', $metadata->asArray()[0]->className());
    }

    public function test_Parses_coversNothing_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(CoversTest::class)->isCoversNothing();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isCoversNothing());
    }

    public function test_Parses_doesNotPerformAssertions_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(DoesNotPerformAssertionsTest::class)->isDoesNotPerformAssertions();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isDoesNotPerformAssertions());
    }

    public function test_Parses_group_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(GroupTest::class)->isGroup();

        $this->assertCount(2, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('group', $metadata->asArray()[0]->groupName());
    }

    public function test_Parses_large_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(LargeTest::class)->isGroup();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('large', $metadata->asArray()[0]->groupName());
    }

    public function test_Parses_medium_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(MediumTest::class)->isGroup();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('medium', $metadata->asArray()[0]->groupName());
    }

    public function test_Parses_preserveGlobalState_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(PreserveGlobalStateTest::class)->isPreserveGlobalState();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isPreserveGlobalState());
        $this->assertTrue($metadata->asArray()[0]->enabled());
    }

    public function test_Parses_requiresFunction_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(RequiresFunctionTest::class)->isRequiresFunction();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresFunction());
        $this->assertSame('f', $metadata->asArray()[0]->functionName());
    }

    public function test_Parses_requiresOperatingSystem_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(RequiresOperatingSystemTest::class)->isRequiresOperatingSystem();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresOperatingSystem());
        $this->assertSame('Linux', $metadata->asArray()[0]->operatingSystem());
    }

    public function test_Parses_requiresOperatingSystemFamily_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(RequiresOperatingSystemFamilyTest::class)->isRequiresOperatingSystemFamily();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresOperatingSystemFamily());
        $this->assertSame('Linux', $metadata->asArray()[0]->operatingSystemFamily());
    }

    public function test_Parses_requiresPhp_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(RequiresPhpTest::class)->isRequiresPhp();

        $this->assertCount(1, $metadata);

        $requirement = $metadata->asArray()[0];

        assert($requirement instanceof RequiresPhp);

        $this->assertTrue($requirement->isRequiresPhp());
        $this->assertInstanceOf(ComparisonRequirement::class, $requirement->versionRequirement());

        $versionRequirement = $requirement->versionRequirement();

        assert($versionRequirement instanceof ComparisonRequirement);

        $this->assertSame('>= 8.0.0', $versionRequirement->asString());
    }

    public function test_Parses_requiresPhpExtension_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(RequiresPhpExtensionTest::class)->isRequiresPhpExtension();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresPhpExtension());
        $this->assertSame('foo', $metadata->asArray()[0]->extension());
        $this->assertFalse($metadata->asArray()[0]->hasVersionRequirement());
    }

    public function test_Parses_requiresPhpunit_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(RequiresPhpunitTest::class)->isRequiresPhpunit();

        $this->assertCount(1, $metadata);

        $requirement = $metadata->asArray()[0];

        assert($requirement instanceof RequiresPhpunit);

        $this->assertTrue($requirement->isRequiresPhpunit());
        $this->assertInstanceOf(ComparisonRequirement::class, $requirement->versionRequirement());

        $versionRequirement = $requirement->versionRequirement();

        assert($versionRequirement instanceof ComparisonRequirement);

        $this->assertSame('>= 10.0.0', $versionRequirement->asString());
    }

    public function test_Parses_runClassInSeparateProcess_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(ProcessIsolationTest::class)->isRunClassInSeparateProcess();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRunClassInSeparateProcess());
    }

    public function test_Parses_runTestsInSeparateProcesses_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(ProcessIsolationTest::class)->isRunTestsInSeparateProcesses();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRunTestsInSeparateProcesses());
    }

    public function test_Parses_small_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(SmallTest::class)->isGroup();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('small', $metadata->asArray()[0]->groupName());
    }

    public function test_Parses_testdox_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(TestDoxTest::class)->isTestDox();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isTestDox());
        $this->assertSame('text', $metadata->asArray()[0]->text());
    }

    public function test_Parses_ticket_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(GroupTest::class)->isGroup();

        $this->assertCount(2, $metadata);
        $this->assertTrue($metadata->asArray()[1]->isGroup());
        $this->assertSame('ticket', $metadata->asArray()[1]->groupName());
    }

    public function test_Parses_uses_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(UsesTest::class)->isUses();

        $this->assertCount(2, $metadata);

        $this->assertTrue($metadata->asArray()[0]->isUses());
        $this->assertSame('::\PHPUnit\TestFixture\Metadata\Annotation\f', $metadata->asArray()[0]->target());

        $this->assertTrue($metadata->asArray()[1]->isUses());
        $this->assertSame('\PHPUnit\TestFixture\Metadata\Annotation\Example', $metadata->asArray()[1]->target());
    }

    public function test_Parses_usesDefaultClass_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(UsesTest::class)->isUsesDefaultClass();

        $this->assertCount(1, $metadata);

        $this->assertTrue($metadata->asArray()[0]->isUsesDefaultClass());
        $this->assertSame('\PHPUnit\TestFixture\Metadata\Annotation\Example', $metadata->asArray()[0]->className());
    }

    public function test_Parses_after_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(SmallTest::class, 'afterTest')->isAfter();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isAfter());
    }

    public function test_Parses_afterClass_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(SmallTest::class, 'afterTests')->isAfterClass();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isAfterClass());
    }

    public function test_Parses_backupGlobals_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(BackupGlobalsTest::class, 'testOne')->isBackupGlobals();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBackupGlobals());
        $this->assertFalse($metadata->asArray()[0]->enabled());
    }

    public function test_Parses_backupStaticProperties_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(BackupStaticPropertiesTest::class, 'testOne')->isBackupStaticProperties();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBackupStaticProperties());
        $this->assertFalse($metadata->asArray()[0]->enabled());
    }

    public function test_Parses_before_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(SmallTest::class, 'beforeTest')->isBefore();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBefore());
    }

    public function test_Parses_beforeClass_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(SmallTest::class, 'beforeTests')->isBeforeClass();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBeforeClass());
    }

    public function test_Parses_covers_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(CoversTest::class, 'testTwo')->isCovers();

        $this->assertCount(1, $metadata);

        $this->assertTrue($metadata->asArray()[0]->isCovers());
        $this->assertSame('Foo::bar', $metadata->asArray()[0]->target());
    }

    public function test_Parses_coversNothing_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(CoversTest::class, 'testOne')->isCoversNothing();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isCoversNothing());
    }

    public function test_Parses_dataProvider_annotation_on_method_for_method_in_same_class(): void
    {
        $metadata = (new AnnotationParser)->forMethod(SmallTest::class, 'testWithDataProvider')->isDataProvider();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isDataProvider());
        $this->assertSame(SmallTest::class, $metadata->asArray()[0]->className());
        $this->assertSame('provider', $metadata->asArray()[0]->methodName());
    }

    public function test_Parses_dataProvider_annotation_on_method_for_method_of_other_class(): void
    {
        $metadata = (new AnnotationParser)->forMethod(SmallTest::class, 'testWithDataProviderExternal')->isDataProvider();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isDataProvider());
        $this->assertSame('\\' . SmallTest::class, $metadata->asArray()[0]->className());
        $this->assertSame('provider', $metadata->asArray()[0]->methodName());
    }

    public function test_Parses_depends_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(DependencyTest::class, 'testOne')->isDependsOnMethod();

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

        $metadata = (new AnnotationParser)->forMethod(DependencyTest::class, 'testTwo')->isDependsOnMethod();

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

        $metadata = (new AnnotationParser)->forMethod(DependencyTest::class, 'testThree')->isDependsOnClass();

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
        $metadata = (new AnnotationParser)->forMethod(DoesNotPerformAssertionsTest::class, 'testOne')->isDoesNotPerformAssertions();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isDoesNotPerformAssertions());
    }

    public function test_Parses_excludeGlobalVariableFromBackup_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(BackupGlobalsTest::class, 'testOne')->isExcludeGlobalVariableFromBackup();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isExcludeGlobalVariableFromBackup());
        $this->assertSame('bar', $metadata->asArray()[0]->globalVariableName());
    }

    public function test_Parses_excludeStaticPropertyFromBackup_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(BackupStaticPropertiesTest::class, 'testOne')->isExcludeStaticPropertyFromBackup();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isExcludeStaticPropertyFromBackup());
        $this->assertSame('anotherClassName', $metadata->asArray()[0]->className());
        $this->assertSame('propertyName', $metadata->asArray()[0]->propertyName());
    }

    public function test_Parses_group_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(GroupTest::class, 'testOne')->isGroup();

        $this->assertCount(2, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('another-group', $metadata->asArray()[0]->groupName());
    }

    public function test_Parses_large_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(LargeTest::class, 'testWithLargeAnnotation')->isGroup();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('large', $metadata->asArray()[0]->groupName());
    }

    public function test_Parses_medium_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(MediumTest::class, 'testWithMediumAnnotation')->isGroup();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('medium', $metadata->asArray()[0]->groupName());
    }

    public function test_Parses_postCondition_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(SmallTest::class, 'postCondition')->isPostCondition();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isPostCondition());
    }

    public function test_Parses_preCondition_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(SmallTest::class, 'preCondition')->isPreCondition();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isPreCondition());
    }

    public function test_Parses_preserveGlobalState_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(PreserveGlobalStateTest::class, 'testOne')->isPreserveGlobalState();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isPreserveGlobalState());
        $this->assertFalse($metadata->asArray()[0]->enabled());
    }

    public function test_Parses_requiresFunction_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(RequiresFunctionTest::class, 'testOne')->isRequiresFunction();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresFunction());
        $this->assertSame('g', $metadata->asArray()[0]->functionName());
    }

    public function test_Parses_requiresOperatingSystem_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forClass(RequiresOperatingSystemTest::class)->isRequiresOperatingSystem();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresOperatingSystem());
        $this->assertSame('Linux', $metadata->asArray()[0]->operatingSystem());
    }

    public function test_Parses_requiresOperatingSystemFamily_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(RequiresOperatingSystemFamilyTest::class, 'testOne')->isRequiresOperatingSystemFamily();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresOperatingSystemFamily());
        $this->assertSame('Linux', $metadata->asArray()[0]->operatingSystemFamily());
    }

    #[DataProvider('provideRequiresPhpTestMethods')]
    public function test_Parses_requiresPhp_annotation_on_method(string $method): void
    {
        $metadata = (new AnnotationParser)->forMethod(RequiresPhpTest::class, $method)->isRequiresPhp();

        $this->assertCount(1, $metadata);

        $requirement = $metadata->asArray()[0];

        $this->assertTrue($requirement->isRequiresPhp());

        assert($requirement instanceof RequiresPhp);

        $versionRequirement = $requirement->versionRequirement();

        assert($versionRequirement instanceof ComparisonRequirement);

        $this->assertSame('< 9.0.0', $versionRequirement->asString());
    }

    public function test_Parses_requiresPhpExtension_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(RequiresPhpExtensionTest::class, 'testOne')->isRequiresPhpExtension();

        $this->assertCount(1, $metadata);

        $requirement = $metadata->asArray()[0];

        $this->assertTrue($requirement->isRequiresPhpExtension());

        assert($requirement instanceof RequiresPhpExtension);

        $this->assertSame('bar', $requirement->extension());
        $this->assertTrue($requirement->hasVersionRequirement());

        $versionRequirement = $requirement->versionRequirement();

        assert($versionRequirement instanceof ComparisonRequirement);

        $this->assertSame('>= 1.0.0', $versionRequirement->asString());

        $metadata = (new AnnotationParser)->forMethod(RequiresPhpExtensionTest::class, 'testTwo');

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

    public function test_Parses_requiresPhpunit_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(RequiresPhpunitTest::class, 'testOne')->isRequiresPhpunit();

        $this->assertCount(1, $metadata);

        $requirement = $metadata->asArray()[0];

        $this->assertTrue($requirement->isRequiresPhpunit());

        assert($requirement instanceof RequiresPhpunit);

        $versionRequirement = $requirement->versionRequirement();

        assert($versionRequirement instanceof ComparisonRequirement);

        $this->assertSame('< 11.0.0', $versionRequirement->asString());
    }

    public function test_Parses_runInSeparateProcess_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(ProcessIsolationTest::class, 'testOne')->isRunInSeparateProcess();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRunInSeparateProcess());
    }

    public function test_Parses_small_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(SmallTest::class, 'testWithSmallAnnotation')->isGroup();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('small', $metadata->asArray()[0]->groupName());
    }

    public function test_Parses_test_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(SmallTest::class, 'one')->isTest();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isTest());
    }

    public function test_Parses_testdox_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(TestDoxTest::class, 'testOne')->isTestDox();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isTestDox());
        $this->assertSame('text', $metadata->asArray()[0]->text());
    }

    public function test_Parses_ticket_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(GroupTest::class, 'testOne')->isGroup();

        $this->assertCount(2, $metadata);
        $this->assertTrue($metadata->asArray()[1]->isGroup());
        $this->assertSame('another-ticket', $metadata->asArray()[1]->groupName());
    }

    public function test_Parses_uses_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(UsesTest::class, 'testOne')->isUses();

        $this->assertCount(1, $metadata);

        $this->assertTrue($metadata->asArray()[0]->isUses());
        $this->assertSame('Foo::bar', $metadata->asArray()[0]->target());
    }

    public function test_Merges_class_level_and_method_level_annotations(): void
    {
        $metadata = (new AnnotationParser)->forClassAndMethod(SmallTest::class, 'testWithDataProvider');

        $this->assertCount(2, $metadata);

        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertTrue($metadata->asArray()[1]->isDataProvider());
    }
}
