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

use function array_key_exists;
use function array_map;
use function array_slice;
use function array_values;
use function count;
use function explode;
use function get_class;
use function gettype;
use function implode;
use function is_object;
use function is_subclass_of;
use function sprintf;
use function strpos;
use function strtolower;
use function substr;
use PHPUnit\Framework\SelfDescribing;
use PHPUnit\Util\Type;
use ReflectionException;
use ReflectionNamedType;
use ReflectionObject;
use ReflectionType;
use SebastianBergmann\Exporter\Exporter;
use stdClass;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Invocation implements SelfDescribing
{
    private const TYPES_MAP = [
        'int'   => 'integer',
        'bool'  => 'boolean',
        'float' => 'double',
    ];

    /**
     * @var string
     */
    private $className;

    /**
     * @var string
     */
    private $methodName;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @var string
     */
    private $returnType;

    /**
     * @var bool
     */
    private $isReturnTypeNullable = false;

    /**
     * @var bool
     */
    private $proxiedCall;

    /**
     * @var object
     */
    private $object;

    public function __construct(
        string $className,
        string $methodName,
        array $parameters,
        string $returnType,
        object $object,
        bool $cloneObjects = false,
        bool $proxiedCall = false
    ) {
        $this->className   = $className;
        $this->methodName  = $methodName;
        $this->parameters  = array_values($parameters);
        $this->object      = $object;
        $this->proxiedCall = $proxiedCall;

        if (strtolower($methodName) === '__tostring') {
            $returnType = 'string';
        }

        if (strpos($returnType, '?') === 0) {
            $returnType                 = substr($returnType, 1);
            $this->isReturnTypeNullable = true;
        }

        $this->returnType = $returnType;

        if (!$cloneObjects) {
            return;
        }

        foreach ($this->parameters as $key => $value) {
            if (is_object($value)) {
                $this->parameters[$key] = $this->cloneObject($value);
            }
        }
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @throws RuntimeException
     *
     * @return mixed Mocked return value
     */
    public function generateReturnValue()
    {
        if ($this->isReturnTypeNullable || $this->proxiedCall) {
            return;
        }

        $returnType = $this->returnType;

        if (strpos($returnType, '|') !== false) {
            $types      = explode('|', $returnType);
            $returnType = $types[0];

            foreach ($types as $type) {
                if ($type === 'null') {
                    return;
                }
            }
        }

        switch (strtolower($returnType)) {
            case '':
            case 'void':
                return;

            case 'string':
                return '';

            case 'float':
                return 0.0;

            case 'int':
                return 0;

            case 'bool':
                return false;

            case 'array':
                return [];

            case 'object':
                return new stdClass;

            case 'callable':
            case 'closure':
                return static function (): void {
                };

            case 'traversable':
            case 'generator':
            case 'iterable':
                $generator = static function () {
                    yield;
                };

                return $generator();

            default:
                $generator = new Generator;

                return $generator->getMock($this->returnType, [], [], '', false);
        }
    }

    public function toString(): string
    {
        $exporter = new Exporter();

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

    public function getObject(): object
    {
        return $this->object;
    }

    /**
     * @throws ReflectionException
     */
    public function checkParameterTypes(): bool
    {
        $reflectionObject     = new ReflectionObject($this->getObject());
        $reflectionMethod     = $reflectionObject->getMethod($this->getMethodName());
        $reflectionParameters = $reflectionMethod->getParameters();

        $variadicParameters = false;

        foreach ($reflectionParameters as $index => $reflectionParameter) {
            if ($reflectionParameter->isVariadic()) {
                if ($index !== count($reflectionParameters) - 1) {
                    throw new ReflectionException('Only the last parameter can be variadic');
                }

                /** @var array $variadicParameters */
                $variadicParameters = array_slice($this->getParameters(), $index);

                foreach ($variadicParameters as $variadicParameter) {
                    if (!self::checkParameterType(
                        $reflectionParameter->getType(),
                        $variadicParameter
                    )) {
                        return false;
                    }
                }

                $variadicParameters = true;

                break;
            }

            if (array_key_exists($index, $this->getParameters())) {
                if (!self::checkParameterType(
                    $reflectionParameter->getType(),
                    $this->getParameters()[$index]
                )) {
                    return false;
                }
            } elseif (!$reflectionParameter->isDefaultValueAvailable()) {
                return false;
            }
        }

        return $variadicParameters
            || (count($this->getParameters()) <= count($reflectionParameters));
    }

    private function cloneObject(object $original): object
    {
        if (Type::isCloneable($original)) {
            return clone $original;
        }

        return $original;
    }

    /**
     * @param ReflectionType $reflectionType
     * @param mixed          $invokedParameter
     */
    private static function checkParameterType(
        ?ReflectionType $reflectionType,
        $invokedParameter
    ): bool {
        if (!$reflectionType) {
            return true;
        }

        if ($invokedParameter === null && $reflectionType->allowsNull()) {
            return true;
        }

        if ($reflectionType instanceof ReflectionNamedType) {
            if ($reflectionType->isBuiltin()) {
                $reflectionTypeName = $reflectionType->getName();

                if (array_key_exists($reflectionTypeName, self::TYPES_MAP)) {
                    $reflectionTypeName = self::TYPES_MAP[$reflectionTypeName];
                }

                return $reflectionTypeName === gettype($invokedParameter);
            }

            return (get_class($invokedParameter) === $reflectionType->getName())
                || is_subclass_of($invokedParameter, $reflectionType->getName(), false);
        }

        throw new RuntimeException('Can not define parameter type');
    }
}
