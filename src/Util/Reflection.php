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
use ReflectionException;
use ReflectionMethod;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Reflection
{
    /**
     * @psalm-param class-string $className
     * @psalm-param non-empty-string $methodName
     *
     * @psalm-return array{file: string, line: int}
     */
    public static function sourceLocationFor(string $className, string $methodName): array
    {
        try {
            $reflector = new ReflectionMethod($className, $methodName);

            $file = $reflector->getFileName();
            $line = $reflector->getStartLine();
        } catch (ReflectionException) {
            $file = 'unknown';
            $line = 0;
        }

        return [
            'file' => $file,
            'line' => $line,
        ];
    }

    /**
     * @psalm-return list<ReflectionMethod>
     */
    public static function publicMethodsInTestClass(ReflectionClass $class): array
    {
        return self::filterMethods($class, ReflectionMethod::IS_PUBLIC);
    }

    /**
     * @psalm-return list<ReflectionMethod>
     */
    public static function methodsInTestClass(ReflectionClass $class): array
    {
        return self::filterMethods($class, null);
    }

    /**
     * @psalm-return list<ReflectionMethod>
     */
    private static function filterMethods(ReflectionClass $class, ?int $filter): array
    {
        $methods = [];

        foreach ($class->getMethods($filter) as $method) {
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
