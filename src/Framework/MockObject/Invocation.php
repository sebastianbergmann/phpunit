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
     * @throws RuntimeException
     */
    public function checkParameterTypes(): void
    {
        $reflectionObject     = new ReflectionObject($this->getObject());
        $reflectionMethod     = $reflectionObject->getMethod($this->getMethodName());
        $reflectionParameters = $reflectionMethod->getParameters();

        $variadicParametersChecked = false;

        foreach ($reflectionParameters as $index => $reflectionParameter) {
            if ($reflectionParameter->isVariadic()) {
                if ($index !== count($reflectionParameters) - 1) {
                    throw new ReflectionException('Only the last parameter can be variadic');
                }

                /** @var array $variadicParametersChecked */
                $variadicParametersChecked = array_slice($this->getParameters(), $index);

                foreach ($variadicParametersChecked as $variadicIndex => $variadicParameter) {
                    $this->checkParameterType(
                        $reflectionParameter->getType(),
                        $variadicParameter,
                        $index + $variadicIndex
                    );
                }

                $variadicParametersChecked = true;

                break;
            }

            if (array_key_exists($index, $this->getParameters())) {
                $this->checkParameterType(
                    $reflectionParameter->getType(),
                    $this->getParameters()[$index],
                    $index
                );
            } elseif (!$reflectionParameter->isDefaultValueAvailable()) {
                throw new RuntimeException(
                    sprintf(
                        'Nothing passed as parameter %d of method %s::%s, and no default value available',
                        $index,
                        $this->getClassName(),
                        $this->getMethodName()
                    )
                );
            }
        }

        if (
            !$variadicParametersChecked &&
            (count($this->getParameters()) > count($reflectionParameters))
        ) {
            throw new RuntimeException(
                sprintf(
                    'Too many values passed to method %s::%s',
                    $this->getClassName(),
                    $this->getMethodName()
                )
            );
        }
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
     *
     * @throws RuntimeException
     */
    private function checkParameterType(
        ?ReflectionType $reflectionType,
        $invokedParameter,
        int $index
    ): void {
        if (!$reflectionType) {
            return;
        }

        if ($invokedParameter === null && $reflectionType->allowsNull()) {
            return;
        }

        if ($reflectionType instanceof ReflectionNamedType) {
            if ($reflectionType->isBuiltin()) {
                $reflectionTypeName = $reflectionType->getName();

                if (array_key_exists($reflectionTypeName, self::TYPES_MAP)) {
                    $reflectionTypeName = self::TYPES_MAP[$reflectionTypeName];
                }

                $invokedType = gettype($invokedParameter);

                if ($reflectionTypeName !== $invokedType) {
                    throw $this->getStrictTypeException($index, $reflectionTypeName, $invokedType);
                }
            } else {
                $invokedClass = get_class($invokedParameter);

                if (
                    ($invokedClass !== $reflectionType->getName()) &&
                    !is_subclass_of($invokedParameter, $reflectionType->getName(), false)
                ) {
                    throw $this->getStrictTypeException(
                        $index,
                        $reflectionType->getName(),
                        $invokedClass
                    );
                }
            }
        } else {
            throw new RuntimeException('Can not define parameter type');
        }
    }

    private function getStrictTypeException(
        int $argumentIndex,
        string $declaredType,
        string $actualType
    ): RuntimeException {
        return new RuntimeException(
            sprintf(
                'Argument %d passed to method %s::%s must be of type %s; %s given',
                $argumentIndex,
                $this->getClassName(),
                $this->getMethodName(),
                $declaredType,
                $actualType
            )
        );
    }
}
