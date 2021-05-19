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
use PHPUnit\TestFixture\Metadata\Attribute\PreserveGlobalStateTest;
use PHPUnit\TestFixture\Metadata\Attribute\ProcessIsolationTest;
use PHPUnit\TestFixture\Metadata\Attribute\RequiresFunctionTest;
use PHPUnit\TestFixture\Metadata\Attribute\RequiresOperatingSystemFamilyTest;
use PHPUnit\TestFixture\Metadata\Attribute\RequiresOperatingSystemTest;
use PHPUnit\TestFixture\Metadata\Attribute\RequiresPhpExtensionTest;
use PHPUnit\TestFixture\Metadata\Attribute\RequiresPhpTest;
use PHPUnit\TestFixture\Metadata\Attribute\RequiresPhpunitTest;
use PHPUnit\TestFixture\Metadata\Attribute\SmallTest;
use PHPUnit\TestFixture\Metadata\Attribute\TestDoxTest;
use PHPUnit\TestFixture\Metadata\Attribute\TestWithTest;
use PHPUnit\TestFixture\Metadata\Attribute\UsesTest;

/**
 * @covers \PHPUnit\Framework\Attributes\After
 * @covers \PHPUnit\Framework\Attributes\AfterClass
 * @covers \PHPUnit\Framework\Attributes\BackupGlobals
 * @covers \PHPUnit\Framework\Attributes\BackupStaticProperties
 * @covers \PHPUnit\Framework\Attributes\Before
 * @covers \PHPUnit\Framework\Attributes\BeforeClass
 * @covers \PHPUnit\Framework\Attributes\CodeCoverageIgnore
 * @covers \PHPUnit\Framework\Attributes\CoversClass
 * @covers \PHPUnit\Framework\Attributes\CoversFunction
 * @covers \PHPUnit\Framework\Attributes\CoversNothing
 * @covers \PHPUnit\Framework\Attributes\DataProvider
 * @covers \PHPUnit\Framework\Attributes\DataProviderExternal
 * @covers \PHPUnit\Framework\Attributes\Depends
 * @covers \PHPUnit\Framework\Attributes\DependsExternal
 * @covers \PHPUnit\Framework\Attributes\DoesNotPerformAssertions
 * @covers \PHPUnit\Framework\Attributes\Group
 * @covers \PHPUnit\Framework\Attributes\Large
 * @covers \PHPUnit\Framework\Attributes\Medium
 * @covers \PHPUnit\Framework\Attributes\PostCondition
 * @covers \PHPUnit\Framework\Attributes\PreCondition
 * @covers \PHPUnit\Framework\Attributes\PreserveGlobalState
 * @covers \PHPUnit\Framework\Attributes\RequiresFunction
 * @covers \PHPUnit\Framework\Attributes\RequiresOperatingSystem
 * @covers \PHPUnit\Framework\Attributes\RequiresOperatingSystemFamily
 * @covers \PHPUnit\Framework\Attributes\RequiresPhp
 * @covers \PHPUnit\Framework\Attributes\RequiresPhpExtension
 * @covers \PHPUnit\Framework\Attributes\RequiresPhpunit
 * @covers \PHPUnit\Framework\Attributes\RunClassInSeparateProcess
 * @covers \PHPUnit\Framework\Attributes\RunInSeparateProcess
 * @covers \PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses
 * @covers \PHPUnit\Framework\Attributes\Small
 * @covers \PHPUnit\Framework\Attributes\Test
 * @covers \PHPUnit\Framework\Attributes\TestDox
 * @covers \PHPUnit\Framework\Attributes\TestWith
 * @covers \PHPUnit\Framework\Attributes\TestWithJson
 * @covers \PHPUnit\Framework\Attributes\Ticket
 * @covers \PHPUnit\Framework\Attributes\UsesClass
 * @covers \PHPUnit\Framework\Attributes\UsesFunction
 * @covers \PHPUnit\Metadata\Parser\AttributeParser
 *
 * @requires PHP 8
 *
 * @small
 */
final class AttributeParserTest extends TestCase
{
    /**
     * @testdox Parses #[BackupGlobals] attribute on class
     */
    public function test_parses_BackupGlobals_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(BackupGlobalsTest::class)->isBackupGlobals();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBackupGlobals());
        $this->assertTrue($metadata->asArray()[0]->enabled());
    }

    /**
     * @testdox Parses #[BackupStaticProperties] attribute on class
     */
    public function test_parses_BackupStaticProperties_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(BackupStaticPropertiesTest::class)->isBackupStaticProperties();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBackupStaticProperties());
        $this->assertTrue($metadata->asArray()[0]->enabled());
    }

    /**
     * @testdox Parses #[CodeCoverageIgnore] attribute on class
     */
    public function test_parses_CodeCoverageIgnore_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(Example::class)->isCodeCoverageIgnore();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isCodeCoverageIgnore());
    }

    /**
     * @testdox Parses #[CoversClass] attribute on class
     */
    public function test_parses_CoversClass_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(CoversTest::class)->isCoversClass();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isCoversClass());
        $this->assertSame(Example::class, $metadata->asArray()[0]->className());
    }

    /**
     * @testdox Parses #[CoversFunction] attribute on class
     */
    public function test_parses_CoversFunction_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(CoversTest::class)->isCoversFunction();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isCoversFunction());
        $this->assertSame('f', $metadata->asArray()[0]->functionName());
    }

    /**
     * @testdox Parses #[CoversNothing] attribute on class
     */
    public function test_parses_CoversNothing_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(CoversTest::class)->isCoversNothing();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isCoversNothing());
    }

    /**
     * @testdox Parses #[DoesNotPerformAssertions] attribute on class
     */
    public function test_parses_DoesNotPerformAssertions_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(DoesNotPerformAssertionsTest::class)->isDoesNotPerformAssertions();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isDoesNotPerformAssertions());
    }

    /**
     * @testdox Parses #[ExcludeGlobalVariableFromBackup] attribute on class
     */
    public function test_parses_ExcludeGlobalVariableFromBackup_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(BackupGlobalsTest::class)->isExcludeGlobalVariableFromBackup();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isExcludeGlobalVariableFromBackup());
        $this->assertSame('foo', $metadata->asArray()[0]->globalVariableName());
    }

    /**
     * @testdox Parses #[ExcludeStaticPropertyFromBackup] attribute on class
     */
    public function test_parses_ExcludeStaticPropertyFromBackup_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(BackupStaticPropertiesTest::class)->isExcludeStaticPropertyFromBackup();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isExcludeStaticPropertyFromBackup());
        $this->assertSame('className', $metadata->asArray()[0]->className());
        $this->assertSame('propertyName', $metadata->asArray()[0]->propertyName());
    }

    /**
     * @testdox Parses #[Group] attribute on class
     */
    public function test_parses_Group_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(GroupTest::class)->isGroup();

        $this->assertCount(2, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('group', $metadata->asArray()[0]->groupName());
    }

    /**
     * @testdox Parses #[Large] attribute on class
     */
    public function test_parses_Large_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(LargeTest::class)->isGroup();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('large', $metadata->asArray()[0]->groupName());
    }

    /**
     * @testdox Parses #[Medium] attribute on class
     */
    public function test_parses_Medium_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(MediumTest::class)->isGroup();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('medium', $metadata->asArray()[0]->groupName());
    }

    /**
     * @testdox Parses #[PreserveGlobalState] attribute on class
     */
    public function test_parses_PreserveGlobalState_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(PreserveGlobalStateTest::class)->isPreserveGlobalState();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isPreserveGlobalState());
        $this->assertTrue($metadata->asArray()[0]->enabled());
    }

    /**
     * @testdox Parses #[RequiresFunction] attribute on class
     */
    public function test_parses_RequiresFunction_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(RequiresFunctionTest::class)->isRequiresFunction();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresFunction());
        $this->assertSame('f', $metadata->asArray()[0]->functionName());
    }

    /**
     * @testdox Parses #[RequiresOperatingSystem] attribute on class
     */
    public function test_parses_RequiresOperatingSystem_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(RequiresOperatingSystemTest::class)->isRequiresOperatingSystem();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresOperatingSystem());
        $this->assertSame('Linux', $metadata->asArray()[0]->operatingSystem());
    }

    /**
     * @testdox Parses #[RequiresOperatingSystemFamily] attribute on class
     */
    public function test_parses_RequiresOperatingSystemFamily_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(RequiresOperatingSystemFamilyTest::class)->isRequiresOperatingSystemFamily();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresOperatingSystemFamily());
        $this->assertSame('Linux', $metadata->asArray()[0]->operatingSystemFamily());
    }

    /**
     * @testdox Parses #[RequiresPhp] attribute on class
     */
    public function test_parses_RequiresPhp_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(RequiresPhpTest::class)->isRequiresPhp();

        $this->assertCount(1, $metadata);

        $requirement = $metadata->asArray()[0];

        $this->assertTrue($requirement->isRequiresPhp());

        assert($requirement instanceof RequiresPhp);

        $versionRequirement = $requirement->versionRequirement();

        assert($versionRequirement instanceof ConstraintRequirement);

        $this->assertSame('8.0.0', $versionRequirement->asString());
    }

    /**
     * @testdox Parses #[RequiresPhpExtension] attribute on class
     */
    public function test_parses_RequiresPhpExtension_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(RequiresPhpExtensionTest::class)->isRequiresPhpExtension();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresPhpExtension());
        $this->assertSame('foo', $metadata->asArray()[0]->extension());
        $this->assertFalse($metadata->asArray()[0]->hasVersionRequirement());
    }

    /**
     * @testdox Parses #[RequiresPhpunit] attribute on class
     */
    public function test_parses_RequiresPhpunit_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(RequiresPhpunitTest::class)->isRequiresPhpunit();

        $this->assertCount(1, $metadata);

        $requirement = $metadata->asArray()[0];

        $this->assertTrue($requirement->isRequiresPhpunit());

        assert($requirement instanceof RequiresPhpunit);

        $versionRequirement = $requirement->versionRequirement();

        assert($versionRequirement instanceof ComparisonRequirement);

        $this->assertSame('>= 10.0.0', $versionRequirement->asString());
    }

    /**
     * @testdox Parses #[RunClassInSeparateProcess] attribute on class
     */
    public function test_parses_RunClassInSeparateProcess_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(ProcessIsolationTest::class)->isRunClassInSeparateProcess();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRunClassInSeparateProcess());
    }

    /**
     * @testdox Parses #[RunTestsInSeparateProcesses] attribute on class
     */
    public function test_parses_RunTestsInSeparateProcesses_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(ProcessIsolationTest::class)->isRunTestsInSeparateProcesses();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRunTestsInSeparateProcesses());
    }

    /**
     * @testdox Parses #[Small] attribute on class
     */
    public function test_parses_Small_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(SmallTest::class)->isGroup();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('small', $metadata->asArray()[0]->groupName());
    }

    /**
     * @testdox Parses #[TestDox] attribute on class
     */
    public function test_parses_TestDox_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(TestDoxTest::class)->isTestDox();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isTestDox());
        $this->assertSame('text', $metadata->asArray()[0]->text());
    }

    /**
     * @testdox Parses #[Ticket] attribute on class
     */
    public function test_parses_Ticket_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(GroupTest::class)->isGroup();

        $this->assertCount(2, $metadata);
        $this->assertTrue($metadata->asArray()[1]->isGroup());
        $this->assertSame('ticket', $metadata->asArray()[1]->groupName());
    }

    /**
     * @testdox Parses #[UsesClass] attribute on class
     */
    public function test_parses_UsesClass_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(UsesTest::class)->isUsesClass();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isUsesClass());
        $this->assertSame(Example::class, $metadata->asArray()[0]->className());
    }

    /**
     * @testdox Parses #[UsesFunction] attribute on class
     */
    public function test_parses_UsesFunction_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(UsesTest::class)->isUsesFunction();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isUsesFunction());
        $this->assertSame('f', $metadata->asArray()[0]->functionName());
    }

    /**
     * @testdox Parses #[After] attribute on method
     */
    public function test_parses_After_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(SmallTest::class, 'afterTest')->isAfter();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isAfter());
    }

    /**
     * @testdox Parses #[AfterClass] attribute on method
     */
    public function test_parses_AfterClass_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(SmallTest::class, 'afterTests')->isAfterClass();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isAfterClass());
    }

    /**
     * @testdox Parses #[BackupGlobals] attribute on method
     */
    public function test_parses_BackupGlobals_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(BackupGlobalsTest::class, 'testOne')->isBackupGlobals();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBackupGlobals());
        $this->assertFalse($metadata->asArray()[0]->enabled());
    }

    /**
     * @testdox Parses #[BackupStaticProperties] attribute on method
     */
    public function test_parses_BackupStaticProperties_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(BackupStaticPropertiesTest::class, 'testOne')->isBackupStaticProperties();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBackupStaticProperties());
        $this->assertFalse($metadata->asArray()[0]->enabled());
    }

    /**
     * @testdox Parses #[Before] attribute on method
     */
    public function test_parses_Before_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(SmallTest::class, 'beforeTest')->isBefore();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBefore());
    }

    /**
     * @testdox Parses #[BeforeClass] attribute on method
     */
    public function test_parses_BeforeClass_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(SmallTest::class, 'beforeTests')->isBeforeClass();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBeforeClass());
    }

    /**
     * @testdox Parses #[CodeCoverageIgnore] attribute on method
     */
    public function test_parses_CodeCoverageIgnore_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(Example::class, 'method')->isCodeCoverageIgnore();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isCodeCoverageIgnore());
    }

    /**
     * @testdox Parses #[CoversNothing] attribute on method
     */
    public function test_parses_CoversNothing_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(CoversTest::class, 'testOne')->isCoversNothing();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isCoversNothing());
    }

    /**
     * @testdox Parses #[DataProvider] attribute on method
     */
    public function test_parses_DataProvider_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(SmallTest::class, 'testWithDataProvider')->isDataProvider();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isDataProvider());
        $this->assertSame(SmallTest::class, $metadata->asArray()[0]->className());
        $this->assertSame('provider', $metadata->asArray()[0]->methodName());
    }

    /**
     * @testdox Parses #[DataProviderExternal] attribute on method
     */
    public function test_parses_DataProviderExternal_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(SmallTest::class, 'testWithDataProviderExternal')->isDataProvider();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isDataProvider());
        $this->assertSame(SmallTest::class, $metadata->asArray()[0]->className());
        $this->assertSame('provider', $metadata->asArray()[0]->methodName());
    }

    /**
     * @testdox Parses #[Depends] attribute on method
     */
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

    /**
     * @testdox Parses #[DependsUsingDeepClone] attribute on method
     */
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

    /**
     * @testdox Parses #[DependsUsingShallowClone] attribute on method
     */
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

    /**
     * @testdox Parses #[DependsExternal] attribute on method
     */
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

    /**
     * @testdox Parses #[DependsExternalUsingDeepClone] attribute on method
     */
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

    /**
     * @testdox Parses #[DependsExternalUsingShallowClone] attribute on method
     */
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

    /**
     * @testdox Parses #[DependsOnClass] attribute on method
     */
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

    /**
     * @testdox Parses #[DependsOnClassUsingDeepClone] attribute on method
     */
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

    /**
     * @testdox Parses #[DependsOnClassUsingShallowClone] attribute on method
     */
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

    /**
     * @testdox Parses #[DoesNotPerformAssertions] attribute on method
     */
    public function test_parses_DoesNotPerformAssertions_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(DoesNotPerformAssertionsTest::class, 'testOne')->isDoesNotPerformAssertions();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isDoesNotPerformAssertions());
    }

    /**
     * @testdox Parses #[ExcludeGlobalVariableFromBackup] attribute on method
     */
    public function test_parses_ExcludeGlobalVariableFromBackup_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(BackupGlobalsTest::class, 'testOne')->isExcludeGlobalVariableFromBackup();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isExcludeGlobalVariableFromBackup());
        $this->assertSame('bar', $metadata->asArray()[0]->globalVariableName());
    }

    /**
     * @testdox Parses #[ExcludeStaticPropertyFromBackup] attribute on method
     */
    public function test_parses_ExcludeStaticPropertyFromBackup_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(BackupStaticPropertiesTest::class, 'testOne')->isExcludeStaticPropertyFromBackup();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isExcludeStaticPropertyFromBackup());
        $this->assertSame('anotherClassName', $metadata->asArray()[0]->className());
        $this->assertSame('propertyName', $metadata->asArray()[0]->propertyName());
    }

    /**
     * @testdox Parses #[Group] attribute on method
     */
    public function test_parses_Group_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(GroupTest::class, 'testOne')->isGroup();

        $this->assertCount(2, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('another-group', $metadata->asArray()[0]->groupName());
    }

    /**
     * @testdox Parses #[PostCondition] attribute on method
     */
    public function test_parses_PostCondition_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(SmallTest::class, 'postCondition')->isPostCondition();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isPostCondition());
    }

    /**
     * @testdox Parses #[PreCondition] attribute on method
     */
    public function test_parses_PreCondition_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(SmallTest::class, 'preCondition')->isPreCondition();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isPreCondition());
    }

    /**
     * @testdox Parses #[PreserveGlobalState] attribute on method
     */
    public function test_parses_PreserveGlobalState_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(PreserveGlobalStateTest::class, 'testOne')->isPreserveGlobalState();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isPreserveGlobalState());
        $this->assertFalse($metadata->asArray()[0]->enabled());
    }

    /**
     * @testdox Parses #[RequiresFunction] attribute on method
     */
    public function test_parses_RequiresFunction_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(RequiresFunctionTest::class, 'testOne')->isRequiresFunction();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresFunction());
        $this->assertSame('g', $metadata->asArray()[0]->functionName());
    }

    /**
     * @testdox Parses #[RequiresOperatingSystem] attribute on method
     */
    public function test_parses_RequiresOperatingSystem_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forClass(RequiresOperatingSystemTest::class)->isRequiresOperatingSystem();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresOperatingSystem());
        $this->assertSame('Linux', $metadata->asArray()[0]->operatingSystem());
    }

    /**
     * @testdox Parses #[RequiresOperatingSystemFamily] attribute on method
     */
    public function test_parses_RequiresOperatingSystemFamily_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(RequiresOperatingSystemFamilyTest::class, 'testOne')->isRequiresOperatingSystemFamily();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresOperatingSystemFamily());
        $this->assertSame('Linux', $metadata->asArray()[0]->operatingSystemFamily());
    }

    /**
     * @testdox Parses #[RequiresPhp] attribute on method
     */
    public function test_parses_RequiresPhp_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(RequiresPhpTest::class, 'testOne')->isRequiresPhp();

        $this->assertCount(1, $metadata);

        $requirement = $metadata->asArray()[0];

        $this->assertTrue($requirement->isRequiresPhp());

        assert($requirement instanceof RequiresPhp);

        $versionRequirement = $requirement->versionRequirement();

        assert($versionRequirement instanceof ConstraintRequirement);

        $this->assertSame('^8.0', $versionRequirement->asString());
    }

    /**
     * @testdox Parses #[RequiresPhpExtension] attribute on method
     */
    public function test_parses_RequiresPhpExtension_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(RequiresPhpExtensionTest::class, 'testOne')->isRequiresPhpExtension();

        $this->assertCount(1, $metadata);

        $requirement = $metadata->asArray()[0];

        $this->assertTrue($requirement->isRequiresPhpExtension());

        assert($requirement instanceof RequiresPhpExtension);

        $this->assertSame('bar', $requirement->extension());
        $this->assertTrue($requirement->hasVersionRequirement());

        $versionRequirement = $requirement->versionRequirement();

        assert($versionRequirement instanceof ComparisonRequirement);

        $this->assertSame('>= 1.0', $versionRequirement->asString());

        $metadata = (new AttributeParser)->forMethod(RequiresPhpExtensionTest::class, 'testTwo');

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

    /**
     * @testdox Parses #[RequiresPhpunit] attribute on method
     */
    public function test_parses_RequiresPhpunit_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(RequiresPhpunitTest::class, 'testOne')->isRequiresPhpunit();

        $this->assertCount(1, $metadata);

        $requirement = $metadata->asArray()[0];

        $this->assertTrue($requirement->isRequiresPhpunit());

        assert($requirement instanceof RequiresPhpunit);

        $versionRequirement = $requirement->versionRequirement();

        assert($versionRequirement instanceof ConstraintRequirement);

        $this->assertSame('^10.0', $versionRequirement->asString());
    }

    /**
     * @testdox Parses #[RunInSeparateProcess] attribute on method
     */
    public function test_parses_RunInSeparateProcess_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(ProcessIsolationTest::class, 'testOne')->isRunInSeparateProcess();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRunInSeparateProcess());
    }

    /**
     * @testdox Parses #[Test] attribute on method
     */
    public function test_parses_Test_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(SmallTest::class, 'one')->isTest();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isTest());
    }

    /**
     * @testdox Parses #[TestDox] attribute on method
     */
    public function test_parses_TestDox_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(TestDoxTest::class, 'testOne')->isTestDox();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isTestDox());
        $this->assertSame('text', $metadata->asArray()[0]->text());
    }

    /**
     * @testdox Parses #[TestWith] attribute on method
     */
    public function test_parses_TestWith_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(TestWithTest::class, 'testOne')->isTestWith();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isTestWith());
        $this->assertSame([1, 2, 3], $metadata->asArray()[0]->data());
    }

    /**
     * @testdox Parses #[TestWithJson] attribute on method
     */
    public function test_parses_TestWithJson_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(TestWithTest::class, 'testTwo')->isTestWith();

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isTestWith());
        $this->assertSame([1, 2, 3], $metadata->asArray()[0]->data());
    }

    /**
     * @testdox Parses #[Ticket] attribute on method
     */
    public function test_parses_Ticket_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(GroupTest::class, 'testOne')->isGroup();

        $this->assertCount(2, $metadata);
        $this->assertTrue($metadata->asArray()[1]->isGroup());
        $this->assertSame('another-ticket', $metadata->asArray()[1]->groupName());
    }
}
