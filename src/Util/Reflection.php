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

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Reflection
{
    /**
     * @psalm-return list<ReflectionMethod>
     */
    public function publicMethodsInTestClass(ReflectionClass $class): array
    {
        return $this->filterMethods($class, ReflectionMethod::IS_PUBLIC);
    }

    /**
     * @psalm-return list<ReflectionMethod>
     */
    public function methodsInTestClass(ReflectionClass $class): array
    {
        return $this->filterMethods($class, null);
    }

    /**
     * @psalm-return list<ReflectionMethod>
     */
    private function filterMethods(ReflectionClass $class, ?int $filter): array
    {
        $methods = [];

        // PHP <7.3.5 throw error when null is passed
        // to ReflectionClass::getMethods() when strict_types is enabled.
        $classMethods = $filter === null ? $class->getMethods() : $class->getMethods($filter);

        foreach ($classMethods as $method) {
            if ($method->getDeclaringClass()->getName() === TestCase::class) {
                continue;
            }

            if ($method->getDeclaringClass()->getName() === Assert::class) {
                continue;
            }

            $methods[] = $method;
        }

        return $methods;
    }
}
