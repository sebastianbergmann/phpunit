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
use PHPUnit\Framework\ExecutionOrderDependency;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class DependenciesFacade
{
    /**
     * @psalm-param class-string $className
     *
     * @psalm-return list<ExecutionOrderDependency>
     */
    public static function dependencies(string $className, string $methodName): array
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
}
