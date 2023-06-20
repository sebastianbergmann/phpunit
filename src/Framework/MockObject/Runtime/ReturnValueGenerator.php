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

use function array_map;
use function explode;
use function in_array;
use function interface_exists;
use function sprintf;
use function str_contains;
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
        $intersection               = false;
        $union                      = false;
        $unionContainsIntersections = false;

        if (str_contains($returnType, '|')) {
            $types = explode('|', $returnType);
            $union = true;

            if (str_contains($returnType, '(')) {
                $unionContainsIntersections = true;
            }
        } elseif (str_contains($returnType, '&')) {
            $types        = explode('&', $returnType);
            $intersection = true;
        } else {
            $types = [$returnType];
        }

        $types = array_map('strtolower', $types);

        if (!$intersection && !$unionContainsIntersections) {
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
                try {
                    return (new ReflectionClass($stubClassName))->newInstanceWithoutConstructor();
                } catch (\ReflectionException $e) {
                    throw new ReflectionException(
                        $e->getMessage(),
                        $e->getCode(),
                        $e,
                    );
                }
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
                try {
                    return (new Generator)->getMock($returnType, [], [], '', false);
                } catch (Throwable $t) {
                    if ($t instanceof Exception) {
                        throw $t;
                    }

                    throw new RuntimeException(
                        $t->getMessage(),
                        (int) $t->getCode(),
                        $t,
                    );
                }
            }
        }

        if ($intersection && $this->onlyInterfaces($types)) {
            try {
                return (new Generator)->getMockForInterfaces($types);
            } catch (Throwable $t) {
                throw new RuntimeException(
                    sprintf(
                        'Return value for %s::%s() cannot be generated: %s',
                        $className,
                        $methodName,
                        $t->getMessage(),
                    ),
                    (int) $t->getCode(),
                );
            }
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
}
