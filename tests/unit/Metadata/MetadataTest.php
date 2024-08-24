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
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\Version\ComparisonRequirement;
use PHPUnit\TestFixture\CoveredTrait;
use PHPUnit\Util\VersionComparisonOperator;

#[CoversClass(After::class)]
#[CoversClass(AfterClass::class)]
#[CoversClass(BackupGlobals::class)]
#[CoversClass(BackupStaticProperties::class)]
#[CoversClass(Before::class)]
#[CoversClass(BeforeClass::class)]
#[CoversClass(Covers::class)]
#[CoversClass(\PHPUnit\Metadata\CoversClass::class)]
#[CoversClass(CoversDefaultClass::class)]
#[CoversClass(CoversTrait::class)]
#[CoversClass(CoversFunction::class)]
#[CoversClass(CoversMethod::class)]
#[CoversClass(UsesMethod::class)]
#[CoversClass(CoversNothing::class)]
#[CoversClass(DataProvider::class)]
#[CoversClass(DependsOnClass::class)]
#[CoversClass(DependsOnMethod::class)]
#[CoversClass(DisableReturnValueGenerationForTestDoubles::class)]
#[CoversClass(DoesNotPerformAssertions::class)]
#[CoversClass(ExcludeGlobalVariableFromBackup::class)]
#[CoversClass(ExcludeStaticPropertyFromBackup::class)]
#[CoversClass(\PHPUnit\Metadata\Group::class)]
#[CoversClass(IgnoreDeprecations::class)]
#[CoversClass(IgnorePhpunitDeprecations::class)]
#[CoversClass(Metadata::class)]
#[CoversClass(PostCondition::class)]
#[CoversClass(PreCondition::class)]
#[CoversClass(PreserveGlobalState::class)]
#[CoversClass(RequiresFunction::class)]
#[CoversClass(RequiresMethod::class)]
#[CoversClass(RequiresOperatingSystem::class)]
#[CoversClass(RequiresOperatingSystemFamily::class)]
#[CoversClass(RequiresPhp::class)]
#[CoversClass(RequiresPhpExtension::class)]
#[CoversClass(RequiresPhpunit::class)]
#[CoversClass(RequiresPhpunitExtension::class)]
#[CoversClass(RequiresSetting::class)]
#[CoversClass(RunClassInSeparateProcess::class)]
#[CoversClass(RunInSeparateProcess::class)]
#[CoversClass(RunTestsInSeparateProcesses::class)]
#[CoversClass(Test::class)]
#[CoversClass(TestDox::class)]
#[CoversClass(TestWith::class)]
#[CoversClass(Uses::class)]
#[CoversClass(UsesClass::class)]
#[CoversClass(UsesDefaultClass::class)]
#[CoversClass(UsesTrait::class)]
#[CoversClass(UsesFunction::class)]
#[CoversClass(WithoutErrorHandler::class)]
#[Small]
#[Group('metadata')]
final class MetadataTest extends TestCase
{
    public function testCanBeAfter(): void
    {
        $metadata = Metadata::after(0);

        $this->assertTrue($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertTrue($metadata->isMethodLevel());
        $this->assertFalse($metadata->isClassLevel());
    }

    public function testCanBeAfterClass(): void
    {
        $metadata = Metadata::afterClass(0);

        $this->assertFalse($metadata->isAfter());
        $this->assertTrue($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertTrue($metadata->isMethodLevel());
        $this->assertFalse($metadata->isClassLevel());
    }

    public function testCanBeBackupGlobalsOnClass(): void
    {
        $metadata = Metadata::backupGlobalsOnClass(false);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertTrue($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertFalse($metadata->enabled());
        $this->assertTrue($metadata->isClassLevel());
        $this->assertFalse($metadata->isMethodLevel());
    }

    public function testCanBeBackupGlobalsOnMethod(): void
    {
        $metadata = Metadata::backupGlobalsOnMethod(false);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertTrue($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertFalse($metadata->enabled());
        $this->assertTrue($metadata->isMethodLevel());
        $this->assertFalse($metadata->isClassLevel());
    }

    public function testCanBeBackupStaticPropertiesOnClass(): void
    {
        $metadata = Metadata::backupStaticPropertiesOnClass(false);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertTrue($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertFalse($metadata->enabled());
        $this->assertTrue($metadata->isClassLevel());
        $this->assertFalse($metadata->isMethodLevel());
    }

    public function testCanBeBackupStaticPropertiesOnMethod(): void
    {
        $metadata = Metadata::backupStaticPropertiesOnMethod(false);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertTrue($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertFalse($metadata->enabled());
        $this->assertTrue($metadata->isMethodLevel());
        $this->assertFalse($metadata->isClassLevel());
    }

    public function testCanBeBeforeClass(): void
    {
        $metadata = Metadata::beforeClass(0);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertTrue($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertTrue($metadata->isMethodLevel());
        $this->assertFalse($metadata->isClassLevel());
    }

    public function testCanBeBefore(): void
    {
        $metadata = Metadata::before(0);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertTrue($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertTrue($metadata->isMethodLevel());
        $this->assertFalse($metadata->isClassLevel());
    }

    public function testCanBeCoversOnClass(): void
    {
        $metadata = Metadata::coversOnClass(self::class);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertTrue($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame(self::class, $metadata->target());
        $this->assertTrue($metadata->isClassLevel());
        $this->assertFalse($metadata->isMethodLevel());
    }

    public function testCanBeCoversOnMethod(): void
    {
        $metadata = Metadata::coversOnMethod(self::class);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertTrue($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame(self::class, $metadata->target());
        $this->assertTrue($metadata->isMethodLevel());
        $this->assertFalse($metadata->isClassLevel());
    }

    public function testCanBeCoversClass(): void
    {
        $metadata = Metadata::coversClass(self::class);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertTrue($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame(self::class, $metadata->className());
        $this->assertSame(self::class, $metadata->asStringForCodeUnitMapper());

        $this->assertTrue($metadata->isClassLevel());
        $this->assertFalse($metadata->isMethodLevel());
    }

    public function testCanBeCoversDefaultClass(): void
    {
        $metadata = Metadata::coversDefaultClass(self::class);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertTrue($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame(self::class, $metadata->className());

        $this->assertTrue($metadata->isClassLevel());
        $this->assertFalse($metadata->isMethodLevel());
    }

    public function testCanBeCoversTrait(): void
    {
        $metadata = Metadata::coversTrait(CoveredTrait::class);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertTrue($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame(CoveredTrait::class, $metadata->traitName());
        $this->assertSame(CoveredTrait::class, $metadata->asStringForCodeUnitMapper());

        $this->assertTrue($metadata->isClassLevel());
        $this->assertFalse($metadata->isMethodLevel());
    }

    public function testCanBeCoversFunction(): void
    {
        $metadata = Metadata::coversFunction('f');

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertTrue($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame('f', $metadata->functionName());
        $this->assertSame('::f', $metadata->asStringForCodeUnitMapper());

        $this->assertTrue($metadata->isClassLevel());
        $this->assertFalse($metadata->isMethodLevel());
    }

    public function testCanBeCoversMethod(): void
    {
        $metadata = Metadata::coversMethod(self::class, 'testCanBeCoversMethod');

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertTrue($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame(self::class, $metadata->className());
        $this->assertSame('testCanBeCoversMethod', $metadata->methodName());
        $this->assertSame(self::class . '::testCanBeCoversMethod', $metadata->asStringForCodeUnitMapper());

        $this->assertTrue($metadata->isClassLevel());
        $this->assertFalse($metadata->isMethodLevel());
    }

    public function testCanBeCoversNothingOnMethod(): void
    {
        $metadata = Metadata::coversNothingOnMethod();

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertTrue($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertTrue($metadata->isMethodLevel());
        $this->assertFalse($metadata->isClassLevel());
    }

    public function testCanBeCoversNothingOnClass(): void
    {
        $metadata = Metadata::coversNothingOnClass();

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertTrue($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertTrue($metadata->isClassLevel());
        $this->assertFalse($metadata->isMethodLevel());
    }

    public function testCanBeDataProvider(): void
    {
        $metadata = Metadata::dataProvider(self::class, 'method');

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertTrue($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame(self::class, $metadata->className());
        $this->assertSame('method', $metadata->methodName());

        $this->assertTrue($metadata->isMethodLevel());
        $this->assertFalse($metadata->isClassLevel());
    }

    public function testCanBeDependsOnClass(): void
    {
        $metadata = Metadata::dependsOnClass(self::class, false, false);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertTrue($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame(self::class, $metadata->className());
        $this->assertFalse($metadata->deepClone());
        $this->assertFalse($metadata->shallowClone());

        $this->assertTrue($metadata->isMethodLevel());
        $this->assertFalse($metadata->isClassLevel());
    }

    public function testCanBeDependsOnMethod(): void
    {
        $metadata = Metadata::dependsOnMethod(self::class, 'method', false, false);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertTrue($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame(self::class, $metadata->className());
        $this->assertSame('method', $metadata->methodName());
        $this->assertFalse($metadata->deepClone());
        $this->assertFalse($metadata->shallowClone());

        $this->assertTrue($metadata->isMethodLevel());
        $this->assertFalse($metadata->isClassLevel());
    }

    public function testCanBeDisableReturnValueGenerationForTestDoubles(): void
    {
        $metadata = Metadata::disableReturnValueGenerationForTestDoubles();

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertTrue($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertTrue($metadata->isClassLevel());
        $this->assertFalse($metadata->isMethodLevel());
    }

    public function testCanBeDoesNotPerformAssertionsOnClass(): void
    {
        $metadata = Metadata::doesNotPerformAssertionsOnClass();

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertTrue($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertTrue($metadata->isClassLevel());
        $this->assertFalse($metadata->isMethodLevel());
    }

    public function testCanBeDoesNotPerformAssertionsOnMethod(): void
    {
        $metadata = Metadata::doesNotPerformAssertionsOnMethod();

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertTrue($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertTrue($metadata->isMethodLevel());
        $this->assertFalse($metadata->isClassLevel());
    }

    public function testCanBeExcludeGlobalVariableFromBackupOnClass(): void
    {
        $metadata = Metadata::excludeGlobalVariableFromBackupOnClass('variable');

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertTrue($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame('variable', $metadata->globalVariableName());
        $this->assertTrue($metadata->isClassLevel());
        $this->assertFalse($metadata->isMethodLevel());
    }

    public function testCanBeExcludeGlobalVariableFromBackupOnMethod(): void
    {
        $metadata = Metadata::excludeGlobalVariableFromBackupOnMethod('variable');

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertTrue($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame('variable', $metadata->globalVariableName());
        $this->assertTrue($metadata->isMethodLevel());
        $this->assertFalse($metadata->isClassLevel());
    }

    public function testCanBeExcludeStaticPropertyFromBackupOnClass(): void
    {
        $metadata = Metadata::excludeStaticPropertyFromBackupOnClass('class', 'property');

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertTrue($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame('class', $metadata->className());
        $this->assertSame('property', $metadata->propertyName());

        $this->assertTrue($metadata->isClassLevel());
        $this->assertFalse($metadata->isMethodLevel());
    }

    public function testCanBeExcludeStaticPropertyFromBackupOnMethod(): void
    {
        $metadata = Metadata::excludeStaticPropertyFromBackupOnMethod('class', 'property');

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertTrue($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame('class', $metadata->className());
        $this->assertSame('property', $metadata->propertyName());

        $this->assertTrue($metadata->isMethodLevel());
        $this->assertFalse($metadata->isClassLevel());
    }

    public function testCanBeGroupOnClass(): void
    {
        $metadata = Metadata::groupOnClass('name');

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertTrue($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame('name', $metadata->groupName());

        $this->assertTrue($metadata->isClassLevel());
        $this->assertFalse($metadata->isMethodLevel());
    }

    public function testCanBeIgnoreDeprecationsOnClass(): void
    {
        $metadata = Metadata::ignoreDeprecationsOnClass();

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertTrue($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertTrue($metadata->isClassLevel());
        $this->assertFalse($metadata->isMethodLevel());
    }

    public function testCanBeIgnoreDeprecationsOnMethod(): void
    {
        $metadata = Metadata::ignoreDeprecationsOnMethod();

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertTrue($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertTrue($metadata->isMethodLevel());
        $this->assertFalse($metadata->isClassLevel());
    }

    public function testCanBeIgnorePhpunitDeprecationsOnClass(): void
    {
        $metadata = Metadata::ignorePhpunitDeprecationsOnClass();

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertTrue($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertTrue($metadata->isClassLevel());
        $this->assertFalse($metadata->isMethodLevel());
    }

    public function testCanBeIgnorePhpunitDeprecationsOnMethod(): void
    {
        $metadata = Metadata::ignorePhpunitDeprecationsOnMethod();

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertTrue($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertTrue($metadata->isMethodLevel());
        $this->assertFalse($metadata->isClassLevel());
    }

    public function testCanBeGroupOnMethod(): void
    {
        $metadata = Metadata::groupOnMethod('name');

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertTrue($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame('name', $metadata->groupName());

        $this->assertTrue($metadata->isMethodLevel());
        $this->assertFalse($metadata->isClassLevel());
    }

    public function testCanBeRunTestsInSeparateProcesses(): void
    {
        $metadata = Metadata::runTestsInSeparateProcesses();

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertTrue($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertTrue($metadata->isClassLevel());
        $this->assertFalse($metadata->isMethodLevel());
    }

    public function testCanBeRunClassInSeparateProcess(): void
    {
        $metadata = Metadata::runClassInSeparateProcess();

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertTrue($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertTrue($metadata->isClassLevel());
        $this->assertFalse($metadata->isMethodLevel());
    }

    public function testCanBeRunInSeparateProcess(): void
    {
        $metadata = Metadata::runInSeparateProcess();

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertTrue($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertTrue($metadata->isMethodLevel());
        $this->assertFalse($metadata->isClassLevel());
    }

    public function testCanBeTest(): void
    {
        $metadata = Metadata::test();

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertTrue($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertTrue($metadata->isMethodLevel());
        $this->assertFalse($metadata->isClassLevel());
    }

    public function testCanBePreCondition(): void
    {
        $metadata = Metadata::preCondition(0);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertTrue($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertTrue($metadata->isMethodLevel());
        $this->assertFalse($metadata->isClassLevel());
    }

    public function testCanBePostCondition(): void
    {
        $metadata = Metadata::postCondition(0);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertTrue($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertTrue($metadata->isMethodLevel());
        $this->assertFalse($metadata->isClassLevel());
    }

    public function testCanBePreserveGlobalStateOnClass(): void
    {
        $metadata = Metadata::preserveGlobalStateOnClass(false);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertTrue($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertFalse($metadata->enabled());

        $this->assertTrue($metadata->isClassLevel());
        $this->assertFalse($metadata->isMethodLevel());
    }

    public function testCanBePreserveGlobalStateOnMethod(): void
    {
        $metadata = Metadata::preserveGlobalStateOnMethod(false);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertTrue($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertFalse($metadata->enabled());

        $this->assertTrue($metadata->isMethodLevel());
        $this->assertFalse($metadata->isClassLevel());
    }

    public function testCanBeRequiresMethodOnClass(): void
    {
        $metadata = Metadata::requiresMethodOnClass(self::class, __METHOD__);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertTrue($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame(self::class, $metadata->className());
        $this->assertSame(__METHOD__, $metadata->methodName());

        $this->assertTrue($metadata->isClassLevel());
        $this->assertFalse($metadata->isMethodLevel());
    }

    public function testCanBeRequiresMethodOnMethod(): void
    {
        $metadata = Metadata::requiresMethodOnMethod(self::class, __METHOD__);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertTrue($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame(self::class, $metadata->className());
        $this->assertSame(__METHOD__, $metadata->methodName());

        $this->assertTrue($metadata->isMethodLevel());
        $this->assertFalse($metadata->isClassLevel());
    }

    public function testCanBeRequiresFunctionOnClass(): void
    {
        $metadata = Metadata::requiresFunctionOnClass('f');

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertTrue($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame('f', $metadata->functionName());

        $this->assertTrue($metadata->isClassLevel());
        $this->assertFalse($metadata->isMethodLevel());
    }

    public function testCanBeRequiresFunctionOnMethod(): void
    {
        $metadata = Metadata::requiresFunctionOnMethod('f');

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertTrue($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame('f', $metadata->functionName());

        $this->assertTrue($metadata->isMethodLevel());
        $this->assertFalse($metadata->isClassLevel());
    }

    public function testCanBeRequiresOperatingSystemOnClass(): void
    {
        $metadata = Metadata::requiresOperatingSystemOnClass('Linux');

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertTrue($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame('Linux', $metadata->operatingSystem());

        $this->assertTrue($metadata->isClassLevel());
        $this->assertFalse($metadata->isMethodLevel());
    }

    public function testCanBeRequiresOperatingSystemOnMethod(): void
    {
        $metadata = Metadata::requiresOperatingSystemOnMethod('Linux');

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertTrue($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame('Linux', $metadata->operatingSystem());

        $this->assertTrue($metadata->isMethodLevel());
        $this->assertFalse($metadata->isClassLevel());
    }

    public function testCanBeRequiresOperatingSystemFamilyOnClass(): void
    {
        $metadata = Metadata::requiresOperatingSystemFamilyOnClass('Linux');

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertTrue($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame('Linux', $metadata->operatingSystemFamily());

        $this->assertTrue($metadata->isClassLevel());
        $this->assertFalse($metadata->isMethodLevel());
    }

    public function testCanBeRequiresOperatingSystemFamilyOnMethod(): void
    {
        $metadata = Metadata::requiresOperatingSystemFamilyOnMethod('Linux');

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertTrue($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame('Linux', $metadata->operatingSystemFamily());

        $this->assertTrue($metadata->isMethodLevel());
        $this->assertFalse($metadata->isClassLevel());
    }

    public function testCanBeRequiresPhpOnClass(): void
    {
        $metadata = Metadata::requiresPhpOnClass(
            new ComparisonRequirement(
                '8.0.0',
                new VersionComparisonOperator('>='),
            ),
        );

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertTrue($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame('>= 8.0.0', $metadata->versionRequirement()->asString());

        $this->assertTrue($metadata->isClassLevel());
        $this->assertFalse($metadata->isMethodLevel());
    }

    public function testCanBeRequiresPhpOnMethod(): void
    {
        $metadata = Metadata::requiresPhpOnMethod(
            new ComparisonRequirement(
                '8.0.0',
                new VersionComparisonOperator('>='),
            ),
        );

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertTrue($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame('>= 8.0.0', $metadata->versionRequirement()->asString());

        $this->assertTrue($metadata->isMethodLevel());
        $this->assertFalse($metadata->isClassLevel());
    }

    public function testCanBeRequiresPhpExtensionOnClass(): void
    {
        $metadata = Metadata::requiresPhpExtensionOnClass('test', null);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertTrue($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame('test', $metadata->extension());
        $this->assertFalse($metadata->hasVersionRequirement());

        $this->expectException(NoVersionRequirementException::class);
        $metadata->versionRequirement();

        $this->assertTrue($metadata->isClassLevel());
        $this->assertFalse($metadata->isMethodLevel());
    }

    public function testCanBeRequiresPhpExtensionWithVersionOnClass(): void
    {
        $metadata = Metadata::requiresPhpExtensionOnClass(
            'test',
            new ComparisonRequirement(
                '1.0.0',
                new VersionComparisonOperator('>='),
            ),
        );

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertTrue($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame('test', $metadata->extension());
        $this->assertTrue($metadata->hasVersionRequirement());
        $this->assertSame('>= 1.0.0', $metadata->versionRequirement()->asString());

        $this->assertTrue($metadata->isClassLevel());
        $this->assertFalse($metadata->isMethodLevel());
    }

    public function testCanBeRequiresPhpExtensionOnMethod(): void
    {
        $metadata = Metadata::requiresPhpExtensionOnMethod('test', null);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertTrue($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame('test', $metadata->extension());
        $this->assertFalse($metadata->hasVersionRequirement());

        $this->expectException(NoVersionRequirementException::class);
        $metadata->versionRequirement();

        $this->assertTrue($metadata->isMethodLevel());
        $this->assertFalse($metadata->isClassLevel());
    }

    public function testCanBeRequiresPhpExtensionWithVersionOnMethod(): void
    {
        $metadata = Metadata::requiresPhpExtensionOnMethod(
            'test',
            new ComparisonRequirement(
                '1.0.0',
                new VersionComparisonOperator('>='),
            ),
        );

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertTrue($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame('test', $metadata->extension());
        $this->assertTrue($metadata->hasVersionRequirement());
        $this->assertSame('>= 1.0.0', $metadata->versionRequirement()->asString());

        $this->assertTrue($metadata->isMethodLevel());
        $this->assertFalse($metadata->isClassLevel());
    }

    public function testCanBeRequiresPhpunitOnClass(): void
    {
        $metadata = Metadata::requiresPhpunitOnClass(
            new ComparisonRequirement(
                '10.0.0',
                new VersionComparisonOperator('>='),
            ),
        );

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertTrue($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame('>= 10.0.0', $metadata->versionRequirement()->asString());

        $this->assertTrue($metadata->isClassLevel());
        $this->assertFalse($metadata->isMethodLevel());
    }

    public function testCanBeRequiresPhpunitExtensionOnClass(): void
    {
        $metadata = Metadata::requiresPhpunitExtensionOnClass(SomeExtension::class);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertTrue($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame(SomeExtension::class, $metadata->extensionClass());

        $this->assertTrue($metadata->isClassLevel());
        $this->assertFalse($metadata->isMethodLevel());
    }

    public function testCanBeRequiresPhpunitOnMethod(): void
    {
        $metadata = Metadata::requiresPhpunitOnMethod(
            new ComparisonRequirement(
                '10.0.0',
                new VersionComparisonOperator('>='),
            ),
        );

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertTrue($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame('>= 10.0.0', $metadata->versionRequirement()->asString());

        $this->assertTrue($metadata->isMethodLevel());
        $this->assertFalse($metadata->isClassLevel());
    }

    public function testCanBeRequiresPhpunitExtensionOnMethod(): void
    {
        $metadata = Metadata::requiresPhpunitExtensionOnMethod(SomeExtension::class);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertTrue($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame(SomeExtension::class, $metadata->extensionClass());

        $this->assertTrue($metadata->isMethodLevel());
        $this->assertFalse($metadata->isClassLevel());
    }

    public function testCanBeRequiresSettingOnClass(): void
    {
        $metadata = Metadata::requiresSettingOnClass('foo', 'bar');

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertTrue($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame('foo', $metadata->setting());
        $this->assertSame('bar', $metadata->value());

        $this->assertTrue($metadata->isClassLevel());
        $this->assertFalse($metadata->isMethodLevel());
    }

    public function testCanBeRequiresSettingOnMethod(): void
    {
        $metadata = Metadata::requiresSettingOnMethod('foo', 'bar');

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertTrue($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame('foo', $metadata->setting());
        $this->assertSame('bar', $metadata->value());

        $this->assertTrue($metadata->isMethodLevel());
        $this->assertFalse($metadata->isClassLevel());
    }

    public function testCanBeTestDoxOnClass(): void
    {
        $metadata = Metadata::testDoxOnClass('text');

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertTrue($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame('text', $metadata->text());

        $this->assertTrue($metadata->isClassLevel());
        $this->assertFalse($metadata->isMethodLevel());
    }

    public function testCanBeTestDoxOnMethod(): void
    {
        $metadata = Metadata::testDoxOnMethod('text');

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertTrue($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame('text', $metadata->text());

        $this->assertTrue($metadata->isMethodLevel());
        $this->assertFalse($metadata->isClassLevel());
    }

    public function testCanBeTestWith(): void
    {
        $metadata = Metadata::testWith(['a', 'b']);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertTrue($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame(['a', 'b'], $metadata->data());
        $this->assertFalse($metadata->hasName());
        $this->assertNull($metadata->name());

        $this->assertTrue($metadata->isMethodLevel());
        $this->assertFalse($metadata->isClassLevel());
    }

    public function testCanBeUsesOnClass(): void
    {
        $metadata = Metadata::usesOnClass(self::class);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertTrue($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame(self::class, $metadata->target());

        $this->assertTrue($metadata->isClassLevel());
        $this->assertFalse($metadata->isMethodLevel());
    }

    public function testCanBeUsesOnMethod(): void
    {
        $metadata = Metadata::usesOnMethod(self::class);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertTrue($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame(self::class, $metadata->target());

        $this->assertTrue($metadata->isMethodLevel());
        $this->assertFalse($metadata->isClassLevel());
    }

    public function testCanBeUsesClass(): void
    {
        $metadata = Metadata::usesClass(self::class);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertTrue($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame(self::class, $metadata->className());
        $this->assertSame(self::class, $metadata->asStringForCodeUnitMapper());

        $this->assertTrue($metadata->isClassLevel());
        $this->assertFalse($metadata->isMethodLevel());
    }

    public function testCanBeUsesDefaultClass(): void
    {
        $metadata = Metadata::usesDefaultClass(self::class);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertTrue($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame(self::class, $metadata->className());

        $this->assertTrue($metadata->isClassLevel());
        $this->assertFalse($metadata->isMethodLevel());
    }

    public function testCanBeUsesTrait(): void
    {
        $metadata = Metadata::usesTrait(CoveredTrait::class);

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertTrue($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame(CoveredTrait::class, $metadata->traitName());
        $this->assertSame(CoveredTrait::class, $metadata->asStringForCodeUnitMapper());

        $this->assertTrue($metadata->isClassLevel());
        $this->assertFalse($metadata->isMethodLevel());
    }

    public function testCanBeUsesFunction(): void
    {
        $metadata = Metadata::usesFunction('f');

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertTrue($metadata->isUsesFunction());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame('f', $metadata->functionName());
        $this->assertSame('::f', $metadata->asStringForCodeUnitMapper());

        $this->assertTrue($metadata->isClassLevel());
        $this->assertFalse($metadata->isMethodLevel());
    }

    public function testCanBeUsesMethod(): void
    {
        $metadata = Metadata::usesMethod(self::class, 'testCanBeUsesMethod');

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertTrue($metadata->isUsesMethod());
        $this->assertFalse($metadata->isWithoutErrorHandler());

        $this->assertSame(self::class, $metadata->className());
        $this->assertSame('testCanBeUsesMethod', $metadata->methodName());
        $this->assertSame(self::class . '::testCanBeUsesMethod', $metadata->asStringForCodeUnitMapper());

        $this->assertTrue($metadata->isClassLevel());
        $this->assertFalse($metadata->isMethodLevel());
    }

    public function testCanBeWithoutErrorHandler(): void
    {
        $metadata = Metadata::withoutErrorHandler();

        $this->assertFalse($metadata->isAfter());
        $this->assertFalse($metadata->isAfterClass());
        $this->assertFalse($metadata->isBackupGlobals());
        $this->assertFalse($metadata->isBackupStaticProperties());
        $this->assertFalse($metadata->isBeforeClass());
        $this->assertFalse($metadata->isBefore());
        $this->assertFalse($metadata->isCovers());
        $this->assertFalse($metadata->isCoversClass());
        $this->assertFalse($metadata->isCoversDefaultClass());
        $this->assertFalse($metadata->isCoversTrait());
        $this->assertFalse($metadata->isCoversFunction());
        $this->assertFalse($metadata->isCoversMethod());
        $this->assertFalse($metadata->isCoversNothing());
        $this->assertFalse($metadata->isDataProvider());
        $this->assertFalse($metadata->isDependsOnClass());
        $this->assertFalse($metadata->isDependsOnMethod());
        $this->assertFalse($metadata->isDisableReturnValueGenerationForTestDoubles());
        $this->assertFalse($metadata->isDoesNotPerformAssertions());
        $this->assertFalse($metadata->isExcludeGlobalVariableFromBackup());
        $this->assertFalse($metadata->isExcludeStaticPropertyFromBackup());
        $this->assertFalse($metadata->isGroup());
        $this->assertFalse($metadata->isIgnoreDeprecations());
        $this->assertFalse($metadata->isIgnorePhpunitDeprecations());
        $this->assertFalse($metadata->isRunClassInSeparateProcess());
        $this->assertFalse($metadata->isRunInSeparateProcess());
        $this->assertFalse($metadata->isRunTestsInSeparateProcesses());
        $this->assertFalse($metadata->isTest());
        $this->assertFalse($metadata->isPreCondition());
        $this->assertFalse($metadata->isPostCondition());
        $this->assertFalse($metadata->isPreserveGlobalState());
        $this->assertFalse($metadata->isRequiresMethod());
        $this->assertFalse($metadata->isRequiresFunction());
        $this->assertFalse($metadata->isRequiresOperatingSystem());
        $this->assertFalse($metadata->isRequiresOperatingSystemFamily());
        $this->assertFalse($metadata->isRequiresPhp());
        $this->assertFalse($metadata->isRequiresPhpExtension());
        $this->assertFalse($metadata->isRequiresPhpunit());
        $this->assertFalse($metadata->isRequiresPhpunitExtension());
        $this->assertFalse($metadata->isRequiresSetting());
        $this->assertFalse($metadata->isTestDox());
        $this->assertFalse($metadata->isTestWith());
        $this->assertFalse($metadata->isUses());
        $this->assertFalse($metadata->isUsesClass());
        $this->assertFalse($metadata->isUsesDefaultClass());
        $this->assertFalse($metadata->isUsesTrait());
        $this->assertFalse($metadata->isUsesFunction());
        $this->assertFalse($metadata->isUsesMethod());
        $this->assertTrue($metadata->isWithoutErrorHandler());

        $this->assertTrue($metadata->isMethodLevel());
        $this->assertFalse($metadata->isClassLevel());
    }
}
