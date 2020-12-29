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

use function strpos;
use PHPUnit\Framework\Attributes\After as AfterAttribute;
use PHPUnit\Framework\Attributes\AfterClass as AfterClassAttribute;
use PHPUnit\Framework\Attributes\BackupGlobals as BackupGlobalsAttribute;
use PHPUnit\Framework\Attributes\BackupStaticProperties as BackupStaticPropertiesAttribute;
use PHPUnit\Framework\Attributes\Before as BeforeAttribute;
use PHPUnit\Framework\Attributes\BeforeClass as BeforeClassAttribute;
use PHPUnit\Framework\Attributes\CodeCoverageIgnore as CodeCoverageIgnoreAttribute;
use PHPUnit\Framework\Attributes\CoversClass as CoversClassAttribute;
use PHPUnit\Framework\Attributes\CoversFunction as CoversFunctionAttribute;
use PHPUnit\Framework\Attributes\CoversNothing as CoversNothingAttribute;
use PHPUnit\Framework\Attributes\DataProvider as DataProviderAttribute;
use PHPUnit\Framework\Attributes\DataProviderExternal as DataProviderExternalAttribute;
use PHPUnit\Framework\Attributes\Depends as DependsAttribute;
use PHPUnit\Framework\Attributes\DependsExternal as DependsExternalAttribute;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions as DoesNotPerformAssertionsAttribute;
use PHPUnit\Framework\Attributes\Group as GroupAttribute;
use PHPUnit\Framework\Attributes\Large as LargeAttribute;
use PHPUnit\Framework\Attributes\Medium as MediumAttribute;
use PHPUnit\Framework\Attributes\PostCondition as PostConditionAttribute;
use PHPUnit\Framework\Attributes\PreCondition as PreConditionAttribute;
use PHPUnit\Framework\Attributes\PreserveGlobalState as PreserveGlobalStateAttribute;
use PHPUnit\Framework\Attributes\RequiresFunction as RequiresFunctionAttribute;
use PHPUnit\Framework\Attributes\RequiresOperatingSystem as RequiresOperatingSystemAttribute;
use PHPUnit\Framework\Attributes\RequiresOperatingSystemFamily as RequiresOperatingSystemFamilyAttribute;
use PHPUnit\Framework\Attributes\RequiresPhp as RequiresPhpAttribute;
use PHPUnit\Framework\Attributes\Small as SmallAttribute;
use PHPUnit\Framework\Attributes\Test as TestAttribute;
use PHPUnit\Framework\Attributes\Ticket as TicketAttribute;
use PHPUnit\Framework\Attributes\UsesClass as UsesClassAttribute;
use PHPUnit\Framework\Attributes\UsesFunction as UsesFunctionAttribute;
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
            if (strpos($attribute->getName(), 'PHPUnit\\Framework\\Attributes\\') !== 0) {
                continue;
            }

            $attributeInstance = $attribute->newInstance();

            switch ($attribute->getName()) {
                case BackupGlobalsAttribute::class:
                    $result[] = new BackupGlobals($attributeInstance->enabled());

                    break;

                case BackupStaticPropertiesAttribute::class:
                    $result[] = new BackupStaticProperties($attributeInstance->enabled());

                    break;

                case CodeCoverageIgnoreAttribute::class:
                    $result[] = new CodeCoverageIgnore;

                    break;

                case CoversClassAttribute::class:
                    $result[] = new CoversClass($attributeInstance->className());

                    break;

                case CoversFunctionAttribute::class:
                    $result[] = new CoversFunction($attributeInstance->functionName());

                    break;

                case CoversNothingAttribute::class:
                    $result[] = new CoversNothing;

                    break;

                case DoesNotPerformAssertionsAttribute::class:
                    $result[] = new DoesNotPerformAssertions;

                    break;

                case GroupAttribute::class:
                    $result[] = new Group($attributeInstance->name());

                    break;

                case LargeAttribute::class:
                    $result[] = new Group('large');

                    break;

                case MediumAttribute::class:
                    $result[] = new Group('medium');

                    break;

                case PreserveGlobalStateAttribute::class:
                    $result[] = new PreserveGlobalState($attributeInstance->enabled());

                    break;

                case RequiresFunctionAttribute::class:
                    $result[] = new RequiresFunction($attributeInstance->functionName());

                    break;

                case RequiresOperatingSystemAttribute::class:
                    $result[] = new RequiresOperatingSystem($attributeInstance->regularExpression());

                    break;

                case RequiresOperatingSystemFamilyAttribute::class:
                    $result[] = new RequiresOperatingSystemFamily($attributeInstance->operatingSystemFamily());

                    break;

                case RequiresPhpAttribute::class:
                    $result[] = new RequiresPhp($attributeInstance->version(), $attributeInstance->operator());

                    break;

                case SmallAttribute::class:
                    $result[] = new Group('small');

                    break;

                case TicketAttribute::class:
                    $result[] = new Group($attributeInstance->text());

                    break;

                case UsesClassAttribute::class:
                    $result[] = new UsesClass($attributeInstance->className());

                    break;

                case UsesFunctionAttribute::class:
                    $result[] = new UsesFunction($attributeInstance->functionName());

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
            if (strpos($attribute->getName(), 'PHPUnit\\Framework\\Attributes\\') !== 0) {
                continue;
            }

            $attributeInstance = $attribute->newInstance();

            switch ($attribute->getName()) {
                case AfterAttribute::class:
                    $result[] = new After;

                    break;

                case AfterClassAttribute::class:
                    $result[] = new AfterClass;

                    break;

                case BackupGlobalsAttribute::class:
                    $result[] = new BackupGlobals($attributeInstance->enabled());

                    break;

                case BackupStaticPropertiesAttribute::class:
                    $result[] = new BackupStaticProperties($attributeInstance->enabled());

                    break;

                case BeforeAttribute::class:
                    $result[] = new Before;

                    break;

                case BeforeClassAttribute::class:
                    $result[] = new BeforeClass;

                    break;

                case CodeCoverageIgnoreAttribute::class:
                    $result[] = new CodeCoverageIgnore;

                    break;

                case CoversNothingAttribute::class:
                    $result[] = new CoversNothing;

                    break;

                case DataProviderAttribute::class:
                    $result[] = new DataProvider($className, $attributeInstance->methodName());

                    break;

                case DataProviderExternalAttribute::class:
                    $result[] = new DataProvider($attributeInstance->className(), $attributeInstance->methodName());

                    break;

                case DependsAttribute::class:
                    $result[] = new Depends($className, $attributeInstance->methodName());

                    break;

                case DependsExternalAttribute::class:
                    $result[] = new Depends($attributeInstance->className(), $attributeInstance->methodName());

                    break;

                case DoesNotPerformAssertionsAttribute::class:
                    $result[] = new DoesNotPerformAssertions;

                    break;

                case GroupAttribute::class:
                    $result[] = new Group($attributeInstance->name());

                    break;

                case PostConditionAttribute::class:
                    $result[] = new PostCondition;

                    break;

                case PreConditionAttribute::class:
                    $result[] = new PreCondition;

                    break;

                case PreserveGlobalStateAttribute::class:
                    $result[] = new PreserveGlobalState($attributeInstance->enabled());

                    break;

                case RequiresFunctionAttribute::class:
                    $result[] = new RequiresFunction($attributeInstance->functionName());

                    break;

                case RequiresOperatingSystemAttribute::class:
                    $result[] = new RequiresOperatingSystem($attributeInstance->regularExpression());

                    break;

                case RequiresOperatingSystemFamilyAttribute::class:
                    $result[] = new RequiresOperatingSystemFamily($attributeInstance->operatingSystemFamily());

                    break;

                case RequiresPhpAttribute::class:
                    $result[] = new RequiresPhp($attributeInstance->version(), $attributeInstance->operator());

                    break;

                case TestAttribute::class:
                    $result[] = new Test;

                    break;

                case TicketAttribute::class:
                    $result[] = new Group($attributeInstance->text());

                    break;
            }
        }

        return MetadataCollection::fromArray($result);
    }
}
