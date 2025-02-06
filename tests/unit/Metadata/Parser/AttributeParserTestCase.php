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
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\DependsOnClass;
use PHPUnit\Metadata\DependsOnMethod;
use PHPUnit\Metadata\InvalidAttributeException;
use PHPUnit\Metadata\RequiresPhp;
use PHPUnit\Metadata\RequiresPhpExtension;
use PHPUnit\Metadata\RequiresPhpunit;
use PHPUnit\Metadata\RequiresPhpunitExtension;
use PHPUnit\Metadata\RequiresSetting;
use PHPUnit\Metadata\Version\ComparisonRequirement;
use PHPUnit\Metadata\Version\ConstraintRequirement;
use PHPUnit\TestFixture\Metadata\Attribute\AnotherTest;
use PHPUnit\TestFixture\Metadata\Attribute\BackupGlobalsTest;
use PHPUnit\TestFixture\Metadata\Attribute\BackupStaticPropertiesTest;
use PHPUnit\TestFixture\Metadata\Attribute\CoversTest;
use PHPUnit\TestFixture\Metadata\Attribute\DependencyTest;
use PHPUnit\TestFixture\Metadata\Attribute\DisableReturnValueGenerationForTestDoublesTest;
use PHPUnit\TestFixture\Metadata\Attribute\DoesNotPerformAssertionsTest;
use PHPUnit\TestFixture\Metadata\Attribute\DuplicateSmallAttributeTest;
use PHPUnit\TestFixture\Metadata\Attribute\DuplicateTestAttributeTest;
use PHPUnit\TestFixture\Metadata\Attribute\Example;
use PHPUnit\TestFixture\Metadata\Attribute\ExampleTrait;
use PHPUnit\TestFixture\Metadata\Attribute\GroupTest;
use PHPUnit\TestFixture\Metadata\Attribute\IgnoreDeprecationsClassTest;
use PHPUnit\TestFixture\Metadata\Attribute\IgnoreDeprecationsMethodTest;
use PHPUnit\TestFixture\Metadata\Attribute\IgnorePhpunitDeprecationsClassTest;
use PHPUnit\TestFixture\Metadata\Attribute\IgnorePhpunitDeprecationsMethodTest;
use PHPUnit\TestFixture\Metadata\Attribute\LargeTest;
use PHPUnit\TestFixture\Metadata\Attribute\MediumTest;
use PHPUnit\TestFixture\Metadata\Attribute\NonPhpunitAttributeTest;
use PHPUnit\TestFixture\Metadata\Attribute\PhpunitAttributeThatDoesNotExistTest;
use PHPUnit\TestFixture\Metadata\Attribute\PreserveGlobalStateTest;
use PHPUnit\TestFixture\Metadata\Attribute\ProcessIsolationTest;
use PHPUnit\TestFixture\Metadata\Attribute\RequiresFunctionTest;
use PHPUnit\TestFixture\Metadata\Attribute\RequiresMethodTest;
use PHPUnit\TestFixture\Metadata\Attribute\RequiresOperatingSystemFamilyTest;
use PHPUnit\TestFixture\Metadata\Attribute\RequiresOperatingSystemTest;
use PHPUnit\TestFixture\Metadata\Attribute\RequiresPhpExtensionTest;
use PHPUnit\TestFixture\Metadata\Attribute\RequiresPhpTest;
use PHPUnit\TestFixture\Metadata\Attribute\RequiresPhpunitExtensionTest;
use PHPUnit\TestFixture\Metadata\Attribute\RequiresPhpunitTest;
use PHPUnit\TestFixture\Metadata\Attribute\RequiresSettingTest;
use PHPUnit\TestFixture\Metadata\Attribute\SmallTest;
use PHPUnit\TestFixture\Metadata\Attribute\TestDoxTest;
use PHPUnit\TestFixture\Metadata\Attribute\TestWithTest;
use PHPUnit\TestFixture\Metadata\Attribute\UsesTest;
use PHPUnit\TestFixture\Metadata\Attribute\WithoutErrorHandlerTest;

abstract class AttributeParserTestCase extends TestCase
{
    #[TestDox('Parses #[BackupGlobals] attribute on class')]
    public function test_parses_BackupGlobals_attribute_on_class(): void
    {
        $metadata = $this->parser()->forClass(BackupGlobalsTest::class)->isBackupGlobals();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBackupGlobals());
        $this->assertTrue($metadata->asArray()[0]->enabled());
    }

    #[TestDox('Parses #[BackupStaticProperties] attribute on class')]
    public function test_parses_BackupStaticProperties_attribute_on_class(): void
    {
        $metadata = $this->parser()->forClass(BackupStaticPropertiesTest::class)->isBackupStaticProperties();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBackupStaticProperties());
        $this->assertTrue($metadata->asArray()[0]->enabled());
    }

    #[TestDox('Parses #[CoversClass] attribute on class')]
    public function test_parses_CoversClass_attribute_on_class(): void
    {
        $metadata = $this->parser()->forClass(CoversTest::class)->isCoversClass();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isCoversClass());
        $this->assertSame(Example::class, $metadata->asArray()[0]->className());
    }

    #[TestDox('Parses #[CoversTrait] attribute on class')]
    public function test_parses_CoversTrait_attribute_on_class(): void
    {
        $metadata = $this->parser()->forClass(CoversTest::class)->isCoversTrait();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isCoversTrait());
        $this->assertSame(ExampleTrait::class, $metadata->asArray()[0]->traitName());
    }

    #[TestDox('Parses #[CoversFunction] attribute on class')]
    public function test_parses_CoversFunction_attribute_on_class(): void
    {
        $metadata = $this->parser()->forClass(CoversTest::class)->isCoversFunction();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isCoversFunction());
        $this->assertSame('f', $metadata->asArray()[0]->functionName());
    }

    #[TestDox('Parses #[CoversMethod] attribute on class')]
    public function test_parses_CoversMethod_attribute_on_class(): void
    {
        $metadata = $this->parser()->forClass(CoversTest::class)->isCoversMethod();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isCoversMethod());
        $this->assertSame(Example::class, $metadata->asArray()[0]->className());
        $this->assertSame('method', $metadata->asArray()[0]->methodName());
    }

    #[TestDox('Parses #[CoversNothing] attribute on class')]
    public function test_parses_CoversNothing_attribute_on_class(): void
    {
        $metadata = $this->parser()->forClass(CoversTest::class)->isCoversNothing();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isCoversNothing());
    }

    #[TestDox('Parses #[DisableReturnValueGenerationForTestDoubles] attribute on class')]
    public function test_parses_DisableReturnValueGenerationForTestDoubles_attribute_on_class(): void
    {
        $metadata = $this->parser()->forClass(DisableReturnValueGenerationForTestDoublesTest::class)->isDisableReturnValueGenerationForTestDoubles();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isDisableReturnValueGenerationForTestDoubles());
    }

    #[TestDox('Parses #[DoesNotPerformAssertions] attribute on class')]
    public function test_parses_DoesNotPerformAssertions_attribute_on_class(): void
    {
        $metadata = $this->parser()->forClass(DoesNotPerformAssertionsTest::class)->isDoesNotPerformAssertions();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isDoesNotPerformAssertions());
    }

    #[TestDox('Parses #[ExcludeGlobalVariableFromBackup] attribute on class')]
    public function test_parses_ExcludeGlobalVariableFromBackup_attribute_on_class(): void
    {
        $metadata = $this->parser()->forClass(BackupGlobalsTest::class)->isExcludeGlobalVariableFromBackup();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isExcludeGlobalVariableFromBackup());
        $this->assertSame('foo', $metadata->asArray()[0]->globalVariableName());
    }

    #[TestDox('Parses #[ExcludeStaticPropertyFromBackup] attribute on class')]
    public function test_parses_ExcludeStaticPropertyFromBackup_attribute_on_class(): void
    {
        $metadata = $this->parser()->forClass(BackupStaticPropertiesTest::class)->isExcludeStaticPropertyFromBackup();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isExcludeStaticPropertyFromBackup());
        $this->assertSame('className', $metadata->asArray()[0]->className());
        $this->assertSame('propertyName', $metadata->asArray()[0]->propertyName());
    }

    #[TestDox('Parses #[Group] attribute on class')]
    public function test_parses_Group_attribute_on_class(): void
    {
        $metadata = $this->parser()->forClass(GroupTest::class)->isGroup();

        $this->assertCount(2, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('group', $metadata->asArray()[0]->groupName());
    }

    #[TestDox('Parses #[Large] attribute on class')]
    public function test_parses_Large_attribute_on_class(): void
    {
        $metadata = $this->parser()->forClass(LargeTest::class)->isGroup();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('large', $metadata->asArray()[0]->groupName());
    }

    #[TestDox('Parses #[Medium] attribute on class')]
    public function test_parses_Medium_attribute_on_class(): void
    {
        $metadata = $this->parser()->forClass(MediumTest::class)->isGroup();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('medium', $metadata->asArray()[0]->groupName());
    }

    #[TestDox('Parses #[IgnoreDeprecations] attribute on class')]
    public function test_parses_IgnoreDeprecations_attribute_on_class(): void
    {
        $metadata = $this->parser()->forClass(IgnoreDeprecationsClassTest::class)->isIgnoreDeprecations();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isIgnoreDeprecations());
    }

    #[TestDox('Parses #[IgnorePhpunitDeprecations] attribute on class')]
    public function test_parses_IgnorePhpunitDeprecations_attribute_on_class(): void
    {
        $metadata = $this->parser()->forClass(IgnorePhpunitDeprecationsClassTest::class)->isIgnorePhpunitDeprecations();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isIgnorePhpunitDeprecations());
    }

    #[TestDox('Parses #[PreserveGlobalState] attribute on class')]
    public function test_parses_PreserveGlobalState_attribute_on_class(): void
    {
        $metadata = $this->parser()->forClass(PreserveGlobalStateTest::class)->isPreserveGlobalState();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isPreserveGlobalState());
        $this->assertTrue($metadata->asArray()[0]->enabled());
    }

    #[TestDox('Parses #[RequiresMethod] attribute on class')]
    public function test_parses_RequiresMethod_attribute_on_class(): void
    {
        $metadata = $this->parser()->forClass(RequiresMethodTest::class)->isRequiresMethod();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresMethod());
        $this->assertSame('ClassName', $metadata->asArray()[0]->className());
        $this->assertSame('methodName', $metadata->asArray()[0]->methodName());
    }

    #[TestDox('Parses #[RequiresFunction] attribute on class')]
    public function test_parses_RequiresFunction_attribute_on_class(): void
    {
        $metadata = $this->parser()->forClass(RequiresFunctionTest::class)->isRequiresFunction();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresFunction());
        $this->assertSame('f', $metadata->asArray()[0]->functionName());
    }

    #[TestDox('Parses #[RequiresOperatingSystem] attribute on class')]
    public function test_parses_RequiresOperatingSystem_attribute_on_class(): void
    {
        $metadata = $this->parser()->forClass(RequiresOperatingSystemTest::class)->isRequiresOperatingSystem();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresOperatingSystem());
        $this->assertSame('Linux', $metadata->asArray()[0]->operatingSystem());
    }

    #[TestDox('Parses #[RequiresOperatingSystemFamily] attribute on class')]
    public function test_parses_RequiresOperatingSystemFamily_attribute_on_class(): void
    {
        $metadata = $this->parser()->forClass(RequiresOperatingSystemFamilyTest::class)->isRequiresOperatingSystemFamily();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresOperatingSystemFamily());
        $this->assertSame('Linux', $metadata->asArray()[0]->operatingSystemFamily());
    }

    #[TestDox('Parses #[RequiresPhp] attribute on class')]
    public function test_parses_RequiresPhp_attribute_on_class(): void
    {
        $metadata = $this->parser()->forClass(RequiresPhpTest::class)->isRequiresPhp();

        $this->assertCount(1, $metadata);

        $requirement = $metadata->asArray()[0];

        $this->assertTrue($requirement->isRequiresPhp());

        assert($requirement instanceof RequiresPhp);

        $versionRequirement = $requirement->versionRequirement();

        assert($versionRequirement instanceof ConstraintRequirement);

        $this->assertSame('8.0.0', $versionRequirement->asString());
    }

    #[TestDox('Parses #[RequiresPhpExtension] attribute on class')]
    public function test_parses_RequiresPhpExtension_attribute_on_class(): void
    {
        $metadata = $this->parser()->forClass(RequiresPhpExtensionTest::class)->isRequiresPhpExtension();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresPhpExtension());
        $this->assertSame('foo', $metadata->asArray()[0]->extension());
        $this->assertTrue($metadata->asArray()[0]->hasVersionRequirement());
        $this->assertSame('>= 1.0', $metadata->asArray()[0]->versionRequirement()->asString());
    }

    #[TestDox('Parses #[RequiresPhpunit] attribute on class')]
    public function test_parses_RequiresPhpunit_attribute_on_class(): void
    {
        $metadata = $this->parser()->forClass(RequiresPhpunitTest::class)->isRequiresPhpunit();

        $this->assertCount(1, $metadata);

        $requirement = $metadata->asArray()[0];

        $this->assertTrue($requirement->isRequiresPhpunit());

        assert($requirement instanceof RequiresPhpunit);

        $versionRequirement = $requirement->versionRequirement();

        assert($versionRequirement instanceof ComparisonRequirement);

        $this->assertSame('>= 10.0.0', $versionRequirement->asString());
    }

    #[TestDox('Parses #[RequiresPhpunitExtension] attribute on class')]
    public function test_parses_RequiresPhpunitExtension_attribute_on_class(): void
    {
        $metadata = $this->parser()->forClass(RequiresPhpunitExtensionTest::class)->isRequiresPhpunitExtension();

        $this->assertCount(1, $metadata);

        $requirement = $metadata->asArray()[0];

        $this->assertTrue($requirement->isRequiresPhpunitExtension());

        assert($requirement instanceof RequiresPhpunitExtension);

        $this->assertSame('PHPUnit\TestFixture\Metadata\Attribute\SomeExtension', $requirement->extensionClass());
    }

    #[TestDox('Parses #[RequiresSetting] attribute on class')]
    public function test_parses_RequiresSetting_attribute_on_class(): void
    {
        $metadata = $this->parser()->forClass(RequiresSettingTest::class)->isRequiresSetting();

        $this->assertCount(1, $metadata);

        $requirement = $metadata->asArray()[0];

        $this->assertTrue($requirement->isRequiresSetting());

        assert($requirement instanceof RequiresSetting);

        $this->assertSame('setting', $requirement->setting());
        $this->assertSame('value', $requirement->value());
    }

    #[TestDox('Parses #[RunClassInSeparateProcess] attribute on class')]
    public function test_parses_RunClassInSeparateProcess_attribute_on_class(): void
    {
        $metadata = $this->parser()->forClass(ProcessIsolationTest::class)->isRunClassInSeparateProcess();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRunClassInSeparateProcess());
    }

    #[TestDox('Parses #[RunTestsInSeparateProcesses] attribute on class')]
    public function test_parses_RunTestsInSeparateProcesses_attribute_on_class(): void
    {
        $metadata = $this->parser()->forClass(ProcessIsolationTest::class)->isRunTestsInSeparateProcesses();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRunTestsInSeparateProcesses());
    }

    #[TestDox('Parses #[Small] attribute on class')]
    public function test_parses_Small_attribute_on_class(): void
    {
        $metadata = $this->parser()->forClass(SmallTest::class)->isGroup();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('small', $metadata->asArray()[0]->groupName());
    }

    #[TestDox('Parses #[TestDox] attribute on class')]
    public function test_parses_TestDox_attribute_on_class(): void
    {
        $metadata = $this->parser()->forClass(TestDoxTest::class)->isTestDox();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isTestDox());
        $this->assertSame('text', $metadata->asArray()[0]->text());
    }

    #[TestDox('Parses #[Ticket] attribute on class')]
    public function test_parses_Ticket_attribute_on_class(): void
    {
        $metadata = $this->parser()->forClass(GroupTest::class)->isGroup();

        $this->assertCount(2, $metadata);
        $this->assertTrue($metadata->asArray()[1]->isGroup());
        $this->assertSame('ticket', $metadata->asArray()[1]->groupName());
    }

    #[TestDox('Parses #[UsesClass] attribute on class')]
    public function test_parses_UsesClass_attribute_on_class(): void
    {
        $metadata = $this->parser()->forClass(UsesTest::class)->isUsesClass();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isUsesClass());
        $this->assertSame(Example::class, $metadata->asArray()[0]->className());
    }

    #[TestDox('Parses #[UsesTrait] attribute on class')]
    public function test_parses_UsesTrait_attribute_on_class(): void
    {
        $metadata = $this->parser()->forClass(UsesTest::class)->isUsesTrait();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isUsesTrait());
        $this->assertSame(ExampleTrait::class, $metadata->asArray()[0]->traitName());
    }

    #[TestDox('Parses #[UsesFunction] attribute on class')]
    public function test_parses_UsesFunction_attribute_on_class(): void
    {
        $metadata = $this->parser()->forClass(UsesTest::class)->isUsesFunction();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isUsesFunction());
        $this->assertSame('f', $metadata->asArray()[0]->functionName());
    }

    #[TestDox('Parses #[UsesMethod] attribute on class')]
    public function test_parses_UsesMethod_attribute_on_class(): void
    {
        $metadata = $this->parser()->forClass(UsesTest::class)->isUsesMethod();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isUsesMethod());
        $this->assertSame(Example::class, $metadata->asArray()[0]->className());
        $this->assertSame('method', $metadata->asArray()[0]->methodName());
    }

    #[TestDox('Parses #[After] attribute on class')]
    public function test_parses_After_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(SmallTest::class, 'afterTest')->isAfter();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isAfter());
    }

    #[TestDox('Parses #[AfterClass] attribute on class')]
    public function test_parses_AfterClass_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(SmallTest::class, 'afterTests')->isAfterClass();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isAfterClass());
    }

    #[TestDox('Parses #[BackupGlobals] attribute on method')]
    public function test_parses_BackupGlobals_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(BackupGlobalsTest::class, 'testOne')->isBackupGlobals();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBackupGlobals());
        $this->assertFalse($metadata->asArray()[0]->enabled());
    }

    #[TestDox('Parses #[BackupStaticProperties] attribute on method')]
    public function test_parses_BackupStaticProperties_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(BackupStaticPropertiesTest::class, 'testOne')->isBackupStaticProperties();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBackupStaticProperties());
        $this->assertFalse($metadata->asArray()[0]->enabled());
    }

    #[TestDox('Parses #[Before] attribute on method')]
    public function test_parses_Before_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(SmallTest::class, 'beforeTest')->isBefore();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBefore());
    }

    #[TestDox('Parses #[BeforeClass] attribute on method')]
    public function test_parses_BeforeClass_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(SmallTest::class, 'beforeTests')->isBeforeClass();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBeforeClass());
    }

    #[TestDox('Parses #[CoversNothing] attribute on method')]
    public function test_parses_CoversNothing_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(CoversTest::class, 'testOne')->isCoversNothing();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isCoversNothing());
    }

    #[TestDox('Parses #[DataProvider] attribute on method')]
    public function test_parses_DataProvider_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(SmallTest::class, 'testWithDataProvider')->isDataProvider();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isDataProvider());
        $this->assertSame(SmallTest::class, $metadata->asArray()[0]->className());
        $this->assertSame('provider', $metadata->asArray()[0]->methodName());
    }

    #[TestDox('Parses #[DataProviderExternal] attribute on method')]
    public function test_parses_DataProviderExternal_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(SmallTest::class, 'testWithDataProviderExternal')->isDataProvider();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isDataProvider());
        $this->assertSame(SmallTest::class, $metadata->asArray()[0]->className());
        $this->assertSame('provider', $metadata->asArray()[0]->methodName());
    }

    #[TestDox('Parses #[Depends] attribute on method')]
    public function test_parses_Depends_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(DependencyTest::class, 'testOne')->isDependsOnMethod();

        $this->assertCount(1, $metadata);

        $depends = $metadata->asArray()[0];

        assert($depends instanceof DependsOnMethod);

        $this->assertSame(DependencyTest::class, $depends->className());
        $this->assertSame('testOne', $depends->methodName());
        $this->assertFalse($depends->deepClone());
        $this->assertFalse($depends->shallowClone());
    }

    #[TestDox('Parses #[DependsUsingDeepClone] attribute on method')]
    public function test_parses_DependsUsingDeepClone_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(DependencyTest::class, 'testTwo')->isDependsOnMethod();

        $this->assertCount(1, $metadata);

        $depends = $metadata->asArray()[0];

        assert($depends instanceof DependsOnMethod);

        $this->assertSame(DependencyTest::class, $depends->className());
        $this->assertSame('testOne', $depends->methodName());
        $this->assertTrue($depends->deepClone());
        $this->assertFalse($depends->shallowClone());
    }

    #[TestDox('Parses #[DependsUsingShallowClone] attribute on method')]
    public function test_parses_DependsUsingShallowClone_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(DependencyTest::class, 'testThree')->isDependsOnMethod();

        $this->assertCount(1, $metadata);

        $depends = $metadata->asArray()[0];

        assert($depends instanceof DependsOnMethod);

        $this->assertSame(DependencyTest::class, $depends->className());
        $this->assertSame('testOne', $depends->methodName());
        $this->assertFalse($depends->deepClone());
        $this->assertTrue($depends->shallowClone());
    }

    #[TestDox('Parses #[DependsExternal] attribute on method')]
    public function test_parses_DependsExternal_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(DependencyTest::class, 'testFour')->isDependsOnMethod();

        $this->assertCount(1, $metadata);

        $depends = $metadata->asArray()[0];

        assert($depends instanceof DependsOnMethod);

        $this->assertSame(AnotherTest::class, $depends->className());
        $this->assertSame('testOne', $depends->methodName());
        $this->assertFalse($depends->deepClone());
        $this->assertFalse($depends->shallowClone());
    }

    #[TestDox('Parses #[DependsExternalUsingDeepClone] attribute on method')]
    public function test_parses_DependsExternalUsingDeepClone_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(DependencyTest::class, 'testFive')->isDependsOnMethod();

        $this->assertCount(1, $metadata);

        $depends = $metadata->asArray()[0];

        assert($depends instanceof DependsOnMethod);

        $this->assertSame(AnotherTest::class, $depends->className());
        $this->assertSame('testOne', $depends->methodName());
        $this->assertTrue($depends->deepClone());
        $this->assertFalse($depends->shallowClone());
    }

    #[TestDox('Parses #[DependsExternalUsingShallowClone] attribute on method')]
    public function test_parses_DependsExternalUsingShallowClone_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(DependencyTest::class, 'testSix')->isDependsOnMethod();

        $this->assertCount(1, $metadata);

        $depends = $metadata->asArray()[0];

        assert($depends instanceof DependsOnMethod);

        $this->assertSame(AnotherTest::class, $depends->className());
        $this->assertSame('testOne', $depends->methodName());
        $this->assertFalse($depends->deepClone());
        $this->assertTrue($depends->shallowClone());
    }

    #[TestDox('Parses #[DependsOnClass] attribute on method')]
    public function test_parses_DependsOnClass_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(DependencyTest::class, 'testSeven')->isDependsOnClass();

        $this->assertCount(1, $metadata);

        $depends = $metadata->asArray()[0];

        assert($depends instanceof DependsOnClass);

        $this->assertSame(AnotherTest::class, $depends->className());
        $this->assertFalse($depends->deepClone());
        $this->assertFalse($depends->shallowClone());
    }

    #[TestDox('Parses #[DependsOnClassUsingDeepClone] attribute on method')]
    public function test_parses_DependsOnClassUsingDeepClone_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(DependencyTest::class, 'testEight')->isDependsOnClass();

        $this->assertCount(1, $metadata);

        $depends = $metadata->asArray()[0];

        assert($depends instanceof DependsOnClass);

        $this->assertSame(AnotherTest::class, $depends->className());
        $this->assertTrue($depends->deepClone());
        $this->assertFalse($depends->shallowClone());
    }

    #[TestDox('Parses #[DependsOnClassUsingShallowClone] attribute on method')]
    public function test_parses_DependsOnClassUsingShallowClone_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(DependencyTest::class, 'testNine')->isDependsOnClass();

        $this->assertCount(1, $metadata);

        $depends = $metadata->asArray()[0];

        assert($depends instanceof DependsOnClass);

        $this->assertSame(AnotherTest::class, $depends->className());
        $this->assertFalse($depends->deepClone());
        $this->assertTrue($depends->shallowClone());
    }

    #[TestDox('Parses #[DoesNotPerformAssertions] attribute on method')]
    public function test_parses_DoesNotPerformAssertions_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(DoesNotPerformAssertionsTest::class, 'testOne')->isDoesNotPerformAssertions();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isDoesNotPerformAssertions());
    }

    #[TestDox('Parses #[ExcludeGlobalVariableFromBackup] attribute on method')]
    public function test_parses_ExcludeGlobalVariableFromBackup_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(BackupGlobalsTest::class, 'testOne')->isExcludeGlobalVariableFromBackup();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isExcludeGlobalVariableFromBackup());
        $this->assertSame('bar', $metadata->asArray()[0]->globalVariableName());
    }

    #[TestDox('Parses #[ExcludeStaticPropertyFromBackup] attribute on method')]
    public function test_parses_ExcludeStaticPropertyFromBackup_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(BackupStaticPropertiesTest::class, 'testOne')->isExcludeStaticPropertyFromBackup();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isExcludeStaticPropertyFromBackup());
        $this->assertSame('anotherClassName', $metadata->asArray()[0]->className());
        $this->assertSame('propertyName', $metadata->asArray()[0]->propertyName());
    }

    #[TestDox('Parses #[Group] attribute on method')]
    public function test_parses_Group_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(GroupTest::class, 'testOne')->isGroup();

        $this->assertCount(2, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('another-group', $metadata->asArray()[0]->groupName());
    }

    #[TestDox('Parses #[IgnoreDeprecations] attribute on method')]
    public function test_parses_IgnoreDeprecations_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(IgnoreDeprecationsMethodTest::class, 'testOne')->isIgnoreDeprecations();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isIgnoreDeprecations());
    }

    #[TestDox('Parses #[IgnorePhpunitDeprecations] attribute on method')]
    public function test_parses_IgnorePhpunitDeprecations_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(IgnorePhpunitDeprecationsMethodTest::class, 'testOne')->isIgnorePhpunitDeprecations();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isIgnorePhpunitDeprecations());
    }

    #[TestDox('Parses #[PostCondition] attribute on method')]
    public function test_parses_PostCondition_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(SmallTest::class, 'postCondition')->isPostCondition();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isPostCondition());
    }

    #[TestDox('Parses #[PreCondition] attribute on method')]
    public function test_parses_PreCondition_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(SmallTest::class, 'preCondition')->isPreCondition();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isPreCondition());
    }

    #[TestDox('Parses #[PreserveGlobalState] attribute on method')]
    public function test_parses_PreserveGlobalState_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(PreserveGlobalStateTest::class, 'testOne')->isPreserveGlobalState();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isPreserveGlobalState());
        $this->assertFalse($metadata->asArray()[0]->enabled());
    }

    #[TestDox('Parses #[RequiresMethod] attribute on method')]
    public function test_parses_RequiresMethod_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(RequiresMethodTest::class, 'testOne')->isRequiresMethod();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresMethod());
        $this->assertSame('AnotherClassName', $metadata->asArray()[0]->className());
        $this->assertSame('anotherMethodName', $metadata->asArray()[0]->methodName());
    }

    #[TestDox('Parses #[RequiresFunction] attribute on method')]
    public function test_parses_RequiresFunction_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(RequiresFunctionTest::class, 'testOne')->isRequiresFunction();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresFunction());
        $this->assertSame('g', $metadata->asArray()[0]->functionName());
    }

    #[TestDox('Parses #[RequiresOperatingSystem] attribute on method')]
    public function test_parses_RequiresOperatingSystem_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(RequiresOperatingSystemTest::class, 'testOne')->isRequiresOperatingSystem();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresOperatingSystem());
        $this->assertSame('Linux', $metadata->asArray()[0]->operatingSystem());
    }

    #[TestDox('Parses #[RequiresOperatingSystemFamily] attribute on method')]
    public function test_parses_RequiresOperatingSystemFamily_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(RequiresOperatingSystemFamilyTest::class, 'testOne')->isRequiresOperatingSystemFamily();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresOperatingSystemFamily());
        $this->assertSame('Linux', $metadata->asArray()[0]->operatingSystemFamily());
    }

    #[TestDox('Parses #[RequiresPhp] attribute on method')]
    public function test_parses_RequiresPhp_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(RequiresPhpTest::class, 'testOne')->isRequiresPhp();

        $this->assertCount(1, $metadata);

        $requirement = $metadata->asArray()[0];

        $this->assertTrue($requirement->isRequiresPhp());

        assert($requirement instanceof RequiresPhp);

        $versionRequirement = $requirement->versionRequirement();

        assert($versionRequirement instanceof ConstraintRequirement);

        $this->assertSame('^8.0', $versionRequirement->asString());
    }

    #[TestDox('Parses #[RequiresPhpExtension] attribute on method')]
    public function test_parses_RequiresPhpExtension_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(RequiresPhpExtensionTest::class, 'testOne')->isRequiresPhpExtension();

        $this->assertCount(1, $metadata);

        $requirement = $metadata->asArray()[0];

        $this->assertTrue($requirement->isRequiresPhpExtension());

        assert($requirement instanceof RequiresPhpExtension);

        $this->assertSame('bar', $requirement->extension());
        $this->assertTrue($requirement->hasVersionRequirement());

        $versionRequirement = $requirement->versionRequirement();

        assert($versionRequirement instanceof ComparisonRequirement);

        $this->assertSame('>= 2.0', $versionRequirement->asString());

        $metadata = $this->parser()->forMethod(RequiresPhpExtensionTest::class, 'testTwo');

        $this->assertCount(1, $metadata);

        $requirement = $metadata->asArray()[0];

        $this->assertTrue($requirement->isRequiresPhpExtension());

        assert($requirement instanceof RequiresPhpExtension);

        $this->assertSame('baz', $requirement->extension());
        $this->assertTrue($requirement->hasVersionRequirement());

        $versionRequirement = $requirement->versionRequirement();

        assert($versionRequirement instanceof ConstraintRequirement);

        $this->assertSame('^1.0', $versionRequirement->asString());
    }

    #[TestDox('Parses #[RequiresPhpunit] attribute on method')]
    public function test_parses_RequiresPhpunit_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(RequiresPhpunitTest::class, 'testOne')->isRequiresPhpunit();

        $this->assertCount(1, $metadata);

        $requirement = $metadata->asArray()[0];

        $this->assertTrue($requirement->isRequiresPhpunit());

        assert($requirement instanceof RequiresPhpunit);

        $versionRequirement = $requirement->versionRequirement();

        assert($versionRequirement instanceof ConstraintRequirement);

        $this->assertSame('^10.0', $versionRequirement->asString());
    }

    #[TestDox('Parses #[RequiresPhpunitExtension] attribute on method')]
    public function test_parses_RequiresPhpunitExtension_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(RequiresPhpunitExtensionTest::class, 'testOne')->isRequiresPhpunitExtension();

        $this->assertCount(2, $metadata);

        $requirement = $metadata->asArray()[0];
        $this->assertTrue($requirement->isRequiresPhpunitExtension());
        assert($requirement instanceof RequiresPhpunitExtension);
        $this->assertSame('PHPUnit\TestFixture\Metadata\Attribute\SomeExtension', $requirement->extensionClass());

        $requirement = $metadata->asArray()[1];
        $this->assertTrue($requirement->isRequiresPhpunitExtension());
        assert($requirement instanceof RequiresPhpunitExtension);
        $this->assertSame('PHPUnit\TestFixture\Metadata\Attribute\SomeOtherExtension', $requirement->extensionClass());
    }

    #[TestDox('Parses #[RequiresSetting] attribute on method')]
    public function test_parses_RequiresSetting_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(RequiresSettingTest::class, 'testOne')->isRequiresSetting();

        $this->assertCount(1, $metadata);

        $requirement = $metadata->asArray()[0];

        $this->assertTrue($requirement->isRequiresSetting());

        assert($requirement instanceof RequiresSetting);

        $this->assertSame('another-setting', $requirement->setting());
        $this->assertSame('another-value', $requirement->value());
    }

    #[TestDox('Parses #[RunInSeparateProcess] attribute on method')]
    public function test_parses_RunInSeparateProcess_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(ProcessIsolationTest::class, 'testOne')->isRunInSeparateProcess();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRunInSeparateProcess());
    }

    #[TestDox('Parses #[Test] attribute on method')]
    public function test_parses_Test_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(SmallTest::class, 'one')->isTest();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isTest());
    }

    #[TestDox('Parses #[TestDox] attribute on method')]
    public function test_parses_TestDox_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(TestDoxTest::class, 'testOne')->isTestDox();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isTestDox());
        $this->assertSame('text', $metadata->asArray()[0]->text());
    }

    #[TestDox('Parses #[TestWith] attribute on method')]
    public function test_parses_TestWith_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(TestWithTest::class, 'testOne')->isTestWith();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isTestWith());
        $this->assertSame([1, 2, 3], $metadata->asArray()[0]->data());
        $this->assertFalse($metadata->asArray()[0]->hasName());
        $this->assertNull($metadata->asArray()[0]->name());
    }

    #[TestDox('Parses #[TestWith] attribute with name on method')]
    public function test_parses_TestWith_attribute_with_name_on_method(): void
    {
        $metadata = $this->parser()->forMethod(TestWithTest::class, 'testOneWithName')->isTestWith();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isTestWith());
        $this->assertSame([1, 2, 3], $metadata->asArray()[0]->data());
        $this->assertTrue($metadata->asArray()[0]->hasName());
        $this->assertSame('Name1', $metadata->asArray()[0]->name());
    }

    #[TestDox('Parses #[TestWithJson] attribute on method')]
    public function test_parses_TestWithJson_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(TestWithTest::class, 'testTwo')->isTestWith();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isTestWith());
        $this->assertSame([1, 2, 3], $metadata->asArray()[0]->data());
        $this->assertFalse($metadata->asArray()[0]->hasName());
        $this->assertNull($metadata->asArray()[0]->name());
    }

    #[TestDox('Parses #[TestWithJson] attribute with name on method')]
    public function test_parses_TestWithJson_attribute_with_name_on_method(): void
    {
        $metadata = $this->parser()->forMethod(TestWithTest::class, 'testTwoWithName')->isTestWith();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isTestWith());
        $this->assertSame([1, 2, 3], $metadata->asArray()[0]->data());
        $this->assertTrue($metadata->asArray()[0]->hasName());
        $this->assertSame('Name2', $metadata->asArray()[0]->name());
    }

    #[TestDox('Parses #[Ticket] attribute on method')]
    public function test_parses_Ticket_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(GroupTest::class, 'testOne')->isGroup();

        $this->assertCount(2, $metadata);
        $this->assertTrue($metadata->asArray()[1]->isGroup());
        $this->assertSame('another-ticket', $metadata->asArray()[1]->groupName());
    }

    #[TestDox('Parses #[WithoutErrorHandler] attribute on method')]
    public function test_parses_WithoutErrorHandler_attribute_on_method(): void
    {
        $metadata = $this->parser()->forMethod(WithoutErrorHandlerTest::class, 'testOne')->isWithoutErrorHandler();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isWithoutErrorHandler());
    }

    public function test_parses_attributes_for_class_and_method(): void
    {
        $metadata = $this->parser()->forClassAndMethod(CoversTest::class, 'testOne');

        $this->assertCount(1, $metadata->isCoversClass());
        $this->assertCount(1, $metadata->isCoversFunction());
        $this->assertCount(2, $metadata->isCoversNothing());
    }

    public function test_ignores_attributes_not_owned_by_PHPUnit(): void
    {
        $metadata = $this->parser()->forClassAndMethod(NonPhpunitAttributeTest::class, 'testOne');

        $this->assertTrue($metadata->isEmpty());
    }

    public function test_ignores_attributes_in_PHPUnit_namespace_that_do_not_exist(): void
    {
        $metadata = $this->parser()->forClassAndMethod(PhpunitAttributeThatDoesNotExistTest::class, 'testOne');

        $this->assertTrue($metadata->isEmpty());
    }

    public function test_handles_ReflectionException_raised_when_instantiating_attribute_on_class(): void
    {
        $this->expectException(InvalidAttributeException::class);

        $this->parser()->forClass(DuplicateSmallAttributeTest::class);
    }

    public function test_handles_ReflectionException_raised_when_instantiating_attribute_on_method(): void
    {
        $this->expectException(InvalidAttributeException::class);

        $this->parser()->forMethod(DuplicateTestAttributeTest::class, 'testOne');
    }

    abstract protected function parser(): Parser;
}
