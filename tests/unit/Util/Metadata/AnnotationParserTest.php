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
 *
 * @small
 */
final class AnnotationParserTest extends TestCase
{
    public function test_Parses_backupGlobals_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(BackupGlobalsTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBackupGlobals());
        $this->assertTrue($metadata->asArray()[0]->enabled());
    }

    public function test_Parses_backupStaticAttributes_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(BackupStaticPropertiesTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBackupStaticProperties());
        $this->assertTrue($metadata->asArray()[0]->enabled());
    }

    public function test_Parses_codeCoverageIgnore_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(Example::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isCodeCoverageIgnore());
    }

    public function test_Parses_covers_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(CoversTest::class);

        $this->assertCount(4, $metadata);

        $this->assertTrue($metadata->asArray()[0]->isCovers());
        $this->assertSame('::\PHPUnit\TestFixture\Metadata\Annotation\f', $metadata->asArray()[0]->target());

        $this->assertTrue($metadata->asArray()[1]->isCovers());
        $this->assertSame('\PHPUnit\TestFixture\Metadata\Annotation\Example', $metadata->asArray()[1]->target());
    }

    public function test_Parses_coversDefaultClass_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(CoversTest::class);

        $this->assertCount(4, $metadata);

        $this->assertTrue($metadata->asArray()[3]->isCoversDefaultClass());
        $this->assertSame('\PHPUnit\TestFixture\Metadata\Annotation\Example', $metadata->asArray()[3]->className());
    }

    public function test_Parses_coversNothing_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(CoversTest::class);

        $this->assertCount(4, $metadata);
        $this->assertTrue($metadata->asArray()[2]->isCoversNothing());
    }

    public function test_Parses_doesNotPerformAssertions_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(DoesNotPerformAssertionsTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isDoesNotPerformAssertions());
    }

    public function test_Parses_group_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(GroupTest::class);

        $this->assertCount(2, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('group', $metadata->asArray()[0]->groupName());
    }

    public function test_Parses_large_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(LargeTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('large', $metadata->asArray()[0]->groupName());
    }

    public function test_Parses_medium_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(MediumTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('medium', $metadata->asArray()[0]->groupName());
    }

    public function test_Parses_preserveGlobalState_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(PreserveGlobalStateTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isPreserveGlobalState());
        $this->assertTrue($metadata->asArray()[0]->enabled());
    }

    public function test_Parses_requiresFunction_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(RequiresFunctionTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresFunction());
        $this->assertSame('f', $metadata->asArray()[0]->functionName());
    }

    public function test_Parses_requiresOperatingSystem_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(RequiresOperatingSystemTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresOperatingSystem());
        $this->assertSame('Linux', $metadata->asArray()[0]->regularExpression());
    }

    public function test_Parses_requiresOperatingSystemFamily_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(RequiresOperatingSystemFamilyTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresOperatingSystemFamily());
        $this->assertSame('Linux', $metadata->asArray()[0]->operatingSystemFamily());
    }

    public function test_Parses_requiresPhp_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(RequiresPhpTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresPhp());
        $this->assertSame('8.0.0', $metadata->asArray()[0]->versionRequirement());
    }

    public function test_Parses_requiresPhpExtension_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(RequiresPhpExtensionTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresPhpExtension());
        $this->assertSame('foo', $metadata->asArray()[0]->extension());
        $this->assertNull($metadata->asArray()[0]->versionRequirement());
        $this->assertFalse($metadata->asArray()[0]->hasVersionRequirement());
    }

    public function test_Parses_requiresPhpunit_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(RequiresPhpunitTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresPhpunit());
        $this->assertSame('10.0.0', $metadata->asArray()[0]->versionRequirement());
    }

    public function test_Parses_runTestsInSeparateProcesses_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(ProcessIsolationTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRunTestsInSeparateProcesses());
    }

    public function test_Parses_small_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(SmallTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('small', $metadata->asArray()[0]->groupName());
    }

    public function test_Parses_testdox_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(TestDoxTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isTestDox());
        $this->assertSame('text', $metadata->asArray()[0]->text());
    }

    public function test_Parses_ticket_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(GroupTest::class);

        $this->assertCount(2, $metadata);
        $this->assertTrue($metadata->asArray()[1]->isGroup());
        $this->assertSame('ticket', $metadata->asArray()[1]->groupName());
    }

    public function test_Parses_uses_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(UsesTest::class);

        $this->assertCount(3, $metadata);

        $this->assertTrue($metadata->asArray()[0]->isUses());
        $this->assertSame('::\PHPUnit\TestFixture\Metadata\Annotation\f', $metadata->asArray()[0]->target());

        $this->assertTrue($metadata->asArray()[1]->isUses());
        $this->assertSame('\PHPUnit\TestFixture\Metadata\Annotation\Example', $metadata->asArray()[1]->target());
    }

    public function test_Parses_usesDefaultClass_annotation_on_class(): void
    {
        $metadata = (new AnnotationParser)->forClass(UsesTest::class);

        $this->assertCount(3, $metadata);

        $this->assertTrue($metadata->asArray()[2]->isUsesDefaultClass());
        $this->assertSame('\PHPUnit\TestFixture\Metadata\Annotation\Example', $metadata->asArray()[1]->target());
    }

    public function test_Parses_after_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(SmallTest::class, 'afterTest');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isAfter());
    }

    public function test_Parses_afterClass_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(SmallTest::class, 'afterTests');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isAfterClass());
    }

    public function test_Parses_backupGlobals_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(BackupGlobalsTest::class, 'testOne');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBackupGlobals());
        $this->assertFalse($metadata->asArray()[0]->enabled());
    }

    public function test_Parses_backupStaticProperties_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(BackupStaticPropertiesTest::class, 'testOne');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBackupStaticProperties());
        $this->assertFalse($metadata->asArray()[0]->enabled());
    }

    public function test_Parses_before_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(SmallTest::class, 'beforeTest');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBefore());
    }

    public function test_Parses_beforeClass_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(SmallTest::class, 'beforeTests');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isBeforeClass());
    }

    public function test_Parses_codeCoverageIgnore_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(Example::class, 'method');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isCodeCoverageIgnore());
    }

    public function test_Parses_coversNothing_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(CoversTest::class, 'testOne');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isCoversNothing());
    }

    public function test_Parses_dataProvider_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(SmallTest::class, 'testWithDataProvider');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isDataProvider());
        $this->assertSame(SmallTest::class, $metadata->asArray()[0]->className());
        $this->assertSame('provider', $metadata->asArray()[0]->methodName());
    }

    public function test_Parses_depends_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(SmallTest::class, 'testWithDepends');

        $this->assertCount(3, $metadata);

        $this->assertTrue($metadata->asArray()[0]->isDepends());
        $this->assertSame(SmallTest::class, $metadata->asArray()[0]->className());
        $this->assertSame('one', $metadata->asArray()[0]->methodName());
        $this->assertFalse($metadata->asArray()[0]->deepClone());
        $this->assertFalse($metadata->asArray()[0]->shallowClone());

        $this->assertTrue($metadata->asArray()[1]->isDepends());
        $this->assertSame(SmallTest::class, $metadata->asArray()[1]->className());
        $this->assertSame('one', $metadata->asArray()[1]->methodName());
        $this->assertTrue($metadata->asArray()[1]->deepClone());
        $this->assertFalse($metadata->asArray()[1]->shallowClone());

        $this->assertTrue($metadata->asArray()[2]->isDepends());
        $this->assertSame(SmallTest::class, $metadata->asArray()[2]->className());
        $this->assertSame('one', $metadata->asArray()[2]->methodName());
        $this->assertFalse($metadata->asArray()[2]->deepClone());
        $this->assertTrue($metadata->asArray()[2]->shallowClone());
    }

    public function test_Parses_doesNotPerformAssertions_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(DoesNotPerformAssertionsTest::class, 'testOne');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isDoesNotPerformAssertions());
    }

    public function test_Parses_group_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(GroupTest::class, 'testOne');

        $this->assertCount(2, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('another-group', $metadata->asArray()[0]->groupName());
    }

    public function test_Parses_large_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(LargeTest::class, 'testWithLargeAnnotation');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('large', $metadata->asArray()[0]->groupName());
    }

    public function test_Parses_medium_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(MediumTest::class, 'testWithMediumAnnotation');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('medium', $metadata->asArray()[0]->groupName());
    }

    public function test_Parses_postCondition_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(SmallTest::class, 'postCondition');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isPostCondition());
    }

    public function test_Parses_preCondition_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(SmallTest::class, 'preCondition');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isPreCondition());
    }

    public function test_Parses_preserveGlobalState_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(PreserveGlobalStateTest::class, 'testOne');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isPreserveGlobalState());
        $this->assertFalse($metadata->asArray()[0]->enabled());
    }

    public function test_Parses_requiresFunction_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(RequiresFunctionTest::class, 'testOne');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresFunction());
        $this->assertSame('g', $metadata->asArray()[0]->functionName());
    }

    public function test_Parses_requiresOperatingSystem_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forClass(RequiresOperatingSystemTest::class);

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresOperatingSystem());
        $this->assertSame('Linux', $metadata->asArray()[0]->regularExpression());
    }

    public function test_Parses_requiresOperatingSystemFamily_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(RequiresOperatingSystemFamilyTest::class, 'testOne');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresOperatingSystemFamily());
        $this->assertSame('Linux', $metadata->asArray()[0]->operatingSystemFamily());
    }

    public function test_Parses_requiresPhp_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(RequiresPhpTest::class, 'testOne');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresPhp());
        $this->assertSame('< 9.0.0', $metadata->asArray()[0]->versionRequirement());
    }

    public function test_Parses_requiresPhpExtension_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(RequiresPhpExtensionTest::class, 'testOne');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresPhpExtension());
        $this->assertSame('bar', $metadata->asArray()[0]->extension());
        $this->assertSame('>= 1.0.0', $metadata->asArray()[0]->versionRequirement());
        $this->assertTrue($metadata->asArray()[0]->hasVersionRequirement());

        $metadata = (new AnnotationParser)->forMethod(RequiresPhpExtensionTest::class, 'testTwo');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresPhpExtension());
        $this->assertSame('baz', $metadata->asArray()[0]->extension());
        $this->assertSame('< 2.0.0', $metadata->asArray()[0]->versionRequirement());
        $this->assertTrue($metadata->asArray()[0]->hasVersionRequirement());
    }

    public function test_Parses_requiresPhpunit_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(RequiresPhpunitTest::class, 'testOne');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRequiresPhpunit());
        $this->assertSame('< 11.0.0', $metadata->asArray()[0]->versionRequirement());
    }

    public function test_Parses_runInSeparateProcess_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(ProcessIsolationTest::class, 'testOne');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isRunInSeparateProcess());
    }

    public function test_Parses_small_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(SmallTest::class, 'testWithSmallAnnotation');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isGroup());
        $this->assertSame('small', $metadata->asArray()[0]->groupName());
    }

    public function test_Parses_test_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(SmallTest::class, 'one');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isTest());
    }

    public function test_Parses_testdox_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(TestDoxTest::class, 'testOne');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isTestDox());
        $this->assertSame('text', $metadata->asArray()[0]->text());
    }

    public function test_Parses_testWith_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(TestWithTest::class, 'testOne');

        $this->assertCount(1, $metadata);
        $this->assertTrue($metadata->asArray()[0]->isTestWith());
        $this->assertSame([1, 2, 3], $metadata->asArray()[0]->data());
    }

    public function test_Parses_ticket_annotation_on_method(): void
    {
        $metadata = (new AnnotationParser)->forMethod(GroupTest::class, 'testOne');

        $this->assertCount(2, $metadata);
        $this->assertTrue($metadata->asArray()[1]->isGroup());
        $this->assertSame('another-ticket', $metadata->asArray()[1]->groupName());
    }
}
