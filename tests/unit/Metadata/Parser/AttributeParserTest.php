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
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\AfterClass;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\BackupStaticProperties;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\BeforeClass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsExternal;
use PHPUnit\Framework\Attributes\DependsExternalUsingDeepClone;
use PHPUnit\Framework\Attributes\DependsExternalUsingShallowClone;
use PHPUnit\Framework\Attributes\DependsOnClassUsingDeepClone;
use PHPUnit\Framework\Attributes\DependsOnClassUsingShallowClone;
use PHPUnit\Framework\Attributes\DependsUsingDeepClone;
use PHPUnit\Framework\Attributes\DependsUsingShallowClone;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\PostCondition;
use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RequiresFunction;
use PHPUnit\Framework\Attributes\RequiresOperatingSystem;
use PHPUnit\Framework\Attributes\RequiresOperatingSystemFamily;
use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\RequiresPhpunit;
use PHPUnit\Framework\Attributes\RunClassInSeparateProcess;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\Attributes\TestWithJson;
use PHPUnit\Framework\Attributes\Ticket;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\UsesFunction;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\Parser\AttributeParser;
use PHPUnit\Metadata\Version\ComparisonRequirement;
use PHPUnit\Metadata\Version\ConstraintRequirement;
use PHPUnit\TestFixture\Metadata\Attribute\AnotherTest;
use PHPUnit\TestFixture\Metadata\Attribute\BackupGlobalsTest;
use PHPUnit\TestFixture\Metadata\Attribute\BackupStaticPropertiesTest;
use PHPUnit\TestFixture\Metadata\Attribute\CoversTest;
use PHPUnit\TestFixture\Metadata\Attribute\DependencyTest;
use PHPUnit\TestFixture\Metadata\Attribute\DoesNotPerformAssertionsTest;
use PHPUnit\TestFixture\Metadata\Attribute\Example;
use PHPUnit\TestFixture\Metadata\Attribute\GroupTest;
use PHPUnit\TestFixture\Metadata\Attribute\LargeTest;
use PHPUnit\TestFixture\Metadata\Attribute\MediumTest;
use PHPUnit\TestFixture\Metadata\Attribute\NonPhpunitAttributeTest;
use PHPUnit\TestFixture\Metadata\Attribute\PreserveGlobalStateTest;
use PHPUnit\TestFixture\Metadata\Attribute\ProcessIsolationTest;
use PHPUnit\TestFixture\Metadata\Attribute\RequiresFunctionTest;
use PHPUnit\TestFixture\Metadata\Attribute\RequiresMethodTest;
use PHPUnit\TestFixture\Metadata\Attribute\RequiresOperatingSystemFamilyTest;
use PHPUnit\TestFixture\Metadata\Attribute\RequiresOperatingSystemTest;
use PHPUnit\TestFixture\Metadata\Attribute\RequiresPhpExtensionTest;
use PHPUnit\TestFixture\Metadata\Attribute\RequiresPhpTest;
use PHPUnit\TestFixture\Metadata\Attribute\RequiresPhpunitTest;
use PHPUnit\TestFixture\Metadata\Attribute\RequiresSettingTest;
use PHPUnit\TestFixture\Metadata\Attribute\SmallTest;
use PHPUnit\TestFixture\Metadata\Attribute\TestDoxTest;
use PHPUnit\TestFixture\Metadata\Attribute\TestWithTest;
use PHPUnit\TestFixture\Metadata\Attribute\UsesTest;

#[CoversClass(After::class)]
#[CoversClass(AfterClass::class)]
#[CoversClass(BackupGlobals::class)]
#[CoversClass(BackupStaticProperties::class)]
#[CoversClass(Before::class)]
#[CoversClass(BeforeClass::class)]
#[CoversClass(CoversClass::class)]
#[CoversClass(CoversFunction::class)]
#[CoversClass(CoversNothing::class)]
#[CoversClass(DataProvider::class)]
#[CoversClass(DataProviderExternal::class)]
#[CoversClass(Depends::class)]
#[CoversClass(DependsUsingDeepClone::class)]
#[CoversClass(DependsUsingShallowClone::class)]
#[CoversClass(DependsOnClass::class)]
#[CoversClass(DependsOnClassUsingDeepClone::class)]
#[CoversClass(DependsOnClassUsingShallowClone::class)]
#[CoversClass(DependsExternal::class)]
#[CoversClass(DependsExternalUsingDeepClone::class)]
#[CoversClass(DependsExternalUsingShallowClone::class)]
#[CoversClass(DoesNotPerformAssertions::class)]
#[CoversClass(ExcludeGlobalVariableFromBackup::class)]
#[CoversClass(ExcludeStaticPropertyFromBackup::class)]
#[CoversClass(Group::class)]
#[CoversClass(Large::class)]
#[CoversClass(Medium::class)]
#[CoversClass(PostCondition::class)]
#[CoversClass(PreCondition::class)]
#[CoversClass(PreserveGlobalState::class)]
#[CoversClass(RequiresFunction::class)]
#[CoversClass(RequiresOperatingSystem::class)]
#[CoversClass(RequiresOperatingSystemFamily::class)]
#[CoversClass(RequiresPhp::class)]
#[CoversClass(RequiresPhpExtension::class)]
#[CoversClass(RequiresPhpunit::class)]
#[CoversClass(RequiresSetting::class)]
#[CoversClass(RunClassInSeparateProcess::class)]
#[CoversClass(RunInSeparateProcess::class)]
#[CoversClass(RunTestsInSeparateProcesses::class)]
#[CoversClass(Small::class)]
#[CoversClass(Test::class)]
#[CoversClass(TestDox::class)]
#[CoversClass(TestWith::class)]
#[CoversClass(TestWithJson::class)]
#[CoversClass(Ticket::class)]
#[CoversClass(UsesClass::class)]
#[CoversClass(UsesFunction::class)]
#[CoversClass(AttributeParser::class)]
#[Small]
final class AttributeParserTest extends TestCase
{
    #[TestDox('Parses #[BackupGlobals] attribute on class')]
    public function test_parses_BackupGlobals_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(BackupGlobalsTest::class)->isBackupGlobals();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBackupGlobals());
        $this->assertTrue($metadata->asArray()[0]->enabled());
    }

    #[TestDox('Parses #[BackupStaticProperties] attribute on class')]
    public function test_parses_BackupStaticProperties_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(BackupStaticPropertiesTest::class)->isBackupStaticProperties();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBackupStaticProperties());
        $this->assertTrue($metadata->asArray()[0]->enabled());
    }

    #[TestDox('Parses #[CoversClass] attribute on class')]
    public function test_parses_CoversClass_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(CoversTest::class)->isCoversClass();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isCoversClass());
        $this->assertSame(Example::class, $metadata->asArray()[0]->className());
    }

    #[TestDox('Parses #[CoversFunction] attribute on class')]
    public function test_parses_CoversFunction_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(CoversTest::class)->isCoversFunction();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isCoversFunction());
        $this->assertSame('f', $metadata->asArray()[0]->functionName());
    }

    #[TestDox('Parses #[CoversNothing] attribute on class')]
    public function test_parses_CoversNothing_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(CoversTest::class)->isCoversNothing();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isCoversNothing());
    }

    #[TestDox('Parses #[DoesNotPerformAssertions] attribute on class')]
    public function test_parses_DoesNotPerformAssertions_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(DoesNotPerformAssertionsTest::class)->isDoesNotPerformAssertions();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isDoesNotPerformAssertions());
    }

    #[TestDox('Parses #[ExcludeGlobalVariableFromBackup] attribute on class')]
    public function test_parses_ExcludeGlobalVariableFromBackup_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(BackupGlobalsTest::class)->isExcludeGlobalVariableFromBackup();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isExcludeGlobalVariableFromBackup());
        $this->assertSame('foo', $metadata->asArray()[0]->globalVariableName());
    }

    #[TestDox('Parses #[ExcludeStaticPropertyFromBackup] attribute on class')]
    public function test_parses_ExcludeStaticPropertyFromBackup_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(BackupStaticPropertiesTest::class)->isExcludeStaticPropertyFromBackup();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isExcludeStaticPropertyFromBackup());
        $this->assertSame('className', $metadata->asArray()[0]->className());
        $this->assertSame('propertyName', $metadata->asArray()[0]->propertyName());
    }

    #[TestDox('Parses #[Group] attribute on class')]
    public function test_parses_Group_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(GroupTest::class)->isGroup();

        $this->assertCount(2, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('group', $metadata->asArray()[0]->groupName());
    }

    #[TestDox('Parses #[Large] attribute on class')]
    public function test_parses_Large_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(LargeTest::class)->isGroup();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('large', $metadata->asArray()[0]->groupName());
    }

    #[TestDox('Parses #[Medium] attribute on class')]
    public function test_parses_Medium_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(MediumTest::class)->isGroup();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('medium', $metadata->asArray()[0]->groupName());
    }

    #[TestDox('Parses #[PreserveGlobalState] attribute on class')]
    public function test_parses_PreserveGlobalState_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(PreserveGlobalStateTest::class)->isPreserveGlobalState();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isPreserveGlobalState());
        $this->assertTrue($metadata->asArray()[0]->enabled());
    }

    #[TestDox('Parses #[RequiresMethod] attribute on class')]
    public function test_parses_RequiresMethod_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(RequiresMethodTest::class)->isRequiresMethod();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresMethod());
        $this->assertSame('ClassName', $metadata->asArray()[0]->className());
        $this->assertSame('methodName', $metadata->asArray()[0]->methodName());
    }

    #[TestDox('Parses #[RequiresFunction] attribute on class')]
    public function test_parses_RequiresFunction_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(RequiresFunctionTest::class)->isRequiresFunction();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresFunction());
        $this->assertSame('f', $metadata->asArray()[0]->functionName());
    }

    #[TestDox('Parses #[RequiresOperatingSystem] attribute on class')]
    public function test_parses_RequiresOperatingSystem_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(RequiresOperatingSystemTest::class)->isRequiresOperatingSystem();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresOperatingSystem());
        $this->assertSame('Linux', $metadata->asArray()[0]->operatingSystem());
    }

    #[TestDox('Parses #[RequiresOperatingSystemFamily] attribute on class')]
    public function test_parses_RequiresOperatingSystemFamily_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(RequiresOperatingSystemFamilyTest::class)->isRequiresOperatingSystemFamily();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresOperatingSystemFamily());
        $this->assertSame('Linux', $metadata->asArray()[0]->operatingSystemFamily());
    }

    #[TestDox('Parses #[RequiresPhp] attribute on class')]
    public function test_parses_RequiresPhp_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(RequiresPhpTest::class)->isRequiresPhp();

        $this->assertCount(1, $metadata);

        $requirement = $metadata->asArray()[0];

        $this->assertTrue($requirement->isRequiresPhp());

        assert($requirement instanceof \PHPUnit\Metadata\RequiresPhp);

        $versionRequirement = $requirement->versionRequirement();

        assert($versionRequirement instanceof ConstraintRequirement);

        $this->assertSame('8.0.0', $versionRequirement->asString());
    }

    #[TestDox('Parses #[RequiresPhpExtension] attribute on class')]
    public function test_parses_RequiresPhpExtension_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(RequiresPhpExtensionTest::class)->isRequiresPhpExtension();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresPhpExtension());
        $this->assertSame('foo', $metadata->asArray()[0]->extension());
        $this->assertTrue($metadata->asArray()[0]->hasVersionRequirement());
        $this->assertSame('>= 1.0', $metadata->asArray()[0]->versionRequirement()->asString());
    }

    #[TestDox('Parses #[RequiresPhpunit] attribute on class')]
    public function test_parses_RequiresPhpunit_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(RequiresPhpunitTest::class)->isRequiresPhpunit();

        $this->assertCount(1, $metadata);

        $requirement = $metadata->asArray()[0];

        $this->assertTrue($requirement->isRequiresPhpunit());

        assert($requirement instanceof \PHPUnit\Metadata\RequiresPhpunit);

        $versionRequirement = $requirement->versionRequirement();

        assert($versionRequirement instanceof ComparisonRequirement);

        $this->assertSame('>= 10.0.0', $versionRequirement->asString());
    }

    #[TestDox('Parses #[RequiresSetting] attribute on class')]
    public function test_parses_RequiresSetting_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(RequiresSettingTest::class)->isRequiresSetting();

        $this->assertCount(1, $metadata);

        $requirement = $metadata->asArray()[0];

        $this->assertTrue($requirement->isRequiresSetting());

        assert($requirement instanceof \PHPUnit\Metadata\RequiresSetting);

        $this->assertSame('setting', $requirement->setting());
        $this->assertSame('value', $requirement->value());
    }

    #[TestDox('Parses #[RunClassInSeparateProcess] attribute on class')]
    public function test_parses_RunClassInSeparateProcess_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(ProcessIsolationTest::class)->isRunClassInSeparateProcess();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRunClassInSeparateProcess());
    }

    #[TestDox('Parses #[RunTestsInSeparateProcesses] attribute on class')]
    public function test_parses_RunTestsInSeparateProcesses_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(ProcessIsolationTest::class)->isRunTestsInSeparateProcesses();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRunTestsInSeparateProcesses());
    }

    #[TestDox('Parses #[Small] attribute on class')]
    public function test_parses_Small_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(SmallTest::class)->isGroup();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('small', $metadata->asArray()[0]->groupName());
    }

    #[TestDox('Parses #[TestDox] attribute on class')]
    public function test_parses_TestDox_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(TestDoxTest::class)->isTestDox();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isTestDox());
        $this->assertSame('text', $metadata->asArray()[0]->text());
    }

    #[TestDox('Parses #[Ticket] attribute on class')]
    public function test_parses_Ticket_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(GroupTest::class)->isGroup();

        $this->assertCount(2, $metadata);
        $this->assertTrue($metadata->asArray()[1]->isGroup());
        $this->assertSame('ticket', $metadata->asArray()[1]->groupName());
    }

    #[TestDox('Parses #[UsesClass] attribute on class')]
    public function test_parses_UsesClass_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(UsesTest::class)->isUsesClass();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isUsesClass());
        $this->assertSame(Example::class, $metadata->asArray()[0]->className());
    }

    #[TestDox('Parses #[UsesFunction] attribute on class')]
    public function test_parses_UsesFunction_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(UsesTest::class)->isUsesFunction();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isUsesFunction());
        $this->assertSame('f', $metadata->asArray()[0]->functionName());
    }

    #[TestDox('Parses #[After] attribute on class')]
    public function test_parses_After_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(SmallTest::class, 'afterTest')->isAfter();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isAfter());
    }

    #[TestDox('Parses #[AfterClass] attribute on class')]
    public function test_parses_AfterClass_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(SmallTest::class, 'afterTests')->isAfterClass();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isAfterClass());
    }

    #[TestDox('Parses #[BackupGlobals] attribute on method')]
    public function test_parses_BackupGlobals_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(BackupGlobalsTest::class, 'testOne')->isBackupGlobals();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBackupGlobals());
        $this->assertFalse($metadata->asArray()[0]->enabled());
    }

    #[TestDox('Parses #[BackupStaticProperties] attribute on method')]
    public function test_parses_BackupStaticProperties_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(BackupStaticPropertiesTest::class, 'testOne')->isBackupStaticProperties();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBackupStaticProperties());
        $this->assertFalse($metadata->asArray()[0]->enabled());
    }

    #[TestDox('Parses #[Before] attribute on method')]
    public function test_parses_Before_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(SmallTest::class, 'beforeTest')->isBefore();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBefore());
    }

    #[TestDox('Parses #[BeforeClass] attribute on method')]
    public function test_parses_BeforeClass_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(SmallTest::class, 'beforeTests')->isBeforeClass();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBeforeClass());
    }

    #[TestDox('Parses #[CoversNothing] attribute on method')]
    public function test_parses_CoversNothing_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(CoversTest::class, 'testOne')->isCoversNothing();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isCoversNothing());
    }

    #[TestDox('Parses #[DataProvider] attribute on method')]
    public function test_parses_DataProvider_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(SmallTest::class, 'testWithDataProvider')->isDataProvider();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isDataProvider());
        $this->assertSame(SmallTest::class, $metadata->asArray()[0]->className());
        $this->assertSame('provider', $metadata->asArray()[0]->methodName());
    }

    #[TestDox('Parses #[DataProviderExternal] attribute on method')]
    public function test_parses_DataProviderExternal_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(SmallTest::class, 'testWithDataProviderExternal')->isDataProvider();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isDataProvider());
        $this->assertSame(SmallTest::class, $metadata->asArray()[0]->className());
        $this->assertSame('provider', $metadata->asArray()[0]->methodName());
    }

    #[TestDox('Parses #[Depends] attribute on method')]
    public function test_parses_Depends_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(DependencyTest::class, 'testOne')->isDependsOnMethod();

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
        $metadata = (new AttributeParser)->forMethod(DependencyTest::class, 'testTwo')->isDependsOnMethod();

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
        $metadata = (new AttributeParser)->forMethod(DependencyTest::class, 'testThree')->isDependsOnMethod();

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
        $metadata = (new AttributeParser)->forMethod(DependencyTest::class, 'testFour')->isDependsOnMethod();

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
        $metadata = (new AttributeParser)->forMethod(DependencyTest::class, 'testFive')->isDependsOnMethod();

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
        $metadata = (new AttributeParser)->forMethod(DependencyTest::class, 'testSix')->isDependsOnMethod();

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
        $metadata = (new AttributeParser)->forMethod(DependencyTest::class, 'testSeven')->isDependsOnClass();

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
        $metadata = (new AttributeParser)->forMethod(DependencyTest::class, 'testEight')->isDependsOnClass();

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
        $metadata = (new AttributeParser)->forMethod(DependencyTest::class, 'testNine')->isDependsOnClass();

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
        $metadata = (new AttributeParser)->forMethod(DoesNotPerformAssertionsTest::class, 'testOne')->isDoesNotPerformAssertions();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isDoesNotPerformAssertions());
    }

    #[TestDox('Parses #[ExcludeGlobalVariableFromBackup] attribute on method')]
    public function test_parses_ExcludeGlobalVariableFromBackup_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(BackupGlobalsTest::class, 'testOne')->isExcludeGlobalVariableFromBackup();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isExcludeGlobalVariableFromBackup());
        $this->assertSame('bar', $metadata->asArray()[0]->globalVariableName());
    }

    #[TestDox('Parses #[ExcludeStaticPropertyFromBackup] attribute on method')]
    public function test_parses_ExcludeStaticPropertyFromBackup_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(BackupStaticPropertiesTest::class, 'testOne')->isExcludeStaticPropertyFromBackup();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isExcludeStaticPropertyFromBackup());
        $this->assertSame('anotherClassName', $metadata->asArray()[0]->className());
        $this->assertSame('propertyName', $metadata->asArray()[0]->propertyName());
    }

    #[TestDox('Parses #[Group] attribute on method')]
    public function test_parses_Group_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(GroupTest::class, 'testOne')->isGroup();

        $this->assertCount(2, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('another-group', $metadata->asArray()[0]->groupName());
    }

    #[TestDox('Parses #[PostCondition] attribute on method')]
    public function test_parses_PostCondition_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(SmallTest::class, 'postCondition')->isPostCondition();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isPostCondition());
    }

    #[TestDox('Parses #[PreCondition] attribute on method')]
    public function test_parses_PreCondition_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(SmallTest::class, 'preCondition')->isPreCondition();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isPreCondition());
    }

    #[TestDox('Parses #[PreserveGlobalState] attribute on method')]
    public function test_parses_PreserveGlobalState_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(PreserveGlobalStateTest::class, 'testOne')->isPreserveGlobalState();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isPreserveGlobalState());
        $this->assertFalse($metadata->asArray()[0]->enabled());
    }

    #[TestDox('Parses #[RequiresMethod] attribute on method')]
    public function test_parses_RequiresMethod_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(RequiresMethodTest::class, 'testOne')->isRequiresMethod();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresMethod());
        $this->assertSame('AnotherClassName', $metadata->asArray()[0]->className());
        $this->assertSame('anotherMethodName', $metadata->asArray()[0]->methodName());
    }

    #[TestDox('Parses #[RequiresFunction] attribute on method')]
    public function test_parses_RequiresFunction_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(RequiresFunctionTest::class, 'testOne')->isRequiresFunction();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresFunction());
        $this->assertSame('g', $metadata->asArray()[0]->functionName());
    }

    #[TestDox('Parses #[RequiresOperatingSystem] attribute on method')]
    public function test_parses_RequiresOperatingSystem_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(RequiresOperatingSystemTest::class, 'testOne')->isRequiresOperatingSystem();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresOperatingSystem());
        $this->assertSame('Linux', $metadata->asArray()[0]->operatingSystem());
    }

    #[TestDox('Parses #[RequiresOperatingSystemFamily] attribute on method')]
    public function test_parses_RequiresOperatingSystemFamily_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(RequiresOperatingSystemFamilyTest::class, 'testOne')->isRequiresOperatingSystemFamily();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresOperatingSystemFamily());
        $this->assertSame('Linux', $metadata->asArray()[0]->operatingSystemFamily());
    }

    #[TestDox('Parses #[RequiresPhp] attribute on method')]
    public function test_parses_RequiresPhp_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(RequiresPhpTest::class, 'testOne')->isRequiresPhp();

        $this->assertCount(1, $metadata);

        $requirement = $metadata->asArray()[0];

        $this->assertTrue($requirement->isRequiresPhp());

        assert($requirement instanceof \PHPUnit\Metadata\RequiresPhp);

        $versionRequirement = $requirement->versionRequirement();

        assert($versionRequirement instanceof ConstraintRequirement);

        $this->assertSame('^8.0', $versionRequirement->asString());
    }

    #[TestDox('Parses #[RequiresPhpExtension] attribute on method')]
    public function test_parses_RequiresPhpExtension_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(RequiresPhpExtensionTest::class, 'testOne')->isRequiresPhpExtension();

        $this->assertCount(1, $metadata);

        $requirement = $metadata->asArray()[0];

        $this->assertTrue($requirement->isRequiresPhpExtension());

        assert($requirement instanceof \PHPUnit\Metadata\RequiresPhpExtension);

        $this->assertSame('bar', $requirement->extension());
        $this->assertTrue($requirement->hasVersionRequirement());

        $versionRequirement = $requirement->versionRequirement();

        assert($versionRequirement instanceof ComparisonRequirement);

        $this->assertSame('>= 2.0', $versionRequirement->asString());

        $metadata = (new AttributeParser)->forMethod(RequiresPhpExtensionTest::class, 'testTwo');

        $this->assertCount(1, $metadata);

        $requirement = $metadata->asArray()[0];

        $this->assertTrue($requirement->isRequiresPhpExtension());

        assert($requirement instanceof \PHPUnit\Metadata\RequiresPhpExtension);

        $this->assertSame('baz', $requirement->extension());
        $this->assertTrue($requirement->hasVersionRequirement());

        $versionRequirement = $requirement->versionRequirement();

        assert($versionRequirement instanceof ConstraintRequirement);

        $this->assertSame('^1.0', $versionRequirement->asString());
    }

    #[TestDox('Parses #[RequiresPhpunit] attribute on method')]
    public function test_parses_RequiresPhpunit_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(RequiresPhpunitTest::class, 'testOne')->isRequiresPhpunit();

        $this->assertCount(1, $metadata);

        $requirement = $metadata->asArray()[0];

        $this->assertTrue($requirement->isRequiresPhpunit());

        assert($requirement instanceof \PHPUnit\Metadata\RequiresPhpunit);

        $versionRequirement = $requirement->versionRequirement();

        assert($versionRequirement instanceof ConstraintRequirement);

        $this->assertSame('^10.0', $versionRequirement->asString());
    }

    #[TestDox('Parses #[RequiresSetting] attribute on method')]
    public function test_parses_RequiresSetting_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(RequiresSettingTest::class, 'testOne')->isRequiresSetting();

        $this->assertCount(1, $metadata);

        $requirement = $metadata->asArray()[0];

        $this->assertTrue($requirement->isRequiresSetting());

        assert($requirement instanceof \PHPUnit\Metadata\RequiresSetting);

        $this->assertSame('another-setting', $requirement->setting());
        $this->assertSame('another-value', $requirement->value());
    }

    #[TestDox('Parses #[RunInSeparateProcess] attribute on method')]
    public function test_parses_RunInSeparateProcess_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(ProcessIsolationTest::class, 'testOne')->isRunInSeparateProcess();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRunInSeparateProcess());
    }

    #[TestDox('Parses #[Test] attribute on method')]
    public function test_parses_Test_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(SmallTest::class, 'one')->isTest();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isTest());
    }

    #[TestDox('Parses #[TestDox] attribute on method')]
    public function test_parses_TestDox_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(TestDoxTest::class, 'testOne')->isTestDox();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isTestDox());
        $this->assertSame('text', $metadata->asArray()[0]->text());
    }

    #[TestDox('Parses #[TestWith] attribute on method')]
    public function test_parses_TestWith_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(TestWithTest::class, 'testOne')->isTestWith();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isTestWith());
        $this->assertSame([1, 2, 3], $metadata->asArray()[0]->data());
    }

    #[TestDox('Parses #[TestWithJson] attribute on method')]
    public function test_parses_TestWithJson_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(TestWithTest::class, 'testTwo')->isTestWith();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isTestWith());
        $this->assertSame([1, 2, 3], $metadata->asArray()[0]->data());
    }

    #[TestDox('Parses #[Ticket] attribute on method')]
    public function test_parses_Ticket_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(GroupTest::class, 'testOne')->isGroup();

        $this->assertCount(2, $metadata);
        $this->assertTrue($metadata->asArray()[1]->isGroup());
        $this->assertSame('another-ticket', $metadata->asArray()[1]->groupName());
    }

    public function test_parses_attributes_for_class_and_method(): void
    {
        $metadata = (new AttributeParser)->forClassAndMethod(CoversTest::class, 'testOne');

        $this->assertCount(1, $metadata->isCoversClass());
        $this->assertCount(1, $metadata->isCoversFunction());
        $this->assertCount(2, $metadata->isCoversNothing());
    }

    public function test_ignores_attributes_not_owned_by_PHPUnit(): void
    {
        $metadata = (new AttributeParser)->forClassAndMethod(NonPhpunitAttributeTest::class, 'testOne');

        $this->assertTrue($metadata->isEmpty());
    }
}
