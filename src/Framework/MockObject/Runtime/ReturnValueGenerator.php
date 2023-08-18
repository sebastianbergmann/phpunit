<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject;

use function array_keys;
use function array_map;
use function explode;
use function in_array;
use function interface_exists;
use function sprintf;
use function str_contains;
use function str_ends_with;
use function str_starts_with;
use function substr;
use PHPUnit\Framework\MockObject\Generator\Generator;
use ReflectionClass;
use stdClass;
use Throwable;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ReturnValueGenerator
{
    /**
     * @psalm-param class-string $className
     * @psalm-param non-empty-string $methodName
     * @psalm-param class-string $stubClassName
     *
     * @throws Exception
     */
    public function generate(string $className, string $methodName, string $stubClassName, string $returnType): mixed
    {
        $intersection = false;
        $union        = false;

        if (str_contains($returnType, '|')) {
            $types = explode('|', $returnType);
            $union = true;

            foreach (array_keys($types) as $key) {
                if (str_starts_with($types[$key], '(') && str_ends_with($types[$key], ')')) {
                    $types[$key] = substr($types[$key], 1, -1);
                }
            }
        } elseif (str_contains($returnType, '&')) {
            $types        = explode('&', $returnType);
            $intersection = true;
        } else {
            $types = [$returnType];
        }

        $types = array_map('strtolower', $types);

        if (!$intersection) {
            if (in_array('', $types, true) ||
                in_array('null', $types, true) ||
                in_array('mixed', $types, true) ||
                in_array('void', $types, true)) {
                return null;
            }

            if (in_array('true', $types, true)) {
                return true;
            }

            if (in_array('false', $types, true) ||
                in_array('bool', $types, true)) {
                return false;
            }

            if (in_array('float', $types, true)) {
                return 0.0;
            }

            if (in_array('int', $types, true)) {
                return 0;
            }

            if (in_array('string', $types, true)) {
                return '';
            }

            if (in_array('array', $types, true)) {
                return [];
            }

            if (in_array('static', $types, true)) {
                return $this->newInstanceOf($stubClassName, $className, $methodName);
            }

            if (in_array('object', $types, true)) {
                return new stdClass;
            }

            if (in_array('callable', $types, true) ||
                in_array('closure', $types, true)) {
                return static function (): void
                {
                };
            }

            if (in_array('traversable', $types, true) ||
                in_array('generator', $types, true) ||
                in_array('iterable', $types, true)) {
                $generator = static function (): \Generator
                {
                    yield from [];
                };

                return $generator();
            }

            if (!$union) {
                return $this->testDoubleFor($returnType, $className, $methodName);
            }
        }

        if ($union) {
            foreach ($types as $type) {
                if (str_contains($type, '&')) {
                    $_types = explode('&', $type);

                    if ($this->onlyInterfaces($_types)) {
                        return $this->testDoubleForIntersectionOfInterfaces($_types, $className, $methodName);
                    }
                }
            }
        }

        if ($intersection && $this->onlyInterfaces($types)) {
            return $this->testDoubleForIntersectionOfInterfaces($types, $className, $methodName);
        }

        $reason = '';

        if ($union) {
            $reason = ' because the declared return type is a union';
        } elseif ($intersection) {
            $reason = ' because the declared return type is an intersection';
        }

        throw new RuntimeException(
            sprintf(
                'Return value for %s::%s() cannot be generated%s, please configure a return value for this method',
                $className,
                $methodName,
                $reason,
            ),
        );
    }

    /**
     * @psalm-param non-empty-list<string> $types
     */
    private function onlyInterfaces(array $types): bool
    {
        foreach ($types as $type) {
            if (!interface_exists($type)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @psalm-param class-string $stubClassName
     * @psalm-param class-string $className
     * @psalm-param non-empty-string $methodName
     *
     * @throws RuntimeException
     */
    private function newInstanceOf(string $stubClassName, string $className, string $methodName): MockObject
    {
        try {
            return (new ReflectionClass($stubClassName))->newInstanceWithoutConstructor();
        } catch (Throwable $t) {
            throw new RuntimeException(
                sprintf(
                    'Return value for %s::%s() cannot be generated: %s',
                    $className,
                    $methodName,
                    $t->getMessage(),
                ),
            );
        }
    }

    /**
     * @psalm-param class-string $type
     * @psalm-param class-string $className
     * @psalm-param non-empty-string $methodName
     *
     * @throws RuntimeException
     */
    private function testDoubleFor(string $type, string $className, string $methodName): Stub
    {
        try {
            return (new Generator)->testDouble($type, false, [], [], '', false);
        } catch (Throwable $t) {
            throw new RuntimeException(
                sprintf(
                    'Return value for %s::%s() cannot be generated: %s',
                    $className,
                    $methodName,
                    $t->getMessage(),
                ),
            );
        }
    }

    /**
     * @psalm-param non-empty-list<string> $types
     * @psalm-param class-string $className
     * @psalm-param non-empty-string $methodName
     *
     * @throws RuntimeException
     */
    private function testDoubleForIntersectionOfInterfaces(array $types, string $className, string $methodName): Stub
    {
        try {
            return (new Generator)->testDoubleForInterfaceIntersection($types, false);
        } catch (Throwable $t) {
            throw new RuntimeException(
                sprintf(
                    'Return value for %s::%s() cannot be generated: %s',
                    $className,
                    $methodName,
                    $t->getMessage(),
                ),
            );
        }
    }
}
