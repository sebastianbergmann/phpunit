<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject\Generator;

use const PHP_EOL;
use function array_merge;
use function array_pop;
use function array_unique;
use function assert;
use function class_exists;
use function count;
use function explode;
use function implode;
use function in_array;
use function interface_exists;
use function is_array;
use function md5;
use function method_exists;
use function mt_rand;
use function preg_match;
use function serialize;
use function sort;
use function sprintf;
use function substr;
use function trait_exists;
use Exception;
use Iterator;
use IteratorAggregate;
use PHPUnit\Framework\MockObject\ConfigurableMethod;
use PHPUnit\Framework\MockObject\DoubledCloneMethod;
use PHPUnit\Framework\MockObject\Method;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\MockObjectApi;
use PHPUnit\Framework\MockObject\MockObjectInternal;
use PHPUnit\Framework\MockObject\ProxiedCloneMethod;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\MockObject\StubApi;
use PHPUnit\Framework\MockObject\StubInternal;
use PHPUnit\Framework\MockObject\TestDoubleState;
use PropertyHookType;
use ReflectionClass;
use ReflectionMethod;
use ReflectionObject;
use ReflectionProperty;
use SebastianBergmann\Type\ReflectionMapper;
use SebastianBergmann\Type\Type;
use Throwable;
use Traversable;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Generator
{
    use TemplateLoader;

    /**
     * @var non-empty-array<non-empty-string, true>
     */
    private const array EXCLUDED_METHOD_NAMES = [
        '__CLASS__'       => true,
        '__DIR__'         => true,
        '__FILE__'        => true,
        '__FUNCTION__'    => true,
        '__LINE__'        => true,
        '__METHOD__'      => true,
        '__NAMESPACE__'   => true,
        '__TRAIT__'       => true,
        '__clone'         => true,
        '__halt_compiler' => true,
    ];

    /**
     * @var array<non-empty-string, DoubledClass>
     */
    private static array $cache = [];

    /**
     * Returns a test double for the specified class.
     *
     * @param class-string            $type
     * @param ?list<non-empty-string> $methods
     * @param array<mixed>            $arguments
     *
     * @throws ClassIsEnumerationException
     * @throws ClassIsFinalException
     * @throws DuplicateMethodException
     * @throws InvalidMethodNameException
     * @throws NameAlreadyInUseException
     * @throws ReflectionException
     * @throws RuntimeException
     * @throws UnknownTypeException
     */
    public function testDouble(string $type, bool $mockObject, ?array $methods = [], array $arguments = [], string $mockClassName = '', bool $callOriginalConstructor = true, bool $callOriginalClone = true, bool $returnValueGeneration = true): MockObject|Stub
    {
        if ($type === Traversable::class) {
            $type = Iterator::class;
        }

        $this->ensureKnownType($type);
        $this->ensureValidMethods($methods);
        $this->ensureNameForTestDoubleClassIsAvailable($mockClassName);

        $mock = $this->generate(
            $type,
            $mockObject,
            $methods,
            $mockClassName,
            $callOriginalClone,
        );

        $object = $this->instantiate(
            $mock,
            $callOriginalConstructor,
            $arguments,
            $returnValueGeneration,
        );

        assert($object instanceof $type);

        if ($mockObject) {
            assert($object instanceof MockObject);
        } else {
            assert($object instanceof Stub);
        }

        return $object;
    }

    /**
     * @param list<class-string> $interfaces
     *
     * @throws RuntimeException
     * @throws UnknownInterfaceException
     */
    public function testDoubleForInterfaceIntersection(array $interfaces, bool $mockObject, bool $returnValueGeneration = true): MockObject|Stub
    {
        if (count($interfaces) < 2) {
            throw new RuntimeException('At least two interfaces must be specified');
        }

        foreach ($interfaces as $interface) {
            if (!interface_exists($interface)) {
                throw new UnknownInterfaceException($interface);
            }
        }

        sort($interfaces);

        $methods = [];

        foreach ($interfaces as $interface) {
            $methods = array_merge($methods, $this->namesOfMethodsIn($interface));
        }

        if (count(array_unique($methods)) < count($methods)) {
            throw new RuntimeException('Interfaces must not declare the same method');
        }

        $unqualifiedNames = [];

        foreach ($interfaces as $interface) {
            $parts              = explode('\\', $interface);
            $unqualifiedNames[] = array_pop($parts);
        }

        sort($unqualifiedNames);

        do {
            $intersectionName = sprintf(
                'Intersection_%s_%s',
                implode('_', $unqualifiedNames),
                substr(md5((string) mt_rand()), 0, 8),
            );
        } while (interface_exists($intersectionName, false));

        $template = $this->loadTemplate('intersection.tpl');

        $template->setVar(
            [
                'intersection' => $intersectionName,
                'interfaces'   => implode(', ', $interfaces),
            ],
        );

        eval($template->render());

        assert(interface_exists($intersectionName));

        return $this->testDouble(
            $intersectionName,
            $mockObject,
            returnValueGeneration: $returnValueGeneration,
        );
    }

    /**
     * @param class-string            $type
     * @param ?list<non-empty-string> $methods
     *
     * @throws ClassIsEnumerationException
     * @throws ClassIsFinalException
     * @throws ReflectionException
     * @throws RuntimeException
     *
     * @todo This method is only public because it is used to test generated code in PHPT tests
     *
     * @see https://github.com/sebastianbergmann/phpunit/issues/5476
     */
    public function generate(string $type, bool $mockObject, ?array $methods = null, string $mockClassName = '', bool $callOriginalClone = true): DoubledClass
    {
        if ($mockClassName !== '') {
            return $this->generateCodeForTestDoubleClass(
                $type,
                $mockObject,
                $methods,
                $mockClassName,
                $callOriginalClone,
            );
        }

        $key = md5(
            $type .
            ($mockObject ? 'MockObject' : 'TestStub') .
            serialize($methods) .
            serialize($callOriginalClone),
        );

        if (!isset(self::$cache[$key])) {
            self::$cache[$key] = $this->generateCodeForTestDoubleClass(
                $type,
                $mockObject,
                $methods,
                $mockClassName,
                $callOriginalClone,
            );
        }

        return self::$cache[$key];
    }

    /**
     * @param class-string $className
     *
     * @throws ReflectionException
     *
     * @return list<DoubledMethod>
     */
    private function mockClassMethods(string $className): array
    {
        $class   = $this->reflectClass($className);
        $methods = [];

        foreach ($class->getMethods() as $method) {
            if (($method->isPublic() || $method->isAbstract()) && $this->canMethodBeDoubled($method)) {
                $methods[] = DoubledMethod::fromReflection($method);
            }
        }

        return $methods;
    }

    /**
     * @param class-string $interfaceName
     *
     * @throws ReflectionException
     *
     * @return list<ReflectionMethod>
     */
    private function userDefinedInterfaceMethods(string $interfaceName): array
    {
        $interface = $this->reflectClass($interfaceName);
        $methods   = [];

        foreach ($interface->getMethods() as $method) {
            if (!$method->isUserDefined()) {
                continue;
            }

            $methods[] = $method;
        }

        return $methods;
    }

    /**
     * @param array<mixed> $arguments
     *
     * @throws ReflectionException
     * @throws RuntimeException
     */
    private function instantiate(DoubledClass $mockClass, bool $callOriginalConstructor = false, array $arguments = [], bool $returnValueGeneration = true): object
    {
        $className = $mockClass->generate();

        try {
            $object = (new ReflectionClass($className))->newInstanceWithoutConstructor();
            // @codeCoverageIgnoreStart
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                $e->getMessage(),
                $e->getCode(),
                $e,
            );
            // @codeCoverageIgnoreEnd
        }

        $reflector = new ReflectionObject($object);

        /**
         * @noinspection PhpUnhandledExceptionInspection
         */
        $reflector->getProperty('__phpunit_state')->setValue(
            $object,
            new TestDoubleState($mockClass->configurableMethods(), $returnValueGeneration),
        );

        if ($callOriginalConstructor && $reflector->getConstructor() !== null) {
            try {
                $reflector->getConstructor()->invokeArgs($object, $arguments);
                // @codeCoverageIgnoreStart
            } catch (\ReflectionException $e) {
                throw new ReflectionException(
                    $e->getMessage(),
                    $e->getCode(),
                    $e,
                );
                // @codeCoverageIgnoreEnd
            }
        }

        return $object;
    }

    /**
     * @param class-string            $type
     * @param ?list<non-empty-string> $explicitMethods
     *
     * @throws ClassIsEnumerationException
     * @throws ClassIsFinalException
     * @throws MethodNamedMethodException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    private function generateCodeForTestDoubleClass(string $type, bool $mockObject, ?array $explicitMethods, string $mockClassName, bool $callOriginalClone): DoubledClass
    {
        $classTemplate         = $this->loadTemplate('test_double_class.tpl');
        $additionalInterfaces  = [];
        $doubledCloneMethod    = false;
        $proxiedCloneMethod    = false;
        $isClass               = false;
        $isReadonly            = false;
        $isInterface           = false;
        $mockMethods           = new DoubledMethodSet;
        $testDoubleClassPrefix = $mockObject ? 'MockObject_' : 'TestStub_';

        $_mockClassName = $this->generateClassName(
            $type,
            $mockClassName,
            $testDoubleClassPrefix,
        );

        if (class_exists($_mockClassName['fullClassName'])) {
            $isClass = true;
        } elseif (interface_exists($_mockClassName['fullClassName'])) {
            $isInterface = true;
        }

        $class = $this->reflectClass($_mockClassName['fullClassName']);

        if ($class->isEnum()) {
            throw new ClassIsEnumerationException($_mockClassName['fullClassName']);
        }

        if ($class->isFinal()) {
            throw new ClassIsFinalException($_mockClassName['fullClassName']);
        }

        if ($class->isReadOnly()) {
            $isReadonly = true;
        }

        // @see https://github.com/sebastianbergmann/phpunit/issues/2995
        if ($isInterface && $class->implementsInterface(Throwable::class)) {
            $actualClassName        = Exception::class;
            $additionalInterfaces[] = $class->getName();
            $isInterface            = false;
            $class                  = $this->reflectClass($actualClassName);

            foreach ($this->userDefinedInterfaceMethods($_mockClassName['fullClassName']) as $method) {
                $methodName = $method->getName();

                if ($class->hasMethod($methodName)) {
                    $classMethod = $class->getMethod($methodName);

                    if (!$this->canMethodBeDoubled($classMethod)) {
                        continue;
                    }
                }

                $mockMethods->addMethods(
                    DoubledMethod::fromReflection($method),
                );
            }

            $_mockClassName = $this->generateClassName(
                $actualClassName,
                $_mockClassName['className'],
                $testDoubleClassPrefix,
            );
        }

        // @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/103
        if ($isInterface && $class->implementsInterface(Traversable::class) &&
            !$class->implementsInterface(Iterator::class) &&
            !$class->implementsInterface(IteratorAggregate::class)) {
            $additionalInterfaces[] = Iterator::class;

            $mockMethods->addMethods(
                ...$this->mockClassMethods(Iterator::class),
            );
        }

        if ($class->hasMethod('__clone')) {
            $cloneMethod = $class->getMethod('__clone');

            if (!$cloneMethod->isFinal()) {
                if ($callOriginalClone && !$isInterface) {
                    $proxiedCloneMethod = true;
                } else {
                    $doubledCloneMethod = true;
                }
            }
        } else {
            $doubledCloneMethod = true;
        }

        if ($isClass && $explicitMethods === []) {
            $mockMethods->addMethods(
                ...$this->mockClassMethods($_mockClassName['fullClassName']),
            );
        }

        if ($isInterface && ($explicitMethods === [] || $explicitMethods === null)) {
            $mockMethods->addMethods(
                ...$this->interfaceMethods($_mockClassName['fullClassName']),
            );
        }

        if (is_array($explicitMethods)) {
            foreach ($explicitMethods as $methodName) {
                if ($class->hasMethod($methodName)) {
                    $method = $class->getMethod($methodName);

                    if ($this->canMethodBeDoubled($method)) {
                        $mockMethods->addMethods(
                            DoubledMethod::fromReflection($method),
                        );
                    }
                } else {
                    $mockMethods->addMethods(
                        DoubledMethod::fromName(
                            $_mockClassName['fullClassName'],
                            $methodName,
                        ),
                    );
                }
            }
        }

        $propertiesWithHooks = $this->properties($class);
        $configurableMethods = $this->configurableMethods($mockMethods, $propertiesWithHooks);

        $mockedMethods = '';

        foreach ($mockMethods->asArray() as $mockMethod) {
            $mockedMethods .= $mockMethod->generateCode();
        }

        /** @var trait-string[] $traits */
        $traits = [StubApi::class];

        if ($mockObject) {
            $traits[] = MockObjectApi::class;
        }

        if ($mockMethods->hasMethod('method') || $class->hasMethod('method')) {
            throw new MethodNamedMethodException;
        }

        $traits[] = Method::class;

        if ($doubledCloneMethod) {
            $traits[] = DoubledCloneMethod::class;
        } elseif ($proxiedCloneMethod) {
            $traits[] = ProxiedCloneMethod::class;
        }

        $useStatements = '';

        foreach ($traits as $trait) {
            $useStatements .= sprintf(
                '    use %s;' . PHP_EOL,
                $trait,
            );
        }

        unset($traits);

        $classTemplate->setVar(
            [
                'class_declaration' => $this->generateTestDoubleClassDeclaration(
                    $mockObject,
                    $_mockClassName,
                    $isInterface,
                    $additionalInterfaces,
                    $isReadonly,
                ),
                'use_statements'  => $useStatements,
                'mock_class_name' => $_mockClassName['className'],
                'methods'         => $mockedMethods,
                'property_hooks'  => (new HookedPropertyGenerator)->generate(
                    $_mockClassName['className'],
                    $propertiesWithHooks,
                ),
            ],
        );

        return new DoubledClass(
            $classTemplate->render(),
            $_mockClassName['className'],
            $configurableMethods,
        );
    }

    /**
     * @param class-string $type
     *
     * @return array{className: class-string, originalClassName: class-string, fullClassName: class-string, namespaceName: string}
     */
    private function generateClassName(string $type, string $className, string $prefix): array
    {
        if ($type[0] === '\\') {
            $type = substr($type, 1);
        }

        $classNameParts = explode('\\', $type);

        if (count($classNameParts) > 1) {
            $type          = array_pop($classNameParts);
            $namespaceName = implode('\\', $classNameParts);
            $fullClassName = $namespaceName . '\\' . $type;
        } else {
            $namespaceName = '';
            $fullClassName = $type;
        }

        if ($className === '') {
            do {
                $className = $prefix . $type . '_' .
                             substr(md5((string) mt_rand()), 0, 8);
            } while (class_exists($className, false));
        }

        return [
            'className'         => $className,
            'originalClassName' => $type,
            'fullClassName'     => $fullClassName,
            'namespaceName'     => $namespaceName,
        ];
    }

    /**
     * @param array{className: non-empty-string, originalClassName: non-empty-string, fullClassName: non-empty-string, namespaceName: string} $mockClassName
     * @param list<class-string>                                                                                                              $additionalInterfaces
     */
    private function generateTestDoubleClassDeclaration(bool $mockObject, array $mockClassName, bool $isInterface, array $additionalInterfaces, bool $isReadonly): string
    {
        if ($mockObject) {
            $additionalInterfaces[] = MockObjectInternal::class;
        } else {
            $additionalInterfaces[] = StubInternal::class;
        }

        if ($isReadonly) {
            $buffer = 'readonly class ';
        } else {
            $buffer = 'class ';
        }

        $interfaces = implode(', ', $additionalInterfaces);

        if ($isInterface) {
            $buffer .= sprintf(
                '%s implements %s',
                $mockClassName['className'],
                $interfaces,
            );

            if (!in_array($mockClassName['originalClassName'], $additionalInterfaces, true)) {
                $buffer .= ', ';

                if ($mockClassName['namespaceName'] !== '') {
                    $buffer .= $mockClassName['namespaceName'] . '\\';
                }

                $buffer .= $mockClassName['originalClassName'];
            }
        } else {
            $buffer .= sprintf(
                '%s extends %s%s implements %s',
                $mockClassName['className'],
                $mockClassName['namespaceName'] !== '' ? $mockClassName['namespaceName'] . '\\' : '',
                $mockClassName['originalClassName'],
                $interfaces,
            );
        }

        return $buffer;
    }

    private function canMethodBeDoubled(ReflectionMethod $method): bool
    {
        if ($method->isConstructor()) {
            return false;
        }

        if ($method->isDestructor()) {
            return false;
        }

        if ($method->isFinal()) {
            return false;
        }

        if ($method->isPrivate()) {
            return false;
        }

        return !$this->isMethodNameExcluded($method->getName());
    }

    private function isMethodNameExcluded(string $name): bool
    {
        return isset(self::EXCLUDED_METHOD_NAMES[$name]);
    }

    /**
     * @throws UnknownTypeException
     */
    private function ensureKnownType(string $type): void
    {
        if (!class_exists($type) && !interface_exists($type)) {
            throw new UnknownTypeException($type);
        }
    }

    /**
     * @param ?list<non-empty-string> $methods
     *
     * @throws DuplicateMethodException
     * @throws InvalidMethodNameException
     */
    private function ensureValidMethods(?array $methods): void
    {
        if ($methods === null) {
            return;
        }

        foreach ($methods as $method) {
            if (!preg_match('~[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*~', (string) $method)) {
                throw new InvalidMethodNameException((string) $method);
            }
        }

        if ($methods !== array_unique($methods)) {
            throw new DuplicateMethodException($methods);
        }
    }

    /**
     * @throws NameAlreadyInUseException
     * @throws ReflectionException
     */
    private function ensureNameForTestDoubleClassIsAvailable(string $className): void
    {
        if ($className === '') {
            return;
        }

        if (class_exists($className, false) ||
            interface_exists($className, false) ||
            trait_exists($className, false)) {
            throw new NameAlreadyInUseException($className);
        }
    }

    /**
     * @param class-string $className
     *
     * @throws ReflectionException
     *
     * @phpstan-ignore missingType.generics, throws.unusedType
     */
    private function reflectClass(string $className): ReflectionClass
    {
        try {
            $class = new ReflectionClass($className);

            // @codeCoverageIgnoreStart
            /** @phpstan-ignore catch.neverThrown */
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }
        // @codeCoverageIgnoreEnd

        return $class;
    }

    /**
     * @param class-string $classOrInterfaceName
     *
     * @throws ReflectionException
     *
     * @return list<string>
     */
    private function namesOfMethodsIn(string $classOrInterfaceName): array
    {
        $class   = $this->reflectClass($classOrInterfaceName);
        $methods = [];

        foreach ($class->getMethods() as $method) {
            if ($method->isPublic() || $method->isAbstract()) {
                $methods[] = $method->getName();
            }
        }

        return $methods;
    }

    /**
     * @param class-string $interfaceName
     *
     * @throws ReflectionException
     *
     * @return list<DoubledMethod>
     */
    private function interfaceMethods(string $interfaceName): array
    {
        $class   = $this->reflectClass($interfaceName);
        $methods = [];

        foreach ($class->getMethods() as $method) {
            $methods[] = DoubledMethod::fromReflection($method);
        }

        return $methods;
    }

    /**
     * @param list<HookedProperty> $propertiesWithHooks
     *
     * @return list<ConfigurableMethod>
     */
    private function configurableMethods(DoubledMethodSet $methods, array $propertiesWithHooks): array
    {
        $configurable = [];

        foreach ($methods->asArray() as $method) {
            $configurable[] = new ConfigurableMethod(
                $method->methodName(),
                $method->defaultParameterValues(),
                $method->numberOfParameters(),
                $method->returnType(),
            );
        }

        foreach ($propertiesWithHooks as $property) {
            if ($property->hasGetHook()) {
                $configurable[] = new ConfigurableMethod(
                    sprintf(
                        '$%s::get',
                        $property->name(),
                    ),
                    [],
                    0,
                    $property->type(),
                );
            }

            if ($property->hasSetHook()) {
                $configurable[] = new ConfigurableMethod(
                    sprintf(
                        '$%s::set',
                        $property->name(),
                    ),
                    [],
                    1,
                    Type::fromName('void', false),
                );
            }
        }

        return $configurable;
    }

    /**
     * @param ?ReflectionClass<object> $class
     *
     * @return list<HookedProperty>
     */
    private function properties(?ReflectionClass $class): array
    {
        if (!method_exists(ReflectionProperty::class, 'isFinal')) {
            // @codeCoverageIgnoreStart
            return [];
            // @codeCoverageIgnoreEnd
        }

        if ($class === null) {
            return [];
        }

        $mapper     = new ReflectionMapper;
        $properties = [];

        foreach ($class->getProperties() as $property) {
            assert(method_exists($property, 'getHook'));
            assert(method_exists($property, 'hasHooks'));
            assert(method_exists($property, 'hasHook'));
            assert(method_exists($property, 'isFinal'));
            assert(class_exists(PropertyHookType::class));

            if (!$property->isPublic()) {
                continue;
            }

            if ($property->isFinal()) {
                continue;
            }

            if (!$property->hasHooks()) {
                continue;
            }

            $hasGetHook = false;
            $hasSetHook = false;

            if ($property->hasHook(PropertyHookType::Get) &&
                !$property->getHook(PropertyHookType::Get)->isFinal()) {
                $hasGetHook = true;
            }

            if ($property->hasHook(PropertyHookType::Set) &&
                !$property->getHook(PropertyHookType::Set)->isFinal()) {
                $hasSetHook = true;
            }

            if (!$hasGetHook && !$hasSetHook) {
                continue;
            }

            $properties[] = new HookedProperty(
                $property->getName(),
                $mapper->fromPropertyType($property),
                $hasGetHook,
                $hasSetHook,
            );
        }

        return $properties;
    }
}
