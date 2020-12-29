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
use PHPUnit\TestFixture\Metadata\Attribute\BackupGlobalsTest;
use PHPUnit\TestFixture\Metadata\Attribute\BackupStaticPropertiesTest;
use PHPUnit\TestFixture\Metadata\Attribute\CoversTest;
use PHPUnit\TestFixture\Metadata\Attribute\DoesNotPerformAssertionsTest;
use PHPUnit\TestFixture\Metadata\Attribute\Example;
use PHPUnit\TestFixture\Metadata\Attribute\GroupTest;
use PHPUnit\TestFixture\Metadata\Attribute\LargeTest;
use PHPUnit\TestFixture\Metadata\Attribute\MediumTest;
use PHPUnit\TestFixture\Metadata\Attribute\PreserveGlobalStateTest;
use PHPUnit\TestFixture\Metadata\Attribute\RequiresFunctionTest;
use PHPUnit\TestFixture\Metadata\Attribute\RequiresOperatingSystemFamilyTest;
use PHPUnit\TestFixture\Metadata\Attribute\RequiresOperatingSystemTest;
use PHPUnit\TestFixture\Metadata\Attribute\RequiresPhpExtensionTest;
use PHPUnit\TestFixture\Metadata\Attribute\RequiresPhpTest;
use PHPUnit\TestFixture\Metadata\Attribute\RequiresPhpunitTest;
use PHPUnit\TestFixture\Metadata\Attribute\SmallTest;
use PHPUnit\TestFixture\Metadata\Attribute\TestDoxTest;
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
 * @covers \PHPUnit\Util\Metadata\AttributeParser
 *
 * @requires PHP 8
 */
final class AttributeParserTest extends TestCase
{
    /**
     * @testdox Parses #[BackupGlobals] attribute on class
     */
    public function test_parses_BackupGlobals_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(BackupGlobalsTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBackupGlobals());
        $this->assertTrue($metadata->asArray()[0]->enabled());
    }

    /**
     * @testdox Parses #[BackupStaticProperties] attribute on class
     */
    public function test_parses_BackupStaticProperties_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(BackupStaticPropertiesTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBackupStaticProperties());
        $this->assertTrue($metadata->asArray()[0]->enabled());
    }

    /**
     * @testdox Parses #[CodeCoverageIgnore] attribute on class
     */
    public function test_parses_CodeCoverageIgnore_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(Example::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isCodeCoverageIgnore());
    }

    /**
     * @testdox Parses #[CoversClass] attribute on class
     */
    public function test_parses_CoversClass_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(CoversTest::class);

        $this->assertCount(3, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isCoversClass());
        $this->assertSame(Example::class, $metadata->asArray()[0]->className());
    }

    /**
     * @testdox Parses #[CoversFunction] attribute on class
     */
    public function test_parses_CoversFunction_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(CoversTest::class);

        $this->assertCount(3, $metadata);
        $this->assertTrue($metadata->asArray()[1]->isCoversFunction());
        $this->assertSame('f', $metadata->asArray()[1]->functionName());
    }

    /**
     * @testdox Parses #[CoversNothing] attribute on class
     */
    public function test_parses_CoversNothing_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(CoversTest::class);

        $this->assertCount(3, $metadata);
        $this->assertTrue($metadata->asArray()[2]->isCoversNothing());
    }

    /**
     * @testdox Parses #[DoesNotPerformAssertions] attribute on class
     */
    public function test_parses_DoesNotPerformAssertions_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(DoesNotPerformAssertionsTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isDoesNotPerformAssertions());
    }

    /**
     * @testdox Parses #[Group] attribute on class
     */
    public function test_parses_Group_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(GroupTest::class);

        $this->assertCount(2, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('group', $metadata->asArray()[0]->groupName());
    }

    /**
     * @testdox Parses #[Large] attribute on class
     */
    public function test_parses_Large_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(LargeTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('large', $metadata->asArray()[0]->groupName());
    }

    /**
     * @testdox Parses #[Medium] attribute on class
     */
    public function test_parses_Medium_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(MediumTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('medium', $metadata->asArray()[0]->groupName());
    }

    /**
     * @testdox Parses #[PreserveGlobalState] attribute on class
     */
    public function test_parses_PreserveGlobalState_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(PreserveGlobalStateTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isPreserveGlobalState());
        $this->assertTrue($metadata->asArray()[0]->enabled());
    }

    /**
     * @testdox Parses #[RequiresFunction] attribute on class
     */
    public function test_parses_RequiresFunction_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(RequiresFunctionTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresFunction());
        $this->assertSame('f', $metadata->asArray()[0]->functionName());
    }

    /**
     * @testdox Parses #[RequiresOperatingSystem] attribute on class
     */
    public function test_parses_RequiresOperatingSystem_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(RequiresOperatingSystemTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresOperatingSystem());
        $this->assertSame('Linux', $metadata->asArray()[0]->regularExpression());
    }

    /**
     * @testdox Parses #[RequiresOperatingSystemFamily] attribute on class
     */
    public function test_parses_RequiresOperatingSystemFamily_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(RequiresOperatingSystemFamilyTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresOperatingSystemFamily());
        $this->assertSame('Linux', $metadata->asArray()[0]->operatingSystemFamily());
    }

    /**
     * @testdox Parses #[RequiresPhp] attribute on class
     */
    public function test_parses_RequiresPhp_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(RequiresPhpTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresPhp());
        $this->assertSame('8.0.0', $metadata->asArray()[0]->version());
        $this->assertSame('>=', $metadata->asArray()[0]->operator());
    }

    /**
     * @testdox Parses #[RequiresPhpExtension] attribute on class
     */
    public function test_parses_RequiresPhpExtension_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(RequiresPhpExtensionTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresPhpExtension());
        $this->assertSame('foo', $metadata->asArray()[0]->extension());
        $this->assertNull($metadata->asArray()[0]->version());
        $this->assertSame('>=', $metadata->asArray()[0]->operator());
        $this->assertFalse($metadata->asArray()[0]->hasVersionRequirement());
    }

    /**
     * @testdox Parses #[RequiresPhpunit] attribute on class
     */
    public function test_parses_RequiresPhpunit_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(RequiresPhpunitTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresPhpunit());
        $this->assertSame('10.0.0', $metadata->asArray()[0]->version());
        $this->assertSame('>=', $metadata->asArray()[0]->operator());
    }

    /**
     * @testdox Parses #[Small] attribute on class
     */
    public function test_parses_Small_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(SmallTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('small', $metadata->asArray()[0]->groupName());
    }

    /**
     * @testdox Parses #[TestDox] attribute on class
     */
    public function test_parses_TestDox_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(TestDoxTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isTestDox());
        $this->assertSame('text', $metadata->asArray()[0]->text());
    }

    /**
     * @testdox Parses #[Ticket] attribute on class
     */
    public function test_parses_Ticket_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(GroupTest::class);

        $this->assertCount(2, $metadata);
        $this->assertTrue($metadata->asArray()[1]->isGroup());
        $this->assertSame('ticket', $metadata->asArray()[1]->groupName());
    }

    /**
     * @testdox Parses #[UsesClass] attribute on class
     */
    public function test_parses_UsesClass_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(UsesTest::class);

        $this->assertCount(2, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isUsesClass());
        $this->assertSame(Example::class, $metadata->asArray()[0]->className());
    }

    /**
     * @testdox Parses #[UsesFunction] attribute on class
     */
    public function test_parses_UsesFunction_attribute_on_class(): void
    {
        $metadata = (new AttributeParser)->forClass(UsesTest::class);

        $this->assertCount(2, $metadata);
        $this->assertTrue($metadata->asArray()[1]->isUsesFunction());
        $this->assertSame('f', $metadata->asArray()[1]->functionName());
    }

    /**
     * @testdox Parses #[After] attribute on method
     */
    public function test_parses_After_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(SmallTest::class, 'afterTest');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isAfter());
    }

    /**
     * @testdox Parses #[AfterClass] attribute on method
     */
    public function test_parses_AfterClass_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(SmallTest::class, 'afterTests');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isAfterClass());
    }

    /**
     * @testdox Parses #[BackupGlobals] attribute on method
     */
    public function test_parses_BackupGlobals_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(BackupGlobalsTest::class, 'testOne');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBackupGlobals());
        $this->assertFalse($metadata->asArray()[0]->enabled());
    }

    /**
     * @testdox Parses #[BackupStaticProperties] attribute on method
     */
    public function test_parses_BackupStaticProperties_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(BackupStaticPropertiesTest::class, 'testOne');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBackupStaticProperties());
        $this->assertFalse($metadata->asArray()[0]->enabled());
    }

    /**
     * @testdox Parses #[Before] attribute on method
     */
    public function test_parses_Before_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(SmallTest::class, 'beforeTest');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBefore());
    }

    /**
     * @testdox Parses #[BeforeClass] attribute on method
     */
    public function test_parses_BeforeClass_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(SmallTest::class, 'beforeTests');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBeforeClass());
    }

    /**
     * @testdox Parses #[CodeCoverageIgnore] attribute on method
     */
    public function test_parses_CodeCoverageIgnore_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(Example::class, 'method');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isCodeCoverageIgnore());
    }

    /**
     * @testdox Parses #[CoversNothing] attribute on method
     */
    public function test_parses_CoversNothing_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(CoversTest::class, 'testOne');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isCoversNothing());
    }

    /**
     * @testdox Parses #[DataProvider] attribute on method
     */
    public function test_parses_DataProvider_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(SmallTest::class, 'testWithDataProvider');

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
        $metadata = (new AttributeParser)->forMethod(SmallTest::class, 'testWithDataProviderExternal');

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
        $metadata = (new AttributeParser)->forMethod(SmallTest::class, 'testWithDepends');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isDepends());
        $this->assertSame(SmallTest::class, $metadata->asArray()[0]->className());
        $this->assertSame('one', $metadata->asArray()[0]->methodName());
    }

    /**
     * @testdox Parses #[DependsExternal] attribute on method
     */
    public function test_parses_DependsExternal_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(SmallTest::class, 'testWithDependsExternal');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isDepends());
        $this->assertSame(SmallTest::class, $metadata->asArray()[0]->className());
        $this->assertSame('one', $metadata->asArray()[0]->methodName());
    }

    /**
     * @testdox Parses #[DoesNotPerformAssertions] attribute on method
     */
    public function test_parses_DoesNotPerformAssertions_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(DoesNotPerformAssertionsTest::class, 'testOne');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isDoesNotPerformAssertions());
    }

    /**
     * @testdox Parses #[Group] attribute on method
     */
    public function test_parses_Group_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(GroupTest::class, 'testOne');

        $this->assertCount(2, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('another-group', $metadata->asArray()[0]->groupName());
    }

    /**
     * @testdox Parses #[PostCondition] attribute on method
     */
    public function test_parses_PostCondition_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(SmallTest::class, 'postCondition');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isPostCondition());
    }

    /**
     * @testdox Parses #[PreCondition] attribute on method
     */
    public function test_parses_PreCondition_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(SmallTest::class, 'preCondition');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isPreCondition());
    }

    /**
     * @testdox Parses #[PreserveGlobalState] attribute on method
     */
    public function test_parses_PreserveGlobalState_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(PreserveGlobalStateTest::class, 'testOne');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isPreserveGlobalState());
        $this->assertFalse($metadata->asArray()[0]->enabled());
    }

    /**
     * @testdox Parses #[RequiresFunction] attribute on method
     */
    public function test_parses_RequiresFunction_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(RequiresFunctionTest::class, 'testOne');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresFunction());
        $this->assertSame('g', $metadata->asArray()[0]->functionName());
    }

    /**
     * @testdox Parses #[RequiresOperatingSystem] attribute on method
     */
    public function test_parses_RequiresOperatingSystem_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forClass(RequiresOperatingSystemTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresOperatingSystem());
        $this->assertSame('Linux', $metadata->asArray()[0]->regularExpression());
    }

    /**
     * @testdox Parses #[RequiresOperatingSystemFamily] attribute on method
     */
    public function test_parses_RequiresOperatingSystemFamily_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(RequiresOperatingSystemFamilyTest::class, 'testOne');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresOperatingSystemFamily());
        $this->assertSame('Linux', $metadata->asArray()[0]->operatingSystemFamily());
    }

    /**
     * @testdox Parses #[RequiresPhp] attribute on method
     */
    public function test_parses_RequiresPhp_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(RequiresPhpTest::class, 'testOne');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresPhp());
        $this->assertSame('9.0.0', $metadata->asArray()[0]->version());
        $this->assertSame('<', $metadata->asArray()[0]->operator());
    }

    /**
     * @testdox Parses #[RequiresPhpExtension] attribute on method
     */
    public function test_parses_RequiresPhpExtension_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(RequiresPhpExtensionTest::class, 'testOne');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresPhpExtension());
        $this->assertSame('bar', $metadata->asArray()[0]->extension());
        $this->assertSame('1.0.0', $metadata->asArray()[0]->version());
        $this->assertSame('>=', $metadata->asArray()[0]->operator());
        $this->assertTrue($metadata->asArray()[0]->hasVersionRequirement());

        $metadata = (new AttributeParser)->forMethod(RequiresPhpExtensionTest::class, 'testTwo');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresPhpExtension());
        $this->assertSame('baz', $metadata->asArray()[0]->extension());
        $this->assertSame('2.0.0', $metadata->asArray()[0]->version());
        $this->assertSame('<', $metadata->asArray()[0]->operator());
        $this->assertTrue($metadata->asArray()[0]->hasVersionRequirement());
    }

    /**
     * @testdox Parses #[RequiresPhpunit] attribute on method
     */
    public function test_parses_RequiresPhpunit_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(RequiresPhpunitTest::class, 'testOne');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresPhpunit());
        $this->assertSame('11.0.0', $metadata->asArray()[0]->version());
        $this->assertSame('<', $metadata->asArray()[0]->operator());
    }

    /**
     * @testdox Parses #[Test] attribute on method
     */
    public function test_parses_Test_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(SmallTest::class, 'one');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isTest());
    }

    /**
     * @testdox Parses #[TestDox] attribute on method
     */
    public function test_parses_TestDox_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(TestDoxTest::class, 'testOne');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isTestDox());
        $this->assertSame('text', $metadata->asArray()[0]->text());
    }

    /**
     * @testdox Parses #[Ticket] attribute on method
     */
    public function test_parses_Ticket_attribute_on_method(): void
    {
        $metadata = (new AttributeParser)->forMethod(GroupTest::class, 'testOne');

        $this->assertCount(2, $metadata);
        $this->assertTrue($metadata->asArray()[1]->isGroup());
        $this->assertSame('another-ticket', $metadata->asArray()[1]->groupName());
    }
}
