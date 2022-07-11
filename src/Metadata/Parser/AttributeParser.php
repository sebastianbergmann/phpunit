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

use const JSON_THROW_ON_ERROR;
use function json_decode;
use function str_starts_with;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\AfterClass;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\BackupStaticProperties;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\BeforeClass;
use PHPUnit\Framework\Attributes\CodeCoverageIgnore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsExternal;
use PHPUnit\Framework\Attributes\DependsExternalUsingDeepClone;
use PHPUnit\Framework\Attributes\DependsExternalUsingShallowClone;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\DependsOnClassUsingDeepClone;
use PHPUnit\Framework\Attributes\DependsOnClassUsingShallowClone;
use PHPUnit\Framework\Attributes\DependsUsingDeepClone;
use PHPUnit\Framework\Attributes\DependsUsingShallowClone;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\ExcludeGlobalVariableFromBackup;
use PHPUnit\Framework\Attributes\ExcludeStaticPropertyFromBackup;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\PostCondition;
use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RequiresFunction;
use PHPUnit\Framework\Attributes\RequiresMethod;
use PHPUnit\Framework\Attributes\RequiresOperatingSystem;
use PHPUnit\Framework\Attributes\RequiresOperatingSystemFamily;
use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\RequiresPhpunit;
use PHPUnit\Framework\Attributes\RequiresSetting;
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
use PHPUnit\Metadata\Metadata;
use PHPUnit\Metadata\MetadataCollection;
use PHPUnit\Metadata\Version\ConstraintRequirement;
use ReflectionClass;
use ReflectionMethod;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class AttributeParser implements Parser
{
    /**
     * @psalm-param class-string $className
     */
    public function forClass(string $className): MetadataCollection
    {
        $result = [];

        foreach ((new ReflectionClass($className))->getAttributes() as $attribute) {
            if (!str_starts_with($attribute->getName(), 'PHPUnit\\Framework\\Attributes\\')) {
                continue;
            }

            $attributeInstance = $attribute->newInstance();

            switch ($attribute->getName()) {
                case BackupGlobals::class:
                    $result[] = Metadata::backupGlobalsOnClass($attributeInstance->enabled());

                    break;

                case BackupStaticProperties::class:
                    $result[] = Metadata::backupStaticPropertiesOnClass($attributeInstance->enabled());

                    break;

                case CodeCoverageIgnore::class:
                    $result[] = Metadata::codeCoverageIgnoreOnClass();

                    break;

                case CoversClass::class:
                    $result[] = Metadata::coversClass($attributeInstance->className());

                    break;

                case CoversFunction::class:
                    $result[] = Metadata::coversFunction($attributeInstance->functionName());

                    break;

                case CoversNothing::class:
                    $result[] = Metadata::coversNothingOnClass();

                    break;

                case DoesNotPerformAssertions::class:
                    $result[] = Metadata::doesNotPerformAssertionsOnClass();

                    break;

                case ExcludeGlobalVariableFromBackup::class:
                    $result[] = Metadata::excludeGlobalVariableFromBackupOnClass($attributeInstance->globalVariableName());

                    break;

                case ExcludeStaticPropertyFromBackup::class:
                    $result[] = Metadata::excludeStaticPropertyFromBackupOnClass(
                        $attributeInstance->className(),
                        $attributeInstance->propertyName()
                    );

                    break;

                case Group::class:
                    $result[] = Metadata::groupOnClass($attributeInstance->name());

                    break;

                case Large::class:
                    $result[] = Metadata::groupOnClass('large');

                    break;

                case Medium::class:
                    $result[] = Metadata::groupOnClass('medium');

                    break;

                case PreserveGlobalState::class:
                    $result[] = Metadata::preserveGlobalStateOnClass($attributeInstance->enabled());

                    break;

                case RequiresMethod::class:
                    $result[] = Metadata::requiresMethodOnClass(
                        $attributeInstance->className(),
                        $attributeInstance->methodName()
                    );

                    break;

                case RequiresFunction::class:
                    $result[] = Metadata::requiresFunctionOnClass($attributeInstance->functionName());

                    break;

                case RequiresOperatingSystem::class:
                    $result[] = Metadata::requiresOperatingSystemOnClass($attributeInstance->regularExpression());

                    break;

                case RequiresOperatingSystemFamily::class:
                    $result[] = Metadata::requiresOperatingSystemFamilyOnClass($attributeInstance->operatingSystemFamily());

                    break;

                case RequiresPhp::class:
                    $result[] = Metadata::requiresPhpOnClass(
                        ConstraintRequirement::from(
                            $attributeInstance->versionRequirement()
                        )
                    );

                    break;

                case RequiresPhpExtension::class:
                    $versionConstraint = null;

                    if ($attributeInstance->hasVersionRequirement()) {
                        $versionConstraint = ConstraintRequirement::from(
                            $attributeInstance->versionRequirement()
                        );
                    }

                    $result[] = Metadata::requiresPhpExtensionOnClass(
                        $attributeInstance->extension(),
                        $versionConstraint
                    );

                    break;

                case RequiresPhpunit::class:
                    $result[] = Metadata::requiresPhpunitOnClass(
                        ConstraintRequirement::from(
                            $attributeInstance->versionRequirement()
                        )
                    );

                    break;

                case RequiresSetting::class:
                    $result[] = Metadata::requiresSettingOnClass(
                        $attributeInstance->setting(),
                        $attributeInstance->value()
                    );

                    break;

                case RunClassInSeparateProcess::class:
                    $result[] = Metadata::runClassInSeparateProcess();

                    break;

                case RunTestsInSeparateProcesses::class:
                    $result[] = Metadata::runTestsInSeparateProcesses();

                    break;

                case Small::class:
                    $result[] = Metadata::groupOnClass('small');

                    break;

                case TestDox::class:
                    $result[] = Metadata::testDoxOnClass($attributeInstance->text());

                    break;

                case Ticket::class:
                    $result[] = Metadata::groupOnClass($attributeInstance->text());

                    break;

                case UsesClass::class:
                    $result[] = Metadata::usesClass($attributeInstance->className());

                    break;

                case UsesFunction::class:
                    $result[] = Metadata::usesFunction($attributeInstance->functionName());

                    break;
            }
        }

        return MetadataCollection::fromArray($result);
    }

    /**
     * @psalm-param class-string $className
     */
    public function forMethod(string $className, string $methodName): MetadataCollection
    {
        $result = [];

        foreach ((new ReflectionMethod($className, $methodName))->getAttributes() as $attribute) {
            if (!str_starts_with($attribute->getName(), 'PHPUnit\\Framework\\Attributes\\')) {
                continue;
            }

            $attributeInstance = $attribute->newInstance();

            switch ($attribute->getName()) {
                case After::class:
                    $result[] = Metadata::after();

                    break;

                case AfterClass::class:
                    $result[] = Metadata::afterClass();

                    break;

                case BackupGlobals::class:
                    $result[] = Metadata::backupGlobalsOnMethod($attributeInstance->enabled());

                    break;

                case BackupStaticProperties::class:
                    $result[] = Metadata::backupStaticPropertiesOnMethod($attributeInstance->enabled());

                    break;

                case Before::class:
                    $result[] = Metadata::before();

                    break;

                case BeforeClass::class:
                    $result[] = Metadata::beforeClass();

                    break;

                case CodeCoverageIgnore::class:
                    $result[] = Metadata::codeCoverageIgnoreOnMethod();

                    break;

                case CoversNothing::class:
                    $result[] = Metadata::coversNothingOnMethod();

                    break;

                case DataProvider::class:
                    $result[] = Metadata::dataProvider($className, $attributeInstance->methodName());

                    break;

                case DataProviderExternal::class:
                    $result[] = Metadata::dataProvider($attributeInstance->className(), $attributeInstance->methodName());

                    break;

                case Depends::class:
                    $result[] = Metadata::dependsOnMethod($className, $attributeInstance->methodName(), false, false);

                    break;

                case DependsUsingDeepClone::class:
                    $result[] = Metadata::dependsOnMethod($className, $attributeInstance->methodName(), true, false);

                    break;

                case DependsUsingShallowClone::class:
                    $result[] = Metadata::dependsOnMethod($className, $attributeInstance->methodName(), false, true);

                    break;

                case DependsExternal::class:
                    $result[] = Metadata::dependsOnMethod($attributeInstance->className(), $attributeInstance->methodName(), false, false);

                    break;

                case DependsExternalUsingDeepClone::class:
                    $result[] = Metadata::dependsOnMethod($attributeInstance->className(), $attributeInstance->methodName(), true, false);

                    break;

                case DependsExternalUsingShallowClone::class:
                    $result[] = Metadata::dependsOnMethod($attributeInstance->className(), $attributeInstance->methodName(), false, true);

                    break;

                case DependsOnClass::class:
                    $result[] = Metadata::dependsOnClass($attributeInstance->className(), false, false);

                    break;

                case DependsOnClassUsingDeepClone::class:
                    $result[] = Metadata::dependsOnClass($attributeInstance->className(), true, false);

                    break;

                case DependsOnClassUsingShallowClone::class:
                    $result[] = Metadata::dependsOnClass($attributeInstance->className(), false, true);

                    break;

                case DoesNotPerformAssertions::class:
                    $result[] = Metadata::doesNotPerformAssertionsOnMethod();

                    break;

                case ExcludeGlobalVariableFromBackup::class:
                    $result[] = Metadata::excludeGlobalVariableFromBackupOnMethod($attributeInstance->globalVariableName());

                    break;

                case ExcludeStaticPropertyFromBackup::class:
                    $result[] = Metadata::excludeStaticPropertyFromBackupOnMethod(
                        $attributeInstance->className(),
                        $attributeInstance->propertyName()
                    );

                    break;

                case Group::class:
                    $result[] = Metadata::groupOnMethod($attributeInstance->name());

                    break;

                case PostCondition::class:
                    $result[] = Metadata::postCondition();

                    break;

                case PreCondition::class:
                    $result[] = Metadata::preCondition();

                    break;

                case PreserveGlobalState::class:
                    $result[] = Metadata::preserveGlobalStateOnMethod($attributeInstance->enabled());

                    break;

                case RequiresMethod::class:
                    $result[] = Metadata::requiresMethodOnMethod(
                        $attributeInstance->className(),
                        $attributeInstance->methodName()
                    );

                    break;

                case RequiresFunction::class:
                    $result[] = Metadata::requiresFunctionOnMethod($attributeInstance->functionName());

                    break;

                case RequiresOperatingSystem::class:
                    $result[] = Metadata::requiresOperatingSystemOnMethod($attributeInstance->regularExpression());

                    break;

                case RequiresOperatingSystemFamily::class:
                    $result[] = Metadata::requiresOperatingSystemFamilyOnMethod($attributeInstance->operatingSystemFamily());

                    break;

                case RequiresPhp::class:
                    $result[] = Metadata::requiresPhpOnMethod(
                        ConstraintRequirement::from(
                            $attributeInstance->versionRequirement()
                        )
                    );

                    break;

                case RequiresPhpExtension::class:
                    $versionConstraint = null;

                    if ($attributeInstance->hasVersionRequirement()) {
                        $versionConstraint = ConstraintRequirement::from(
                            $attributeInstance->versionRequirement()
                        );
                    }

                    $result[] = Metadata::requiresPhpExtensionOnMethod(
                        $attributeInstance->extension(),
                        $versionConstraint
                    );

                    break;

                case RequiresPhpunit::class:
                    $result[] = Metadata::requiresPhpunitOnMethod(
                        ConstraintRequirement::from(
                            $attributeInstance->versionRequirement()
                        )
                    );

                    break;

                case RequiresSetting::class:
                    $result[] = Metadata::requiresSettingOnMethod(
                        $attributeInstance->setting(),
                        $attributeInstance->value()
                    );

                    break;

                case RunInSeparateProcess::class:
                    $result[] = Metadata::runInSeparateProcess();

                    break;

                case Test::class:
                    $result[] = Metadata::test();

                    break;

                case TestDox::class:
                    $result[] = Metadata::testDoxOnMethod($attributeInstance->text());

                    break;

                case TestWith::class:
                    $result[] = Metadata::testWith($attributeInstance->data());

                    break;

                case TestWithJson::class:
                    $result[] = Metadata::testWith(json_decode($attributeInstance->json(), true, 512, JSON_THROW_ON_ERROR));

                    break;

                case Ticket::class:
                    $result[] = Metadata::groupOnMethod($attributeInstance->text());

                    break;
            }
        }

        return MetadataCollection::fromArray($result);
    }

    /**
     * @psalm-param class-string $className
     */
    public function forClassAndMethod(string $className, string $methodName): MetadataCollection
    {
        return $this->forClass($className)->mergeWith(
            $this->forMethod($className, $methodName)
        );
    }
}
