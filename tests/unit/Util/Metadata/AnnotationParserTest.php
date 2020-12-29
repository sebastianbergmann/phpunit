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
use PHPUnit\TestFixture\Metadata\Annotation\BackupGlobalsTest;
use PHPUnit\TestFixture\Metadata\Annotation\BackupStaticPropertiesTest;
use PHPUnit\TestFixture\Metadata\Annotation\CoversTest;
use PHPUnit\TestFixture\Metadata\Annotation\DoesNotPerformAssertionsTest;
use PHPUnit\TestFixture\Metadata\Annotation\Example;
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
use PHPUnit\TestFixture\Metadata\Annotation\TestWithTest;
use PHPUnit\TestFixture\Metadata\Annotation\UsesTest;

/**
 * @covers \PHPUnit\Util\Metadata\AnnotationParser
 */
final class AnnotationParserTest extends TestCase
{
    /**
     * @testdox Parses backupGlobals annotation on class
     */
    public function test_parses_backupGlobals_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(BackupGlobalsTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBackupGlobals());
        $this->assertTrue($metadata->asArray()[0]->enabled());
    }

    /**
     * @testdox Parses backupStaticProperties annotation on class
     */
    public function test_parses_backupStaticProperties_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(BackupStaticPropertiesTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBackupStaticProperties());
        $this->assertTrue($metadata->asArray()[0]->enabled());
    }

    /**
     * @testdox Parses codeCoverageIgnore annotation on class
     */
    public function test_parses_codeCoverageIgnore_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(Example::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isCodeCoverageIgnore());
    }

    /**
     * @testdox Parses coversClass annotation on class
     */
    public function test_parses_coversClass_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(CoversTest::class);

        $this->assertCount(3, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isCoversClass());
        $this->assertSame(Example::class, $metadata->asArray()[0]->className());
    }

    /**
     * @testdox Parses coversFunction annotation on class
     */
    public function test_parses_coversFunction_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(CoversTest::class);

        $this->assertCount(3, $metadata);
        $this->assertTrue($metadata->asArray()[1]->isCoversFunction());
        $this->assertSame('f', $metadata->asArray()[1]->functionName());
    }

    /**
     * @testdox Parses coversNothing annotation on class
     */
    public function test_parses_coversNothing_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(CoversTest::class);

        $this->assertCount(3, $metadata);
        $this->assertTrue($metadata->asArray()[2]->isCoversNothing());
    }

    /**
     * @testdox Parses doesNotPerformAssertions annotation on class
     */
    public function test_parses_doesNotPerformAssertions_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(DoesNotPerformAssertionsTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isDoesNotPerformAssertions());
    }

    /**
     * @testdox Parses group annotation on class
     */
    public function test_parses_group_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(GroupTest::class);

        $this->assertCount(2, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('group', $metadata->asArray()[0]->groupName());
    }

    /**
     * @testdox Parses large annotation on class
     */
    public function test_parses_large_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(LargeTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('large', $metadata->asArray()[0]->groupName());
    }

    /**
     * @testdox Parses medium annotation on class
     */
    public function test_parses_medium_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(MediumTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('medium', $metadata->asArray()[0]->groupName());
    }

    /**
     * @testdox Parses preserveGlobalState annotation on class
     */
    public function test_parses_preserveGlobalState_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(PreserveGlobalStateTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isPreserveGlobalState());
        $this->assertTrue($metadata->asArray()[0]->enabled());
    }

    /**
     * @testdox Parses requiresFunction annotation on class
     */
    public function test_parses_requiresFunction_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(RequiresFunctionTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresFunction());
        $this->assertSame('f', $metadata->asArray()[0]->functionName());
    }

    /**
     * @testdox Parses requiresOperatingSystem annotation on class
     */
    public function test_parses_requiresOperatingSystem_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(RequiresOperatingSystemTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresOperatingSystem());
        $this->assertSame('Linux', $metadata->asArray()[0]->regularExpression());
    }

    /**
     * @testdox Parses requiresOperatingSystemFamily annotation on class
     */
    public function test_parses_requiresOperatingSystemFamily_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(RequiresOperatingSystemFamilyTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresOperatingSystemFamily());
        $this->assertSame('Linux', $metadata->asArray()[0]->operatingSystemFamily());
    }

    /**
     * @testdox Parses requiresPhp annotation on class
     */
    public function test_parses_requiresPhp_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(RequiresPhpTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresPhp());
        $this->assertSame('8.0.0', $metadata->asArray()[0]->version());
        $this->assertSame('>=', $metadata->asArray()[0]->operator());
    }

    /**
     * @testdox Parses requiresPhpExtension annotation on class
     */
    public function test_parses_requiresPhpExtension_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(RequiresPhpExtensionTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresPhpExtension());
        $this->assertSame('foo', $metadata->asArray()[0]->extension());
        $this->assertNull($metadata->asArray()[0]->version());
        $this->assertSame('>=', $metadata->asArray()[0]->operator());
        $this->assertFalse($metadata->asArray()[0]->hasVersionRequirement());
    }

    /**
     * @testdox Parses requiresPhpunit annotation on class
     */
    public function test_parses_requiresPhpunit_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(RequiresPhpunitTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresPhpunit());
        $this->assertSame('10.0.0', $metadata->asArray()[0]->version());
        $this->assertSame('>=', $metadata->asArray()[0]->operator());
    }

    /**
     * @testdox Parses runTestsInSeparateProcesses annotation on class
     */
    public function test_parses_runTestsInSeparateProcesses_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(ProcessIsolationTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRunTestsInSeparateProcesses());
    }

    /**
     * @testdox Parses small annotation on class
     */
    public function test_parses_small_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(SmallTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('small', $metadata->asArray()[0]->groupName());
    }

    /**
     * @testdox Parses testDox annotation on class
     */
    public function test_parses_testDox_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(TestDoxTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isTestDox());
        $this->assertSame('text', $metadata->asArray()[0]->text());
    }

    /**
     * @testdox Parses ticket annotation on class
     */
    public function test_parses_ticket_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(GroupTest::class);

        $this->assertCount(2, $metadata);
        $this->assertTrue($metadata->asArray()[1]->isGroup());
        $this->assertSame('ticket', $metadata->asArray()[1]->groupName());
    }

    /**
     * @testdox Parses usesClass annotation on class
     */
    public function test_parses_usesClass_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(UsesTest::class);

        $this->assertCount(2, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isUsesClass());
        $this->assertSame(Example::class, $metadata->asArray()[0]->className());
    }

    /**
     * @testdox Parses usesFunction annotation on class
     */
    public function test_parses_usesFunction_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(UsesTest::class);

        $this->assertCount(2, $metadata);
        $this->assertTrue($metadata->asArray()[1]->isUsesFunction());
        $this->assertSame('f', $metadata->asArray()[1]->functionName());
    }

    /**
     * @testdox Parses after annotation on method
     */
    public function test_parses_after_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(SmallTest::class, 'afterTest');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isAfter());
    }

    /**
     * @testdox Parses afterClass annotation on method
     */
    public function test_parses_afterClass_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(SmallTest::class, 'afterTests');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isAfterClass());
    }

    /**
     * @testdox Parses backupGlobals annotation on method
     */
    public function test_parses_backupGlobals_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(BackupGlobalsTest::class, 'testOne');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBackupGlobals());
        $this->assertFalse($metadata->asArray()[0]->enabled());
    }

    /**
     * @testdox Parses backupStaticProperties annotation on method
     */
    public function test_parses_backupStaticProperties_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(BackupStaticPropertiesTest::class, 'testOne');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBackupStaticProperties());
        $this->assertFalse($metadata->asArray()[0]->enabled());
    }

    /**
     * @testdox Parses before annotation on method
     */
    public function test_parses_before_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(SmallTest::class, 'beforeTest');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBefore());
    }

    /**
     * @testdox Parses beforeClass annotation on method
     */
    public function test_parses_beforeClass_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(SmallTest::class, 'beforeTests');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBeforeClass());
    }

    /**
     * @testdox Parses codeCoverageIgnore annotation on method
     */
    public function test_parses_codeCoverageIgnore_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(Example::class, 'method');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isCodeCoverageIgnore());
    }

    /**
     * @testdox Parses coversNothing annotation on method
     */
    public function test_parses_coversNothing_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(CoversTest::class, 'testOne');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isCoversNothing());
    }

    /**
     * @testdox Parses dataProvider annotation on method
     */
    public function test_parses_dataProvider_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(SmallTest::class, 'testWithDataProvider');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isDataProvider());
        $this->assertSame(SmallTest::class, $metadata->asArray()[0]->className());
        $this->assertSame('provider', $metadata->asArray()[0]->methodName());
    }

    /**
     * @testdox Parses dataProviderExternal annotation on method
     */
    public function test_parses_dataProviderExternal_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(SmallTest::class, 'testWithDataProviderExternal');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isDataProvider());
        $this->assertSame(SmallTest::class, $metadata->asArray()[0]->className());
        $this->assertSame('provider', $metadata->asArray()[0]->methodName());
    }

    /**
     * @testdox Parses depends annotation on method
     */
    public function test_parses_depends_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(SmallTest::class, 'testWithDepends');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isDepends());
        $this->assertSame(SmallTest::class, $metadata->asArray()[0]->className());
        $this->assertSame('one', $metadata->asArray()[0]->methodName());
    }

    /**
     * @testdox Parses dependsExternal annotation on method
     */
    public function test_parses_dependsExternal_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(SmallTest::class, 'testWithDependsExternal');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isDepends());
        $this->assertSame(SmallTest::class, $metadata->asArray()[0]->className());
        $this->assertSame('one', $metadata->asArray()[0]->methodName());
    }

    /**
     * @testdox Parses doesNotPerformAssertions annotation on method
     */
    public function test_parses_doesNotPerformAssertions_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(DoesNotPerformAssertionsTest::class, 'testOne');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isDoesNotPerformAssertions());
    }

    /**
     * @testdox Parses group annotation on method
     */
    public function test_parses_group_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(GroupTest::class, 'testOne');

        $this->assertCount(2, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('another-group', $metadata->asArray()[0]->groupName());
    }

    /**
     * @testdox Parses postCondition annotation on method
     */
    public function test_parses_postCondition_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(SmallTest::class, 'postCondition');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isPostCondition());
    }

    /**
     * @testdox Parses preCondition annotation on method
     */
    public function test_parses_preCondition_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(SmallTest::class, 'preCondition');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isPreCondition());
    }

    /**
     * @testdox Parses preserveGlobalState annotation on method
     */
    public function test_parses_preserveGlobalState_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(PreserveGlobalStateTest::class, 'testOne');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isPreserveGlobalState());
        $this->assertFalse($metadata->asArray()[0]->enabled());
    }

    /**
     * @testdox Parses requiresFunction annotation on method
     */
    public function test_parses_requiresFunction_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(RequiresFunctionTest::class, 'testOne');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresFunction());
        $this->assertSame('g', $metadata->asArray()[0]->functionName());
    }

    /**
     * @testdox Parses requiresOperatingSystem annotation on method
     */
    public function test_parses_requiresOperatingSystem_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forClass(RequiresOperatingSystemTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresOperatingSystem());
        $this->assertSame('Linux', $metadata->asArray()[0]->regularExpression());
    }

    /**
     * @testdox Parses requiresOperatingSystemFamily annotation on method
     */
    public function test_parses_requiresOperatingSystemFamily_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(RequiresOperatingSystemFamilyTest::class, 'testOne');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresOperatingSystemFamily());
        $this->assertSame('Linux', $metadata->asArray()[0]->operatingSystemFamily());
    }

    /**
     * @testdox Parses requiresPhp annotation on method
     */
    public function test_parses_requiresPhp_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(RequiresPhpTest::class, 'testOne');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresPhp());
        $this->assertSame('9.0.0', $metadata->asArray()[0]->version());
        $this->assertSame('<', $metadata->asArray()[0]->operator());
    }

    /**
     * @testdox Parses requiresPhpExtension annotation on method
     */
    public function test_parses_requiresPhpExtension_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(RequiresPhpExtensionTest::class, 'testOne');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresPhpExtension());
        $this->assertSame('bar', $metadata->asArray()[0]->extension());
        $this->assertSame('1.0.0', $metadata->asArray()[0]->version());
        $this->assertSame('>=', $metadata->asArray()[0]->operator());
        $this->assertTrue($metadata->asArray()[0]->hasVersionRequirement());

        $metadata = (new AnnotationParser)->forMethod(RequiresPhpExtensionTest::class, 'testTwo');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresPhpExtension());
        $this->assertSame('baz', $metadata->asArray()[0]->extension());
        $this->assertSame('2.0.0', $metadata->asArray()[0]->version());
        $this->assertSame('<', $metadata->asArray()[0]->operator());
        $this->assertTrue($metadata->asArray()[0]->hasVersionRequirement());
    }

    /**
     * @testdox Parses requiresPhpunit annotation on method
     */
    public function test_parses_requiresPhpunit_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(RequiresPhpunitTest::class, 'testOne');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresPhpunit());
        $this->assertSame('11.0.0', $metadata->asArray()[0]->version());
        $this->assertSame('<', $metadata->asArray()[0]->operator());
    }

    /**
     * @testdox Parses runInSeparateProcess annotation on method
     */
    public function test_parses_runInSeparateProcess_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(ProcessIsolationTest::class, 'testOne');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRunInSeparateProcess());
    }

    /**
     * @testdox Parses test annotation on method
     */
    public function test_parses_test_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(SmallTest::class, 'one');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isTest());
    }

    /**
     * @testdox Parses testdox annotation on method
     */
    public function test_parses_testdox_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(TestDoxTest::class, 'testOne');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isTestDox());
        $this->assertSame('text', $metadata->asArray()[0]->text());
    }

    /**
     * @testdox Parses testWith annotation on method
     */
    public function test_parses_testWith_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(TestWithTest::class, 'testOne');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isTestWith());
        $this->assertSame([1, 2, 3], $metadata->asArray()[0]->data());
    }

    /**
     * @testdox Parses ticket annotation on method
     */
    public function test_parses_ticket_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(GroupTest::class, 'testOne');

        $this->assertCount(2, $metadata);
        $this->assertTrue($metadata->asArray()[1]->isGroup());
        $this->assertSame('another-ticket', $metadata->asArray()[1]->groupName());
    }
}
