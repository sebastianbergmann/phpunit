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
    public function methodsInTestClass(ReflectionClass $class): array
    {
        $methods = [];

        foreach ($class->getMethods() as $method) {
            /*
             * ReflectionClass::getMethods() returns an array of ReflectionMethod
             * objects in the following order:
             *
             *   - Methods in the class for which ReflectionClass::getMethods() was called
             *   - Methods in the parent class (if any) of the class for which ReflectionClass::getMethods() was called
             *   - ...
             *
             * We can stop processing ReflectionMethod objects when we reach the first
             * object for a method in PHPUnit\Framework\TestCase.
             */
            if ($method->getDeclaringClass()->getName() === TestCase::class) {
                break;
            }

            $methods[] = $method;
        }

        return $methods;
    }
}
