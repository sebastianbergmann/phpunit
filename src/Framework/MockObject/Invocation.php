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
use function implode;
use function in_array;
use function interface_exists;
use function is_object;
use function sprintf;
use function str_contains;
use function str_starts_with;
use function strtolower;
use function substr;
use PHPUnit\Framework\SelfDescribing;
use PHPUnit\Util\Cloner;
use ReflectionClass;
use SebastianBergmann\Exporter\Exporter;
use stdClass;
use Throwable;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Invocation implements SelfDescribing
{
    private readonly string $className;
    private readonly string $methodName;
    private array $parameters;
    private readonly string $returnType;
    private bool $isReturnTypeNullable = false;
    private readonly bool $proxiedCall;
    private readonly object $object;

    public function __construct(string $className, string $methodName, array $parameters, string $returnType, object $object, bool $cloneObjects = false, bool $proxiedCall = false)
    {
        $this->className   = $className;
        $this->methodName  = $methodName;
        $this->parameters  = $parameters;
        $this->object      = $object;
        $this->proxiedCall = $proxiedCall;

        if (strtolower($methodName) === '__tostring') {
            $returnType = 'string';
        }

        if (str_starts_with($returnType, '?')) {
            $returnType                 = substr($returnType, 1);
            $this->isReturnTypeNullable = true;
        }

        $this->returnType = $returnType;

        if (!$cloneObjects) {
            return;
        }

        foreach ($this->parameters as $key => $value) {
            if (is_object($value)) {
                $this->parameters[$key] = Cloner::clone($value);
            }
        }
    }

    public function className(): string
    {
        return $this->className;
    }

    public function methodName(): string
    {
        return $this->methodName;
    }

    public function parameters(): array
    {
        return $this->parameters;
    }

    /**
     * @throws Exception
     */
    public function generateReturnValue(): mixed
    {
        if ($this->isReturnTypeNullable || $this->proxiedCall) {
            return null;
        }

        $intersection               = false;
        $union                      = false;
        $unionContainsIntersections = false;

        if (str_contains($this->returnType, '|')) {
            $types = explode('|', $this->returnType);
            $union = true;

            if (str_contains($this->returnType, '(')) {
                $unionContainsIntersections = true;
            }
        } elseif (str_contains($this->returnType, '&')) {
            $types        = explode('&', $this->returnType);
            $intersection = true;
        } else {
            $types = [$this->returnType];
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
                    return (new ReflectionClass($this->object::class))->newInstanceWithoutConstructor();
                    // @codeCoverageIgnoreStart
                } catch (\ReflectionException $e) {
                    throw new ReflectionException(
                        $e->getMessage(),
                        $e->getCode(),
                        $e
                    );
                }
                // @codeCoverageIgnoreEnd
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
                    return (new Generator)->getMock($this->returnType, [], [], '', false);
                } catch (Throwable $t) {
                    if ($t instanceof Exception) {
                        throw $t;
                    }

                    throw new RuntimeException(
                        $t->getMessage(),
                        (int) $t->getCode(),
                        $t
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
                        $this->className,
                        $this->methodName,
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
                $this->className,
                $this->methodName,
                $reason
            )
        );
    }

    public function toString(): string
    {
        $exporter = new Exporter;

        return sprintf(
            '%s::%s(%s)%s',
            $this->className,
            $this->methodName,
            implode(
                ', ',
                array_map(
                    [$exporter, 'shortenedExport'],
                    $this->parameters
                )
            ),
            $this->returnType ? sprintf(': %s', $this->returnType) : ''
        );
    }

    public function object(): object
    {
        return $this->object;
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
