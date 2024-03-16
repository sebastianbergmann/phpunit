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
use ReflectionClass;
use ReflectionMethod;
use Throwable;
use Traversable;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Generator
{
    use TemplateLoader;

    /**
     * @var array
     */
    private const EXCLUDED_METHOD_NAMES = [
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
     * @psalm-var array<non-empty-string, MockClass>
     */
    private static array $cache = [];

    /**
     * Returns a test double for the specified class.
     *
     * @throws ClassAlreadyExistsException
     * @throws ClassIsEnumerationException
     * @throws ClassIsFinalException
     * @throws ClassIsReadonlyException
     * @throws DuplicateMethodException
     * @throws InvalidMethodNameException
     * @throws MethodNamedMethodException
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
        $this->ensureMockedClassDoesNotAlreadyExist($mockClassName);

        $testDoubleClass = $this->generate(
            $type,
            $mockObject,
            $methods,
            $mockClassName,
            $callOriginalClone,
        );

        $testDoubleObject = $this->getObject(
            $testDoubleClass,
            $callOriginalConstructor,
            $arguments,
            $returnValueGeneration,
        );

        assert($testDoubleObject instanceof $type);

        if ($mockObject) {
            assert($testDoubleObject instanceof MockObject);
        } else {
            assert($testDoubleObject instanceof Stub);
        }

        return $testDoubleObject;
    }

    /**
     * @psalm-param list<class-string> $interfaces
     *
     * @throws RuntimeException
     * @throws UnknownTypeException
     */
    public function testDoubleForInterfaceIntersection(array $interfaces, bool $mockObject, bool $returnValueGeneration = true): MockObject|Stub
    {
        if (count($interfaces) < 2) {
            throw new RuntimeException('At least two interfaces must be specified');
        }

        foreach ($interfaces as $interface) {
            if (!interface_exists($interface)) {
                throw new UnknownTypeException($interface);
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

        return $this->testDouble(
            $intersectionName,
            $mockObject,
            returnValueGeneration: $returnValueGeneration,
        );
    }

    /**
     * @throws ClassIsEnumerationException
     * @throws ClassIsFinalException
     * @throws ClassIsReadonlyException
     * @throws MethodNamedMethodException
     * @throws ReflectionException
     * @throws RuntimeException
     *
     * @todo This method is only public because it is used to test generated code in PHPT tests
     *
     * @see https://github.com/sebastianbergmann/phpunit/issues/5476
     */
    public function generate(string $type, bool $mockObject, ?array $methods = null, string $mockClassName = '', bool $callOriginalClone = true): MockClass
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
     * @throws ReflectionException
     *
     * @psalm-return list<MockMethod>
     */
    public function mockClassMethods(string $className): array
    {
        $class   = $this->reflectClass($className);
        $methods = [];

        foreach ($class->getMethods() as $method) {
            if (($method->isPublic() || $method->isAbstract()) && $this->canMethodBeDoubled($method)) {
                $methods[] = MockMethod::fromReflection($method);
            }
        }

        return $methods;
    }

    /**
     * @psalm-param class-string $interfaceName
     *
     * @throws ReflectionException
     *
     * @psalm-return list<ReflectionMethod>
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
     * @throws ReflectionException
     * @throws RuntimeException
     */
    private function getObject(MockClass $mockClass, bool $callOriginalConstructor = false, array $arguments = [], bool $returnValueGeneration = true): object
    {
        $className = $mockClass->generate();
        $object    = $this->instantiate($className, $callOriginalConstructor, $arguments);

        if ($object instanceof StubInternal) {
            $object->__phpunit_setReturnValueGeneration($returnValueGeneration);
        }

        return $object;
    }

    /**
     * @throws ClassIsEnumerationException
     * @throws ClassIsFinalException
     * @throws ClassIsReadonlyException
     * @throws MethodNamedMethodException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    private function generateCodeForTestDoubleClass(string $type, bool $mockObject, ?array $explicitMethods, string $mockClassName, bool $callOriginalClone): MockClass
    {
        $classTemplate         = $this->loadTemplate('test_double_class.tpl');
        $additionalInterfaces  = [];
        $doubledCloneMethod    = false;
        $proxiedCloneMethod    = false;
        $isClass               = false;
        $isInterface           = false;
        $class                 = null;
        $mockMethods           = new MockMethodSet;
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

        if (!$isClass && !$isInterface) {
            $prologue = 'class ' . $_mockClassName['originalClassName'] . "\n{\n}\n\n";

            if (!empty($_mockClassName['namespaceName'])) {
                $prologue = 'namespace ' . $_mockClassName['namespaceName'] .
                    " {\n\n" . $prologue . "}\n\n" .
                    "namespace {\n\n";

                $epilogue = "\n\n}";
            }

            $doubledCloneMethod = true;
        } else {
            $class = $this->reflectClass($_mockClassName['fullClassName']);

            if ($class->isEnum()) {
                throw new ClassIsEnumerationException($_mockClassName['fullClassName']);
            }

            if ($class->isFinal()) {
                throw new ClassIsFinalException($_mockClassName['fullClassName']);
            }

            if (method_exists($class, 'isReadOnly') && $class->isReadOnly()) {
                throw new ClassIsReadonlyException($_mockClassName['fullClassName']);
            }

            if ($class->hasMethod('method') ||
                ($explicitMethods !== null && in_array('method', $explicitMethods, true))) {
                throw new MethodNamedMethodException;
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

                    $mockMethods->addMethods(MockMethod::fromReflection($method));
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

                $mockMethods->addMethods(...$this->mockClassMethods(Iterator::class));
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
                if ($class !== null && $class->hasMethod($methodName)) {
                    $method = $class->getMethod($methodName);

                    if ($this->canMethodBeDoubled($method)) {
                        $mockMethods->addMethods(
                            MockMethod::fromReflection($method),
                        );
                    }
                } else {
                    $mockMethods->addMethods(
                        MockMethod::fromName(
                            $_mockClassName['fullClassName'],
                            $methodName,
                        ),
                    );
                }
            }
        }

        $mockedMethods = '';
        $configurable  = [];

        foreach ($mockMethods->asArray() as $mockMethod) {
            $mockedMethods .= $mockMethod->generateCode();

            $configurable[] = new ConfigurableMethod(
                $mockMethod->methodName(),
                $mockMethod->defaultParameterValues(),
                $mockMethod->numberOfParameters(),
                $mockMethod->returnType(),
            );
        }

        /** @psalm-var trait-string[] $traits */
        $traits = [StubApi::class];

        if ($mockObject) {
            $traits[] = MockObjectApi::class;
        }

        if ($doubledCloneMethod) {
            $traits[] = DoubledCloneMethod::class;
        }

        if ($proxiedCloneMethod) {
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
                'prologue'          => $prologue ?? '',
                'epilogue'          => $epilogue ?? '',
                'class_declaration' => $this->generateTestDoubleClassDeclaration(
                    $mockObject,
                    $_mockClassName,
                    $isInterface,
                    $additionalInterfaces,
                ),
                'use_statements'  => $useStatements,
                'mock_class_name' => $_mockClassName['className'],
                'mocked_methods'  => $mockedMethods,
            ],
        );

        return new MockClass(
            $classTemplate->render(),
            $_mockClassName['className'],
            $configurable,
        );
    }

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

    private function generateTestDoubleClassDeclaration(bool $mockObject, array $mockClassName, bool $isInterface, array $additionalInterfaces = []): string
    {
        if ($mockObject) {
            $additionalInterfaces[] = MockObjectInternal::class;
        } else {
            $additionalInterfaces[] = StubInternal::class;
        }

        $buffer     = 'class ';
        $interfaces = implode(', ', $additionalInterfaces);

        if ($isInterface) {
            $buffer .= sprintf(
                '%s implements %s',
                $mockClassName['className'],
                $interfaces,
            );

            if (!in_array($mockClassName['originalClassName'], $additionalInterfaces, true)) {
                $buffer .= ', ';

                if (!empty($mockClassName['namespaceName'])) {
                    $buffer .= $mockClassName['namespaceName'] . '\\';
                }

                $buffer .= $mockClassName['originalClassName'];
            }
        } else {
            $buffer .= sprintf(
                '%s extends %s%s implements %s',
                $mockClassName['className'],
                !empty($mockClassName['namespaceName']) ? $mockClassName['namespaceName'] . '\\' : '',
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
     * @throws ClassAlreadyExistsException
     * @throws ReflectionException
     */
    private function ensureMockedClassDoesNotAlreadyExist(string $mockClassName): void
    {
        if ($mockClassName !== '' && class_exists($mockClassName, false)) {
            $reflector = $this->reflectClass($mockClassName);

            if (!$reflector->implementsInterface(MockObject::class)) {
                throw new ClassAlreadyExistsException($mockClassName);
            }
        }
    }

    /**
     * @psalm-param class-string $className
     *
     * @throws ReflectionException
     */
    private function instantiate(string $className, bool $callOriginalConstructor, array $arguments): object
    {
        if ($callOriginalConstructor) {
            if (count($arguments) === 0) {
                return new $className;
            }

            try {
                return (new ReflectionClass($className))->newInstanceArgs($arguments);
                // @codeCoverageIgnoreStart
            } catch (\ReflectionException $e) {
                throw new ReflectionException(
                    $e->getMessage(),
                    $e->getCode(),
                    $e,
                );
            }
            // @codeCoverageIgnoreEnd
        }

        try {
            return (new ReflectionClass($className))->newInstanceWithoutConstructor();
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

    /**
     * @psalm-param class-string $className
     *
     * @throws ReflectionException
     */
    private function reflectClass(string $className): ReflectionClass
    {
        try {
            $class = new ReflectionClass($className);
            // @codeCoverageIgnoreStart
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
     * @psalm-param class-string $classOrInterfaceName
     *
     * @psalm-return list<string>
     *
     * @throws ReflectionException
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
     * @psalm-param class-string $interfaceName
     *
     * @psalm-return list<MockMethod>
     *
     * @throws ReflectionException
     */
    private function interfaceMethods(string $interfaceName): array
    {
        $class   = $this->reflectClass($interfaceName);
        $methods = [];

        foreach ($class->getMethods() as $method) {
            $methods[] = MockMethod::fromReflection($method);
        }

        return $methods;
    }
}
