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
use PHPUnit\Runner\Extension\Extension;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
abstract readonly class Metadata
{
    private Level $level;

    public static function after(int $priority): After
    {
        return new After(Level::METHOD_LEVEL, $priority);
    }

    public static function afterClass(int $priority): AfterClass
    {
        return new AfterClass(Level::METHOD_LEVEL, $priority);
    }

    public static function allowMockObjectsWithoutExpectationsOnClass(): AllowMockObjectsWithoutExpectations
    {
        return new AllowMockObjectsWithoutExpectations(Level::CLASS_LEVEL);
    }

    public static function allowMockObjectsWithoutExpectationsOnMethod(): AllowMockObjectsWithoutExpectations
    {
        return new AllowMockObjectsWithoutExpectations(Level::METHOD_LEVEL);
    }

    public static function backupGlobalsOnClass(bool $enabled): BackupGlobals
    {
        return new BackupGlobals(Level::CLASS_LEVEL, $enabled);
    }

    public static function backupGlobalsOnMethod(bool $enabled): BackupGlobals
    {
        return new BackupGlobals(Level::METHOD_LEVEL, $enabled);
    }

    public static function backupStaticPropertiesOnClass(bool $enabled): BackupStaticProperties
    {
        return new BackupStaticProperties(Level::CLASS_LEVEL, $enabled);
    }

    public static function backupStaticPropertiesOnMethod(bool $enabled): BackupStaticProperties
    {
        return new BackupStaticProperties(Level::METHOD_LEVEL, $enabled);
    }

    public static function before(int $priority): Before
    {
        return new Before(Level::METHOD_LEVEL, $priority);
    }

    public static function beforeClass(int $priority): BeforeClass
    {
        return new BeforeClass(Level::METHOD_LEVEL, $priority);
    }

    /**
     * @param non-empty-string $namespace
     */
    public static function coversNamespace(string $namespace): CoversNamespace
    {
        return new CoversNamespace(Level::CLASS_LEVEL, $namespace);
    }

    /**
     * @param class-string $className
     */
    public static function coversClass(string $className): CoversClass
    {
        return new CoversClass(Level::CLASS_LEVEL, $className);
    }

    /**
     * @param class-string $className
     */
    public static function coversClassesThatExtendClass(string $className): CoversClassesThatExtendClass
    {
        return new CoversClassesThatExtendClass(Level::CLASS_LEVEL, $className);
    }

    /**
     * @param class-string $interfaceName
     */
    public static function coversClassesThatImplementInterface(string $interfaceName): CoversClassesThatImplementInterface
    {
        return new CoversClassesThatImplementInterface(Level::CLASS_LEVEL, $interfaceName);
    }

    /**
     * @param trait-string $traitName
     */
    public static function coversTrait(string $traitName): CoversTrait
    {
        return new CoversTrait(Level::CLASS_LEVEL, $traitName);
    }

    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     */
    public static function coversMethod(string $className, string $methodName): CoversMethod
    {
        return new CoversMethod(Level::CLASS_LEVEL, $className, $methodName);
    }

    /**
     * @param non-empty-string $functionName
     */
    public static function coversFunction(string $functionName): CoversFunction
    {
        return new CoversFunction(Level::CLASS_LEVEL, $functionName);
    }

    public static function coversNothingOnClass(): CoversNothing
    {
        return new CoversNothing(Level::CLASS_LEVEL);
    }

    public static function coversNothingOnMethod(): CoversNothing
    {
        return new CoversNothing(Level::METHOD_LEVEL);
    }

    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     */
    public static function dataProvider(string $className, string $methodName, bool $validateArgumentCount): DataProvider
    {
        return new DataProvider(Level::METHOD_LEVEL, $className, $methodName, $validateArgumentCount);
    }

    /**
     * @param class-string $className
     */
    public static function dependsOnClass(string $className, bool $deepClone, bool $shallowClone): DependsOnClass
    {
        return new DependsOnClass(Level::METHOD_LEVEL, $className, $deepClone, $shallowClone);
    }

    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     */
    public static function dependsOnMethod(string $className, string $methodName, bool $deepClone, bool $shallowClone): DependsOnMethod
    {
        return new DependsOnMethod(Level::METHOD_LEVEL, $className, $methodName, $deepClone, $shallowClone);
    }

    public static function disableReturnValueGenerationForTestDoubles(): DisableReturnValueGenerationForTestDoubles
    {
        return new DisableReturnValueGenerationForTestDoubles(Level::CLASS_LEVEL);
    }

    public static function doesNotPerformAssertionsOnClass(): DoesNotPerformAssertions
    {
        return new DoesNotPerformAssertions(Level::CLASS_LEVEL);
    }

    public static function doesNotPerformAssertionsOnMethod(): DoesNotPerformAssertions
    {
        return new DoesNotPerformAssertions(Level::METHOD_LEVEL);
    }

    /**
     * @param non-empty-string $globalVariableName
     */
    public static function excludeGlobalVariableFromBackupOnClass(string $globalVariableName): ExcludeGlobalVariableFromBackup
    {
        return new ExcludeGlobalVariableFromBackup(Level::CLASS_LEVEL, $globalVariableName);
    }

    /**
     * @param non-empty-string $globalVariableName
     */
    public static function excludeGlobalVariableFromBackupOnMethod(string $globalVariableName): ExcludeGlobalVariableFromBackup
    {
        return new ExcludeGlobalVariableFromBackup(Level::METHOD_LEVEL, $globalVariableName);
    }

    /**
     * @param class-string     $className
     * @param non-empty-string $propertyName
     */
    public static function excludeStaticPropertyFromBackupOnClass(string $className, string $propertyName): ExcludeStaticPropertyFromBackup
    {
        return new ExcludeStaticPropertyFromBackup(Level::CLASS_LEVEL, $className, $propertyName);
    }

    /**
     * @param class-string     $className
     * @param non-empty-string $propertyName
     */
    public static function excludeStaticPropertyFromBackupOnMethod(string $className, string $propertyName): ExcludeStaticPropertyFromBackup
    {
        return new ExcludeStaticPropertyFromBackup(Level::METHOD_LEVEL, $className, $propertyName);
    }

    /**
     * @param non-empty-string $groupName
     */
    public static function groupOnClass(string $groupName): Group
    {
        return new Group(Level::CLASS_LEVEL, $groupName);
    }

    /**
     * @param non-empty-string $groupName
     */
    public static function groupOnMethod(string $groupName): Group
    {
        return new Group(Level::METHOD_LEVEL, $groupName);
    }

    /**
     * @param null|non-empty-string $messagePattern
     */
    public static function ignoreDeprecationsOnClass(?string $messagePattern = null): IgnoreDeprecations
    {
        return new IgnoreDeprecations(Level::CLASS_LEVEL, $messagePattern);
    }

    /**
     * @param null|non-empty-string $messagePattern
     */
    public static function ignoreDeprecationsOnMethod(?string $messagePattern = null): IgnoreDeprecations
    {
        return new IgnoreDeprecations(Level::METHOD_LEVEL, $messagePattern);
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public static function ignorePhpunitDeprecationsOnClass(): IgnorePhpunitDeprecations
    {
        return new IgnorePhpunitDeprecations(Level::CLASS_LEVEL);
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public static function ignorePhpunitDeprecationsOnMethod(): IgnorePhpunitDeprecations
    {
        return new IgnorePhpunitDeprecations(Level::METHOD_LEVEL);
    }

    public static function postCondition(int $priority): PostCondition
    {
        return new PostCondition(Level::METHOD_LEVEL, $priority);
    }

    public static function preCondition(int $priority): PreCondition
    {
        return new PreCondition(Level::METHOD_LEVEL, $priority);
    }

    public static function preserveGlobalStateOnClass(bool $enabled): PreserveGlobalState
    {
        return new PreserveGlobalState(Level::CLASS_LEVEL, $enabled);
    }

    public static function preserveGlobalStateOnMethod(bool $enabled): PreserveGlobalState
    {
        return new PreserveGlobalState(Level::METHOD_LEVEL, $enabled);
    }

    /**
     * @param non-empty-string $functionName
     */
    public static function requiresFunctionOnClass(string $functionName): RequiresFunction
    {
        return new RequiresFunction(Level::CLASS_LEVEL, $functionName);
    }

    /**
     * @param non-empty-string $functionName
     */
    public static function requiresFunctionOnMethod(string $functionName): RequiresFunction
    {
        return new RequiresFunction(Level::METHOD_LEVEL, $functionName);
    }

    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     */
    public static function requiresMethodOnClass(string $className, string $methodName): RequiresMethod
    {
        return new RequiresMethod(Level::CLASS_LEVEL, $className, $methodName);
    }

    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     */
    public static function requiresMethodOnMethod(string $className, string $methodName): RequiresMethod
    {
        return new RequiresMethod(Level::METHOD_LEVEL, $className, $methodName);
    }

    /**
     * @param non-empty-string $operatingSystem
     */
    public static function requiresOperatingSystemOnClass(string $operatingSystem): RequiresOperatingSystem
    {
        return new RequiresOperatingSystem(Level::CLASS_LEVEL, $operatingSystem);
    }

    /**
     * @param non-empty-string $operatingSystem
     */
    public static function requiresOperatingSystemOnMethod(string $operatingSystem): RequiresOperatingSystem
    {
        return new RequiresOperatingSystem(Level::METHOD_LEVEL, $operatingSystem);
    }

    /**
     * @param non-empty-string $operatingSystemFamily
     */
    public static function requiresOperatingSystemFamilyOnClass(string $operatingSystemFamily): RequiresOperatingSystemFamily
    {
        return new RequiresOperatingSystemFamily(Level::CLASS_LEVEL, $operatingSystemFamily);
    }

    /**
     * @param non-empty-string $operatingSystemFamily
     */
    public static function requiresOperatingSystemFamilyOnMethod(string $operatingSystemFamily): RequiresOperatingSystemFamily
    {
        return new RequiresOperatingSystemFamily(Level::METHOD_LEVEL, $operatingSystemFamily);
    }

    public static function requiresPhpOnClass(Requirement $versionRequirement): RequiresPhp
    {
        return new RequiresPhp(Level::CLASS_LEVEL, $versionRequirement);
    }

    public static function requiresPhpOnMethod(Requirement $versionRequirement): RequiresPhp
    {
        return new RequiresPhp(Level::METHOD_LEVEL, $versionRequirement);
    }

    /**
     * @param non-empty-string $extension
     */
    public static function requiresPhpExtensionOnClass(string $extension, ?Requirement $versionRequirement): RequiresPhpExtension
    {
        return new RequiresPhpExtension(Level::CLASS_LEVEL, $extension, $versionRequirement);
    }

    /**
     * @param non-empty-string $extension
     */
    public static function requiresPhpExtensionOnMethod(string $extension, ?Requirement $versionRequirement): RequiresPhpExtension
    {
        return new RequiresPhpExtension(Level::METHOD_LEVEL, $extension, $versionRequirement);
    }

    public static function requiresPhpunitOnClass(Requirement $versionRequirement): RequiresPhpunit
    {
        return new RequiresPhpunit(Level::CLASS_LEVEL, $versionRequirement);
    }

    public static function requiresPhpunitOnMethod(Requirement $versionRequirement): RequiresPhpunit
    {
        return new RequiresPhpunit(Level::METHOD_LEVEL, $versionRequirement);
    }

    /**
     * @param class-string<Extension> $extensionClass
     */
    public static function requiresPhpunitExtensionOnClass(string $extensionClass): RequiresPhpunitExtension
    {
        return new RequiresPhpunitExtension(Level::CLASS_LEVEL, $extensionClass);
    }

    /**
     * @param class-string<Extension> $extensionClass
     */
    public static function requiresPhpunitExtensionOnMethod(string $extensionClass): RequiresPhpunitExtension
    {
        return new RequiresPhpunitExtension(Level::METHOD_LEVEL, $extensionClass);
    }

    public static function requiresEnvironmentVariableOnClass(string $environmentVariableName, null|string $value): RequiresEnvironmentVariable
    {
        return new RequiresEnvironmentVariable(Level::CLASS_LEVEL, $environmentVariableName, $value);
    }

    public static function requiresEnvironmentVariableOnMethod(string $environmentVariableName, null|string $value): RequiresEnvironmentVariable
    {
        return new RequiresEnvironmentVariable(Level::METHOD_LEVEL, $environmentVariableName, $value);
    }

    public static function withEnvironmentVariableOnClass(string $environmentVariableName, null|string $value): WithEnvironmentVariable
    {
        return new WithEnvironmentVariable(Level::CLASS_LEVEL, $environmentVariableName, $value);
    }

    public static function withEnvironmentVariableOnMethod(string $environmentVariableName, null|string $value): WithEnvironmentVariable
    {
        return new WithEnvironmentVariable(Level::METHOD_LEVEL, $environmentVariableName, $value);
    }

    /**
     * @param non-empty-string $setting
     * @param non-empty-string $value
     */
    public static function requiresSettingOnClass(string $setting, string $value): RequiresSetting
    {
        return new RequiresSetting(Level::CLASS_LEVEL, $setting, $value);
    }

    /**
     * @param non-empty-string $setting
     * @param non-empty-string $value
     */
    public static function requiresSettingOnMethod(string $setting, string $value): RequiresSetting
    {
        return new RequiresSetting(Level::METHOD_LEVEL, $setting, $value);
    }

    public static function runTestsInSeparateProcesses(): RunTestsInSeparateProcesses
    {
        return new RunTestsInSeparateProcesses(Level::CLASS_LEVEL);
    }

    public static function runInSeparateProcess(): RunInSeparateProcess
    {
        return new RunInSeparateProcess(Level::METHOD_LEVEL);
    }

    public static function test(): Test
    {
        return new Test(Level::METHOD_LEVEL);
    }

    /**
     * @param non-empty-string $text
     */
    public static function testDoxOnClass(string $text): TestDox
    {
        return new TestDox(Level::CLASS_LEVEL, $text);
    }

    /**
     * @param non-empty-string $text
     */
    public static function testDoxOnMethod(string $text): TestDox
    {
        return new TestDox(Level::METHOD_LEVEL, $text);
    }

    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     */
    public static function testDoxFormatter(string $className, string $methodName): TestDoxFormatter
    {
        return new TestDoxFormatter(Level::METHOD_LEVEL, $className, $methodName);
    }

    /**
     * @param ?non-empty-string $name
     */
    public static function testWith(mixed $data, ?string $name = null): TestWith
    {
        return new TestWith(Level::METHOD_LEVEL, $data, $name);
    }

    /**
     * @param non-empty-string $namespace
     */
    public static function usesNamespace(string $namespace): UsesNamespace
    {
        return new UsesNamespace(Level::CLASS_LEVEL, $namespace);
    }

    /**
     * @param class-string $className
     */
    public static function usesClass(string $className): UsesClass
    {
        return new UsesClass(Level::CLASS_LEVEL, $className);
    }

    /**
     * @param class-string $className
     */
    public static function usesClassesThatExtendClass(string $className): UsesClassesThatExtendClass
    {
        return new UsesClassesThatExtendClass(Level::CLASS_LEVEL, $className);
    }

    /**
     * @param class-string $interfaceName
     */
    public static function usesClassesThatImplementInterface(string $interfaceName): UsesClassesThatImplementInterface
    {
        return new UsesClassesThatImplementInterface(Level::CLASS_LEVEL, $interfaceName);
    }

    /**
     * @param trait-string $traitName
     */
    public static function usesTrait(string $traitName): UsesTrait
    {
        return new UsesTrait(Level::CLASS_LEVEL, $traitName);
    }

    /**
     * @param non-empty-string $functionName
     */
    public static function usesFunction(string $functionName): UsesFunction
    {
        return new UsesFunction(Level::CLASS_LEVEL, $functionName);
    }

    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     */
    public static function usesMethod(string $className, string $methodName): UsesMethod
    {
        return new UsesMethod(Level::CLASS_LEVEL, $className, $methodName);
    }

    public static function withoutErrorHandler(): WithoutErrorHandler
    {
        return new WithoutErrorHandler(Level::METHOD_LEVEL);
    }

    /**
     * @param null|non-empty-string $messagePattern
     */
    public static function ignorePhpunitWarnings(?string $messagePattern): IgnorePhpunitWarnings
    {
        return new IgnorePhpunitWarnings(Level::METHOD_LEVEL, $messagePattern);
    }

    protected function __construct(Level $level)
    {
        $this->level = $level;
    }

    public function isClassLevel(): bool
    {
        return $this->level === Level::CLASS_LEVEL;
    }

    public function isMethodLevel(): bool
    {
        return $this->level === Level::METHOD_LEVEL;
    }

    /**
     * @phpstan-assert-if-true After $this
     */
    public function isAfter(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true AfterClass $this
     */
    public function isAfterClass(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true AllowMockObjectsWithoutExpectations $this
     */
    public function isAllowMockObjectsWithoutExpectations(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true BackupGlobals $this
     */
    public function isBackupGlobals(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true BackupStaticProperties $this
     */
    public function isBackupStaticProperties(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true BeforeClass $this
     */
    public function isBeforeClass(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true Before $this
     */
    public function isBefore(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true CoversNamespace $this
     */
    public function isCoversNamespace(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true CoversClass $this
     */
    public function isCoversClass(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true CoversClassesThatExtendClass $this
     */
    public function isCoversClassesThatExtendClass(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true CoversClassesThatImplementInterface $this
     */
    public function isCoversClassesThatImplementInterface(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true CoversTrait $this
     */
    public function isCoversTrait(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true CoversFunction $this
     */
    public function isCoversFunction(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true CoversMethod $this
     */
    public function isCoversMethod(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true CoversNothing $this
     */
    public function isCoversNothing(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true DataProvider $this
     */
    public function isDataProvider(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true DependsOnClass $this
     */
    public function isDependsOnClass(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true DependsOnMethod $this
     */
    public function isDependsOnMethod(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true DisableReturnValueGenerationForTestDoubles $this
     */
    public function isDisableReturnValueGenerationForTestDoubles(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true DoesNotPerformAssertions $this
     */
    public function isDoesNotPerformAssertions(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true ExcludeGlobalVariableFromBackup $this
     */
    public function isExcludeGlobalVariableFromBackup(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true ExcludeStaticPropertyFromBackup $this
     */
    public function isExcludeStaticPropertyFromBackup(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true Group $this
     */
    public function isGroup(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true IgnoreDeprecations $this
     */
    public function isIgnoreDeprecations(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true IgnorePhpunitDeprecations $this
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public function isIgnorePhpunitDeprecations(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true RunInSeparateProcess $this
     */
    public function isRunInSeparateProcess(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true RunTestsInSeparateProcesses $this
     */
    public function isRunTestsInSeparateProcesses(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true Test $this
     */
    public function isTest(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true PreCondition $this
     */
    public function isPreCondition(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true PostCondition $this
     */
    public function isPostCondition(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true PreserveGlobalState $this
     */
    public function isPreserveGlobalState(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true RequiresMethod $this
     */
    public function isRequiresMethod(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true RequiresFunction $this
     */
    public function isRequiresFunction(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true RequiresOperatingSystem $this
     */
    public function isRequiresOperatingSystem(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true RequiresOperatingSystemFamily $this
     */
    public function isRequiresOperatingSystemFamily(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true RequiresPhp $this
     */
    public function isRequiresPhp(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true RequiresPhpExtension $this
     */
    public function isRequiresPhpExtension(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true RequiresPhpunit $this
     */
    public function isRequiresPhpunit(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true RequiresPhpunitExtension $this
     */
    public function isRequiresPhpunitExtension(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true RequiresEnvironmentVariable $this
     */
    public function isRequiresEnvironmentVariable(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true WithEnvironmentVariable $this
     */
    public function isWithEnvironmentVariable(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true RequiresSetting $this
     */
    public function isRequiresSetting(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true TestDox $this
     */
    public function isTestDox(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true TestDoxFormatter $this
     */
    public function isTestDoxFormatter(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true TestWith $this
     */
    public function isTestWith(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true UsesNamespace $this
     */
    public function isUsesNamespace(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true UsesClass $this
     */
    public function isUsesClass(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true UsesClassesThatExtendClass $this
     */
    public function isUsesClassesThatExtendClass(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true UsesClassesThatImplementInterface $this
     */
    public function isUsesClassesThatImplementInterface(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true UsesTrait $this
     */
    public function isUsesTrait(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true UsesFunction $this
     */
    public function isUsesFunction(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true UsesMethod $this
     */
    public function isUsesMethod(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true WithoutErrorHandler $this
     */
    public function isWithoutErrorHandler(): bool
    {
        return false;
    }

    /**
     * @phpstan-assert-if-true IgnorePhpunitWarnings $this
     */
    public function isIgnorePhpunitWarnings(): bool
    {
        return false;
    }
}
