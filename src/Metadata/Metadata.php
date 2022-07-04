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

use PHPUnit\Metadata\Version\Requirement;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 * @psalm-immutable
 */
abstract class Metadata
{
    private const CLASS_LEVEL = 0;

    private const METHOD_LEVEL = 1;
    private int $level;

    final public static function after(): After
    {
        return new After(self::METHOD_LEVEL);
    }

    final public static function afterClass(): AfterClass
    {
        return new AfterClass(self::METHOD_LEVEL);
    }

    final public static function backupGlobalsOnClass(?bool $enabled): BackupGlobals
    {
        return new BackupGlobals(self::CLASS_LEVEL, $enabled);
    }

    final public static function backupGlobalsOnMethod(?bool $enabled): BackupGlobals
    {
        return new BackupGlobals(self::METHOD_LEVEL, $enabled);
    }

    final public static function backupStaticPropertiesOnClass(?bool $enabled): BackupStaticProperties
    {
        return new BackupStaticProperties(self::CLASS_LEVEL, $enabled);
    }

    final public static function backupStaticPropertiesOnMethod(?bool $enabled): BackupStaticProperties
    {
        return new BackupStaticProperties(self::METHOD_LEVEL, $enabled);
    }

    final public static function before(): Before
    {
        return new Before(self::METHOD_LEVEL);
    }

    final public static function beforeClass(): BeforeClass
    {
        return new BeforeClass(self::METHOD_LEVEL);
    }

    final public static function codeCoverageIgnoreOnClass(): CodeCoverageIgnore
    {
        return new CodeCoverageIgnore(self::CLASS_LEVEL);
    }

    final public static function codeCoverageIgnoreOnMethod(): CodeCoverageIgnore
    {
        return new CodeCoverageIgnore(self::METHOD_LEVEL);
    }

    /**
     * @psalm-param class-string $className
     */
    final public static function coversClass(string $className): CoversClass
    {
        return new CoversClass(self::CLASS_LEVEL, $className);
    }

    final public static function coversFunction(string $functionName): CoversFunction
    {
        return new CoversFunction(self::CLASS_LEVEL, $functionName);
    }

    final public static function coversOnClass(string $target): Covers
    {
        return new Covers(self::CLASS_LEVEL, $target);
    }

    final public static function coversOnMethod(string $target): Covers
    {
        return new Covers(self::METHOD_LEVEL, $target);
    }

    /**
     * @psalm-param class-string $className
     */
    final public static function coversDefaultClass(string $className): CoversDefaultClass
    {
        return new CoversDefaultClass(self::CLASS_LEVEL, $className);
    }

    final public static function coversNothingOnClass(): CoversNothing
    {
        return new CoversNothing(self::CLASS_LEVEL);
    }

    final public static function coversNothingOnMethod(): CoversNothing
    {
        return new CoversNothing(self::METHOD_LEVEL);
    }

    /**
     * @psalm-param class-string $className
     */
    final public static function dataProvider(string $className, string $methodName): DataProvider
    {
        return new DataProvider(self::METHOD_LEVEL, $className, $methodName);
    }

    final public static function dependsOnClass(string $className, bool $deepClone, bool $shallowClone): DependsOnClass
    {
        return new DependsOnClass(self::METHOD_LEVEL, $className, $deepClone, $shallowClone);
    }

    final public static function dependsOnMethod(string $className, string $methodName, bool $deepClone, bool $shallowClone): DependsOnMethod
    {
        return new DependsOnMethod(self::METHOD_LEVEL, $className, $methodName, $deepClone, $shallowClone);
    }

    final public static function doesNotPerformAssertionsOnClass(): DoesNotPerformAssertions
    {
        return new DoesNotPerformAssertions(self::CLASS_LEVEL);
    }

    final public static function doesNotPerformAssertionsOnMethod(): DoesNotPerformAssertions
    {
        return new DoesNotPerformAssertions(self::METHOD_LEVEL);
    }

    final public static function excludeGlobalVariableFromBackupOnClass(string $globalVariableName): ExcludeGlobalVariableFromBackup
    {
        return new ExcludeGlobalVariableFromBackup(self::CLASS_LEVEL, $globalVariableName);
    }

    final public static function excludeGlobalVariableFromBackupOnMethod(string $globalVariableName): ExcludeGlobalVariableFromBackup
    {
        return new ExcludeGlobalVariableFromBackup(self::METHOD_LEVEL, $globalVariableName);
    }

    /**
     * @psalm-param class-string $className
     */
    final public static function excludeStaticPropertyFromBackupOnClass(string $className, string $propertyName): ExcludeStaticPropertyFromBackup
    {
        return new ExcludeStaticPropertyFromBackup(self::CLASS_LEVEL, $className, $propertyName);
    }

    /**
     * @psalm-param class-string $className
     */
    final public static function excludeStaticPropertyFromBackupOnMethod(string $className, string $propertyName): ExcludeStaticPropertyFromBackup
    {
        return new ExcludeStaticPropertyFromBackup(self::METHOD_LEVEL, $className, $propertyName);
    }

    final public static function groupOnClass(string $groupName): Group
    {
        return new Group(self::CLASS_LEVEL, $groupName);
    }

    final public static function groupOnMethod(string $groupName): Group
    {
        return new Group(self::METHOD_LEVEL, $groupName);
    }

    final public static function postCondition(): PostCondition
    {
        return new PostCondition(self::METHOD_LEVEL);
    }

    final public static function preCondition(): PreCondition
    {
        return new PreCondition(self::METHOD_LEVEL);
    }

    final public static function preserveGlobalStateOnClass(?bool $enabled): PreserveGlobalState
    {
        return new PreserveGlobalState(self::CLASS_LEVEL, $enabled);
    }

    final public static function preserveGlobalStateOnMethod(?bool $enabled): PreserveGlobalState
    {
        return new PreserveGlobalState(self::METHOD_LEVEL, $enabled);
    }

    final public static function requiresFunctionOnClass(string $functionName): RequiresFunction
    {
        return new RequiresFunction(self::CLASS_LEVEL, $functionName);
    }

    final public static function requiresFunctionOnMethod(string $functionName): RequiresFunction
    {
        return new RequiresFunction(self::METHOD_LEVEL, $functionName);
    }

    /**
     * @psalm-param class-string $className
     */
    final public static function requiresMethodOnClass(string $className, string $methodName): RequiresMethod
    {
        return new RequiresMethod(self::CLASS_LEVEL, $className, $methodName);
    }

    /**
     * @psalm-param class-string $className
     */
    final public static function requiresMethodOnMethod(string $className, string $methodName): RequiresMethod
    {
        return new RequiresMethod(self::METHOD_LEVEL, $className, $methodName);
    }

    final public static function requiresOperatingSystemOnClass(string $operatingSystem): RequiresOperatingSystem
    {
        return new RequiresOperatingSystem(self::CLASS_LEVEL, $operatingSystem);
    }

    final public static function requiresOperatingSystemOnMethod(string $operatingSystem): RequiresOperatingSystem
    {
        return new RequiresOperatingSystem(self::METHOD_LEVEL, $operatingSystem);
    }

    final public static function requiresOperatingSystemFamilyOnClass(string $operatingSystemFamily): RequiresOperatingSystemFamily
    {
        return new RequiresOperatingSystemFamily(self::CLASS_LEVEL, $operatingSystemFamily);
    }

    final public static function requiresOperatingSystemFamilyOnMethod(string $operatingSystemFamily): RequiresOperatingSystemFamily
    {
        return new RequiresOperatingSystemFamily(self::METHOD_LEVEL, $operatingSystemFamily);
    }

    final public static function requiresPhpOnClass(Requirement $versionRequirement): RequiresPhp
    {
        return new RequiresPhp(self::CLASS_LEVEL, $versionRequirement);
    }

    final public static function requiresPhpOnMethod(Requirement $versionRequirement): RequiresPhp
    {
        return new RequiresPhp(self::METHOD_LEVEL, $versionRequirement);
    }

    final public static function requiresPhpExtensionOnClass(string $extension, ?Requirement $versionRequirement): RequiresPhpExtension
    {
        return new RequiresPhpExtension(self::CLASS_LEVEL, $extension, $versionRequirement);
    }

    final public static function requiresPhpExtensionOnMethod(string $extension, ?Requirement $versionRequirement): RequiresPhpExtension
    {
        return new RequiresPhpExtension(self::METHOD_LEVEL, $extension, $versionRequirement);
    }

    final public static function requiresPhpunitOnClass(Requirement $versionRequirement): RequiresPhpunit
    {
        return new RequiresPhpunit(self::CLASS_LEVEL, $versionRequirement);
    }

    final public static function requiresPhpunitOnMethod(Requirement $versionRequirement): RequiresPhpunit
    {
        return new RequiresPhpunit(self::METHOD_LEVEL, $versionRequirement);
    }

    final public static function requiresSettingOnClass(string $setting, string $value): RequiresSetting
    {
        return new RequiresSetting(self::CLASS_LEVEL, $setting, $value);
    }

    final public static function requiresSettingOnMethod(string $setting, string $value): RequiresSetting
    {
        return new RequiresSetting(self::METHOD_LEVEL, $setting, $value);
    }

    final public static function runClassInSeparateProcess(): RunClassInSeparateProcess
    {
        return new RunClassInSeparateProcess(self::CLASS_LEVEL);
    }

    final public static function runTestsInSeparateProcesses(): RunTestsInSeparateProcesses
    {
        return new RunTestsInSeparateProcesses(self::CLASS_LEVEL);
    }

    final public static function runInSeparateProcess(): RunInSeparateProcess
    {
        return new RunInSeparateProcess(self::METHOD_LEVEL);
    }

    final public static function test(): Test
    {
        return new Test(self::METHOD_LEVEL);
    }

    final public static function testDoxOnClass(string $text): TestDox
    {
        return new TestDox(self::CLASS_LEVEL, $text);
    }

    final public static function testDoxOnMethod(string $text): TestDox
    {
        return new TestDox(self::METHOD_LEVEL, $text);
    }

    final public static function testWith(array $data): TestWith
    {
        return new TestWith(self::METHOD_LEVEL, $data);
    }

    /**
     * @psalm-param class-string $className
     */
    final public static function usesClass(string $className): UsesClass
    {
        return new UsesClass(self::CLASS_LEVEL, $className);
    }

    final public static function usesFunction(string $functionName): UsesFunction
    {
        return new UsesFunction(self::CLASS_LEVEL, $functionName);
    }

    final public static function usesOnClass(string $target): Uses
    {
        return new Uses(self::CLASS_LEVEL, $target);
    }

    final public static function usesOnMethod(string $target): Uses
    {
        return new Uses(self::METHOD_LEVEL, $target);
    }

    /**
     * @psalm-param class-string $className
     */
    final public static function usesDefaultClass(string $className): UsesDefaultClass
    {
        return new UsesDefaultClass(self::CLASS_LEVEL, $className);
    }

    protected function __construct(int $level)
    {
        $this->level = $level;
    }

    final public function isClassLevel(): bool
    {
        return $this->level === self::CLASS_LEVEL;
    }

    final public function isMethodLevel(): bool
    {
        return $this->level === self::METHOD_LEVEL;
    }

    /**
     * @psalm-assert-if-true After $this
     */
    final public function isAfter(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true AfterClass $this
     */
    final public function isAfterClass(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true BackupGlobals $this
     */
    final public function isBackupGlobals(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true BackupStaticProperties $this
     */
    final public function isBackupStaticProperties(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true BeforeClass $this
     */
    final public function isBeforeClass(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true Before $this
     */
    final public function isBefore(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true CodeCoverageIgnore $this
     */
    final public function isCodeCoverageIgnore(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true Covers $this
     */
    final public function isCovers(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true CoversClass $this
     */
    final public function isCoversClass(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true CoversDefaultClass $this
     */
    final public function isCoversDefaultClass(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true CoversFunction $this
     */
    final public function isCoversFunction(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true CoversNothing $this
     */
    final public function isCoversNothing(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true DataProvider $this
     */
    final public function isDataProvider(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true DependsOnClass $this
     */
    final public function isDependsOnClass(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true DependsOnMethod $this
     */
    final public function isDependsOnMethod(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true DoesNotPerformAssertions $this
     */
    final public function isDoesNotPerformAssertions(): bool
    {
        return false;
    }

    final public function isExcludeGlobalVariableFromBackup(): bool
    {
        return false;
    }

    final public function isExcludeStaticPropertyFromBackup(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true Group $this
     */
    final public function isGroup(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true RunClassInSeparateProcess $this
     */
    final public function isRunClassInSeparateProcess(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true RunInSeparateProcess $this
     */
    final public function isRunInSeparateProcess(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true RunTestsInSeparateProcesses $this
     */
    final public function isRunTestsInSeparateProcesses(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true Test $this
     */
    final public function isTest(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true PreCondition $this
     */
    final public function isPreCondition(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true PostCondition $this
     */
    final public function isPostCondition(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true PreserveGlobalState $this
     */
    final public function isPreserveGlobalState(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true RequiresMethod $this
     */
    final public function isRequiresMethod(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true RequiresFunction $this
     */
    final public function isRequiresFunction(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true RequiresOperatingSystem $this
     */
    final public function isRequiresOperatingSystem(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true RequiresOperatingSystemFamily $this
     */
    final public function isRequiresOperatingSystemFamily(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true RequiresPhp $this
     */
    final public function isRequiresPhp(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true RequiresPhpExtension $this
     */
    final public function isRequiresPhpExtension(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true RequiresPhpunit $this
     */
    final public function isRequiresPhpunit(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true RequiresSetting $this
     */
    final public function isRequiresSetting(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true TestDox $this
     */
    final public function isTestDox(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true TestWith $this
     */
    final public function isTestWith(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true Uses $this
     */
    final public function isUses(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true UsesClass $this
     */
    final public function isUsesClass(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true UsesDefaultClass $this
     */
    final public function isUsesDefaultClass(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true UsesFunction $this
     */
    final public function isUsesFunction(): bool
    {
        return false;
    }
}
