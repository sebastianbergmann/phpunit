<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use function assert;
use function str_starts_with;
use PHPUnit\Framework\ExecutionOrderDependency;
use PHPUnit\Metadata\DependsOnClass;
use PHPUnit\Metadata\DependsOnMethod;
use PHPUnit\Metadata\Registry;
use ReflectionMethod;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Test
{
    /**
     * @psalm-param class-string $className
     *
     * @psalm-return list<ExecutionOrderDependency>
     */
    public static function getDependencies(string $className, string $methodName): array
    {
        $dependencies = [];

        foreach (Registry::parser()->forClassAndMethod($className, $methodName)->isDepends() as $metadata) {
            if ($metadata->isDependsOnClass()) {
                assert($metadata instanceof DependsOnClass);

                $dependencies[] = ExecutionOrderDependency::forClass($metadata);
            }

            if ($metadata->isDependsOnMethod()) {
                assert($metadata instanceof DependsOnMethod);

                $dependencies[] = ExecutionOrderDependency::forMethod($metadata);
            }
        }

        return $dependencies;
    }

    public static function isTestMethod(ReflectionMethod $method): bool
    {
        if (!$method->isPublic()) {
            return false;
        }

        if (str_starts_with($method->getName(), 'test')) {
            return true;
        }

        $metadata = Registry::parser()->forMethod(
            $method->getDeclaringClass()->getName(),
            $method->getName()
        );

        return $metadata->isTest()->isNotEmpty();
    }
}
