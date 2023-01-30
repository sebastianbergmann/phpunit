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

use const PHP_EOL;
use const PREG_OFFSET_CAPTURE;
use const WSDL_CACHE_NONE;
use function array_merge;
use function array_pop;
use function array_unique;
use function assert;
use function class_exists;
use function count;
use function explode;
use function extension_loaded;
use function implode;
use function in_array;
use function interface_exists;
use function is_array;
use function is_object;
use function md5;
use function method_exists;
use function mt_rand;
use function preg_match;
use function preg_match_all;
use function range;
use function serialize;
use function sort;
use function sprintf;
use function str_contains;
use function str_replace;
use function strlen;
use function strpos;
use function substr;
use function trait_exists;
use Exception;
use Iterator;
use IteratorAggregate;
use PHPUnit\Framework\InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;
use SoapClient;
use SoapFault;
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
    private static array $cache = [];

    /**
     * Returns a mock object for the specified class.
     *
     * @throws ClassAlreadyExistsException
     * @throws ClassIsEnumerationException
     * @throws ClassIsFinalException
     * @throws ClassIsReadonlyException
     * @throws DuplicateMethodException
     * @throws InvalidMethodNameException
     * @throws OriginalConstructorInvocationRequiredException
     * @throws ReflectionException
     * @throws RuntimeException
     * @throws UnknownTypeException
     */
    public function getMock(string $type, ?array $methods = [], array $arguments = [], string $mockClassName = '', bool $callOriginalConstructor = true, bool $callOriginalClone = true, bool $callAutoload = true, bool $cloneArguments = true, bool $callOriginalMethods = false, object $proxyTarget = null, bool $allowMockingUnknownTypes = true, bool $returnValueGeneration = true): MockObject
    {
        if ($type === Traversable::class) {
            $type = Iterator::class;
        }

        if (!$allowMockingUnknownTypes) {
            $this->ensureKnownType($type, $callAutoload);
        }

        $this->ensureValidMethods($methods);
        $this->ensureMockedClassDoesNotAlreadyExist($mockClassName);

        if (!$callOriginalConstructor && $callOriginalMethods) {
            throw new OriginalConstructorInvocationRequiredException;
        }

        $mock = $this->generate(
            $type,
            $methods,
            $mockClassName,
            $callOriginalClone,
            $callAutoload,
            $cloneArguments,
            $callOriginalMethods
        );

        $object = $this->getObject(
            $mock,
            $type,
            $callOriginalConstructor,
            $arguments,
            $callOriginalMethods,
            $proxyTarget,
            $returnValueGeneration
        );

        assert($object instanceof MockObject);

        return $object;
    }

    /**
     * @psalm-param list<class-string> $interfaces
     *
     * @throws RuntimeException
     * @throws UnknownTypeException
     */
    public function getMockForInterfaces(array $interfaces, bool $callAutoload = true): MockObject
    {
        if (count($interfaces) < 2) {
            throw new RuntimeException('At least two interfaces must be specified');
        }

        foreach ($interfaces as $interface) {
            if (!interface_exists($interface, $callAutoload)) {
                throw new UnknownTypeException($interface);
            }
        }

        sort($interfaces);

        $methods = [];

        foreach ($interfaces as $interface) {
            $methods = array_merge($methods, $this->getClassMethods($interface));
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
                substr(md5((string) mt_rand()), 0, 8)
            );
        } while (interface_exists($intersectionName, false));

        $template = $this->loadTemplate('intersection.tpl');

        $template->setVar(
            [
                'intersection' => $intersectionName,
                'interfaces'   => implode(', ', $interfaces),
            ]
        );

        eval($template->render());

        return $this->getMock($intersectionName);
    }

    /**
     * Returns a mock object for the specified abstract class with all abstract
     * methods of the class mocked.
     *
     * Concrete methods to mock can be specified with the $mockedMethods parameter.
     *
     * @psalm-template RealInstanceType of object
     *
     * @psalm-param class-string<RealInstanceType> $originalClassName
     *
     * @psalm-return MockObject&RealInstanceType
     *
     * @throws ClassAlreadyExistsException
     * @throws ClassIsEnumerationException
     * @throws ClassIsFinalException
     * @throws ClassIsReadonlyException
     * @throws DuplicateMethodException
     * @throws InvalidArgumentException
     * @throws InvalidMethodNameException
     * @throws OriginalConstructorInvocationRequiredException
     * @throws ReflectionException
     * @throws RuntimeException
     * @throws UnknownClassException
     * @throws UnknownTypeException
     */
    public function getMockForAbstractClass(string $originalClassName, array $arguments = [], string $mockClassName = '', bool $callOriginalConstructor = true, bool $callOriginalClone = true, bool $callAutoload = true, array $mockedMethods = null, bool $cloneArguments = true): MockObject
    {
        if (class_exists($originalClassName, $callAutoload) ||
            interface_exists($originalClassName, $callAutoload)) {
            try {
                $reflector = new ReflectionClass($originalClassName);
                // @codeCoverageIgnoreStart
            } catch (\ReflectionException $e) {
                throw new ReflectionException(
                    $e->getMessage(),
                    $e->getCode(),
                    $e
                );
            }
            // @codeCoverageIgnoreEnd

            $methods = $mockedMethods;

            foreach ($reflector->getMethods() as $method) {
                if ($method->isAbstract() && !in_array($method->getName(), $methods ?? [], true)) {
                    $methods[] = $method->getName();
                }
            }

            if (empty($methods)) {
                $methods = null;
            }

            return $this->getMock(
                $originalClassName,
                $methods,
                $arguments,
                $mockClassName,
                $callOriginalConstructor,
                $callOriginalClone,
                $callAutoload,
                $cloneArguments
            );
        }

        throw new UnknownClassException($originalClassName);
    }

    /**
     * Returns a mock object for the specified trait with all abstract methods
     * of the trait mocked. Concrete methods to mock can be specified with the
     * `$mockedMethods` parameter.
     *
     * @psalm-param trait-string $traitName
     *
     * @throws ClassAlreadyExistsException
     * @throws ClassIsEnumerationException
     * @throws ClassIsFinalException
     * @throws ClassIsReadonlyException
     * @throws DuplicateMethodException
     * @throws InvalidArgumentException
     * @throws InvalidMethodNameException
     * @throws OriginalConstructorInvocationRequiredException
     * @throws ReflectionException
     * @throws RuntimeException
     * @throws UnknownClassException
     * @throws UnknownTraitException
     * @throws UnknownTypeException
     */
    public function getMockForTrait(string $traitName, array $arguments = [], string $mockClassName = '', bool $callOriginalConstructor = true, bool $callOriginalClone = true, bool $callAutoload = true, array $mockedMethods = null, bool $cloneArguments = true): MockObject
    {
        if (!trait_exists($traitName, $callAutoload)) {
            throw new UnknownTraitException($traitName);
        }

        $className = $this->generateClassName(
            $traitName,
            '',
            'Trait_'
        );

        $classTemplate = $this->loadTemplate('trait_class.tpl');

        $classTemplate->setVar(
            [
                'prologue'   => 'abstract ',
                'class_name' => $className['className'],
                'trait_name' => $traitName,
            ]
        );

        $mockTrait = new MockTrait($classTemplate->render(), $className['className']);
        $mockTrait->generate();

        return $this->getMockForAbstractClass($className['className'], $arguments, $mockClassName, $callOriginalConstructor, $callOriginalClone, $callAutoload, $mockedMethods, $cloneArguments);
    }

    /**
     * Returns an object for the specified trait.
     *
     * @psalm-param trait-string $traitName
     *
     * @throws ReflectionException
     * @throws RuntimeException
     * @throws UnknownTraitException
     */
    public function getObjectForTrait(string $traitName, string $traitClassName = '', bool $callAutoload = true, bool $callOriginalConstructor = false, array $arguments = []): object
    {
        if (!trait_exists($traitName, $callAutoload)) {
            throw new UnknownTraitException($traitName);
        }

        $className = $this->generateClassName(
            $traitName,
            $traitClassName,
            'Trait_'
        );

        $classTemplate = $this->loadTemplate('trait_class.tpl');

        $classTemplate->setVar(
            [
                'prologue'   => '',
                'class_name' => $className['className'],
                'trait_name' => $traitName,
            ]
        );

        return $this->getObject(
            new MockTrait(
                $classTemplate->render(),
                $className['className']
            ),
            '',
            $callOriginalConstructor,
            $arguments
        );
    }

    /**
     * @throws ClassIsEnumerationException
     * @throws ClassIsFinalException
     * @throws ClassIsReadonlyException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function generate(string $type, array $methods = null, string $mockClassName = '', bool $callOriginalClone = true, bool $callAutoload = true, bool $cloneArguments = true, bool $callOriginalMethods = false): MockClass
    {
        if ($mockClassName !== '') {
            return $this->generateMock(
                $type,
                $methods,
                $mockClassName,
                $callOriginalClone,
                $callAutoload,
                $cloneArguments,
                $callOriginalMethods
            );
        }

        $key = md5(
            $type .
            serialize($methods) .
            serialize($callOriginalClone) .
            serialize($cloneArguments) .
            serialize($callOriginalMethods)
        );

        if (!isset(self::$cache[$key])) {
            self::$cache[$key] = $this->generateMock(
                $type,
                $methods,
                $mockClassName,
                $callOriginalClone,
                $callAutoload,
                $cloneArguments,
                $callOriginalMethods
            );
        }

        return self::$cache[$key];
    }

    /**
     * @throws RuntimeException
     * @throws SoapExtensionNotAvailableException
     */
    public function generateClassFromWsdl(string $wsdlFile, string $className, array $methods = [], array $options = []): string
    {
        if (!extension_loaded('soap')) {
            throw new SoapExtensionNotAvailableException;
        }

        $options['cache_wsdl'] = WSDL_CACHE_NONE;

        try {
            $client   = new SoapClient($wsdlFile, $options);
            $_methods = array_unique($client->__getFunctions());
            unset($client);
        } catch (SoapFault $e) {
            throw new RuntimeException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }

        sort($_methods);

        $methodTemplate = $this->loadTemplate('wsdl_method.tpl');
        $methodsBuffer  = '';

        foreach ($_methods as $method) {
            preg_match_all('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*\(/', $method, $matches, PREG_OFFSET_CAPTURE);
            $lastFunction = array_pop($matches[0]);
            $nameStart    = $lastFunction[1];
            $nameEnd      = $nameStart + strlen($lastFunction[0]) - 1;
            $name         = str_replace('(', '', $lastFunction[0]);

            if (empty($methods) || in_array($name, $methods, true)) {
                $arguments = explode(
                    ',',
                    str_replace(')', '', substr($method, $nameEnd + 1))
                );

                foreach (range(0, count($arguments) - 1) as $i) {
                    $parameterStart = strpos($arguments[$i], '$');

                    if (!$parameterStart) {
                        continue;
                    }

                    $arguments[$i] = substr($arguments[$i], $parameterStart);
                }

                $methodTemplate->setVar(
                    [
                        'method_name' => $name,
                        'arguments'   => implode(', ', $arguments),
                    ]
                );

                $methodsBuffer .= $methodTemplate->render();
            }
        }

        $optionsBuffer = '[';

        foreach ($options as $key => $value) {
            $optionsBuffer .= $key . ' => ' . $value;
        }

        $optionsBuffer .= ']';

        $classTemplate = $this->loadTemplate('wsdl_class.tpl');
        $namespace     = '';

        if (str_contains($className, '\\')) {
            $parts     = explode('\\', $className);
            $className = array_pop($parts);
            $namespace = 'namespace ' . implode('\\', $parts) . ';' . "\n\n";
        }

        $classTemplate->setVar(
            [
                'namespace'  => $namespace,
                'class_name' => $className,
                'wsdl'       => $wsdlFile,
                'options'    => $optionsBuffer,
                'methods'    => $methodsBuffer,
            ]
        );

        return $classTemplate->render();
    }

    /**
     * @throws ReflectionException
     *
     * @psalm-return list<string>
     */
    public function getClassMethods(string $className): array
    {
        try {
            $class = new ReflectionClass($className);
            // @codeCoverageIgnoreStart
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
        // @codeCoverageIgnoreEnd

        $methods = [];

        foreach ($class->getMethods() as $method) {
            if ($method->isPublic() || $method->isAbstract()) {
                $methods[] = $method->getName();
            }
        }

        return $methods;
    }

    /**
     * @throws ReflectionException
     *
     * @psalm-return list<MockMethod>
     */
    public function mockClassMethods(string $className, bool $callOriginalMethods, bool $cloneArguments): array
    {
        try {
            $class = new ReflectionClass($className);
            // @codeCoverageIgnoreStart
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
        // @codeCoverageIgnoreEnd

        $methods = [];

        foreach ($class->getMethods() as $method) {
            if (($method->isPublic() || $method->isAbstract()) && $this->canMethodBeDoubled($method)) {
                $methods[] = MockMethod::fromReflection($method, $callOriginalMethods, $cloneArguments);
            }
        }

        return $methods;
    }

    /**
     * @throws ReflectionException
     *
     * @psalm-return list<MockMethod>
     */
    public function mockInterfaceMethods(string $interfaceName, bool $cloneArguments): array
    {
        try {
            $class = new ReflectionClass($interfaceName);
            // @codeCoverageIgnoreStart
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
        // @codeCoverageIgnoreEnd

        $methods = [];

        foreach ($class->getMethods() as $method) {
            $methods[] = MockMethod::fromReflection($method, false, $cloneArguments);
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
        try {
            // @codeCoverageIgnoreStart
            $interface = new ReflectionClass($interfaceName);
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
        // @codeCoverageIgnoreEnd

        $methods = [];

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
    private function getObject(MockType $mockClass, string $type = '', bool $callOriginalConstructor = false, array $arguments = [], bool $callOriginalMethods = false, object $proxyTarget = null, bool $returnValueGeneration = true): object
    {
        $className = $mockClass->generate();
        $object    = $this->instantiate($className, $callOriginalConstructor, $arguments);

        if ($callOriginalMethods) {
            $this->instantiateProxyTarget($proxyTarget, $object, $type, $arguments);
        }

        if ($object instanceof MockObject) {
            $object->__phpunit_setReturnValueGeneration($returnValueGeneration);
        }

        return $object;
    }

    /**
     * @throws ClassIsEnumerationException
     * @throws ClassIsFinalException
     * @throws ClassIsReadonlyException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    private function generateMock(string $type, ?array $explicitMethods, string $mockClassName, bool $callOriginalClone, bool $callAutoload, bool $cloneArguments, bool $callOriginalMethods): MockClass
    {
        $classTemplate        = $this->loadTemplate('mocked_class.tpl');
        $additionalInterfaces = [];
        $mockedCloneMethod    = false;
        $unmockedCloneMethod  = false;
        $isClass              = false;
        $isInterface          = false;
        $class                = null;
        $mockMethods          = new MockMethodSet;

        $_mockClassName = $this->generateClassName(
            $type,
            $mockClassName,
            'Mock_'
        );

        if (class_exists($_mockClassName['fullClassName'], $callAutoload)) {
            $isClass = true;
        } elseif (interface_exists($_mockClassName['fullClassName'], $callAutoload)) {
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

            $mockedCloneMethod = true;
        } else {
            try {
                $class = new ReflectionClass($_mockClassName['fullClassName']);
                // @codeCoverageIgnoreStart
            } catch (\ReflectionException $e) {
                throw new ReflectionException(
                    $e->getMessage(),
                    $e->getCode(),
                    $e
                );
            }
            // @codeCoverageIgnoreEnd

            if ($class->isEnum()) {
                throw new ClassIsEnumerationException($_mockClassName['fullClassName']);
            }

            if ($class->isFinal()) {
                throw new ClassIsFinalException($_mockClassName['fullClassName']);
            }

            if (method_exists($class, 'isReadOnly') && $class->isReadOnly()) {
                throw new ClassIsReadonlyException($_mockClassName['fullClassName']);
            }

            // @see https://github.com/sebastianbergmann/phpunit/issues/2995
            if ($isInterface && $class->implementsInterface(Throwable::class)) {
                $actualClassName        = Exception::class;
                $additionalInterfaces[] = $class->getName();
                $isInterface            = false;

                try {
                    $class = new ReflectionClass($actualClassName);
                    // @codeCoverageIgnoreStart
                } catch (\ReflectionException $e) {
                    throw new ReflectionException(
                        $e->getMessage(),
                        $e->getCode(),
                        $e
                    );
                }
                // @codeCoverageIgnoreEnd

                foreach ($this->userDefinedInterfaceMethods($_mockClassName['fullClassName']) as $method) {
                    $methodName = $method->getName();

                    if ($class->hasMethod($methodName)) {
                        $classMethod = $class->getMethod($methodName);

                        if (!$this->canMethodBeDoubled($classMethod)) {
                            continue;
                        }
                    }

                    $mockMethods->addMethods(
                        MockMethod::fromReflection($method, $callOriginalMethods, $cloneArguments)
                    );
                }

                $_mockClassName = $this->generateClassName(
                    $actualClassName,
                    $_mockClassName['className'],
                    'Mock_'
                );
            }

            // @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/103
            if ($isInterface && $class->implementsInterface(Traversable::class) &&
                !$class->implementsInterface(Iterator::class) &&
                !$class->implementsInterface(IteratorAggregate::class)) {
                $additionalInterfaces[] = Iterator::class;

                $mockMethods->addMethods(
                    ...$this->mockClassMethods(Iterator::class, $callOriginalMethods, $cloneArguments)
                );
            }

            if ($class->hasMethod('__clone')) {
                $cloneMethod = $class->getMethod('__clone');

                if (!$cloneMethod->isFinal()) {
                    if ($callOriginalClone && !$isInterface) {
                        $unmockedCloneMethod = true;
                    } else {
                        $mockedCloneMethod = true;
                    }
                }
            } else {
                $mockedCloneMethod = true;
            }
        }

        if ($isClass && $explicitMethods === []) {
            $mockMethods->addMethods(
                ...$this->mockClassMethods($_mockClassName['fullClassName'], $callOriginalMethods, $cloneArguments)
            );
        }

        if ($isInterface && ($explicitMethods === [] || $explicitMethods === null)) {
            $mockMethods->addMethods(
                ...$this->mockInterfaceMethods($_mockClassName['fullClassName'], $cloneArguments)
            );
        }

        if (is_array($explicitMethods)) {
            foreach ($explicitMethods as $methodName) {
                if ($class !== null && $class->hasMethod($methodName)) {
                    $method = $class->getMethod($methodName);

                    if ($this->canMethodBeDoubled($method)) {
                        $mockMethods->addMethods(
                            MockMethod::fromReflection($method, $callOriginalMethods, $cloneArguments)
                        );
                    }
                } else {
                    $mockMethods->addMethods(
                        MockMethod::fromName(
                            $_mockClassName['fullClassName'],
                            $methodName,
                            $cloneArguments
                        )
                    );
                }
            }
        }

        $mockedMethods = '';
        $configurable  = [];

        foreach ($mockMethods->asArray() as $mockMethod) {
            $mockedMethods .= $mockMethod->generateCode();
            $configurable[] = new ConfigurableMethod($mockMethod->methodName(), $mockMethod->returnType());
        }

        $method = '';

        if (!$mockMethods->hasMethod('method') && (!isset($class) || !$class->hasMethod('method'))) {
            $method = PHP_EOL . '    use \PHPUnit\Framework\MockObject\Method;';
        }

        $cloneTrait = '';

        if ($mockedCloneMethod) {
            $cloneTrait = PHP_EOL . '    use \PHPUnit\Framework\MockObject\MockedCloneMethod;';
        }

        if ($unmockedCloneMethod) {
            $cloneTrait = PHP_EOL . '    use \PHPUnit\Framework\MockObject\UnmockedCloneMethod;';
        }

        $classTemplate->setVar(
            [
                'prologue'          => $prologue ?? '',
                'epilogue'          => $epilogue ?? '',
                'class_declaration' => $this->generateMockClassDeclaration(
                    $_mockClassName,
                    $isInterface,
                    $additionalInterfaces
                ),
                'clone'           => $cloneTrait,
                'mock_class_name' => $_mockClassName['className'],
                'mocked_methods'  => $mockedMethods,
                'method'          => $method,
            ]
        );

        return new MockClass(
            $classTemplate->render(),
            $_mockClassName['className'],
            $configurable
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

    private function generateMockClassDeclaration(array $mockClassName, bool $isInterface, array $additionalInterfaces = []): string
    {
        $buffer = 'class ';

        $additionalInterfaces[] = MockObject::class;
        $interfaces             = implode(', ', $additionalInterfaces);

        if ($isInterface) {
            $buffer .= sprintf(
                '%s implements %s',
                $mockClassName['className'],
                $interfaces
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
                $interfaces
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
    private function ensureKnownType(string $type, bool $callAutoload): void
    {
        if (!class_exists($type, $callAutoload) && !interface_exists($type, $callAutoload)) {
            throw new UnknownTypeException($type);
        }
    }

    /**
     * @throws DuplicateMethodException
     * @throws InvalidMethodNameException
     */
    private function ensureValidMethods(?array $methods): void
    {
        if (null !== $methods) {
            foreach ($methods as $method) {
                if (!preg_match('~[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*~', (string) $method)) {
                    throw new InvalidMethodNameException((string) $method);
                }
            }

            if ($methods !== array_unique($methods)) {
                throw new DuplicateMethodException($methods);
            }
        }
    }

    /**
     * @throws ClassAlreadyExistsException
     * @throws ReflectionException
     */
    private function ensureMockedClassDoesNotAlreadyExist(string $mockClassName): void
    {
        if ($mockClassName !== '' && class_exists($mockClassName, false)) {
            try {
                $reflector = new ReflectionClass($mockClassName);
                // @codeCoverageIgnoreStart
            } catch (\ReflectionException $e) {
                throw new ReflectionException(
                    $e->getMessage(),
                    $e->getCode(),
                    $e
                );
            }
            // @codeCoverageIgnoreEnd

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
                    $e
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
                $e
            );
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * @psalm-param class-string $type
     *
     * @throws ReflectionException
     */
    private function instantiateProxyTarget(?object $proxyTarget, object $object, string $type, array $arguments): void
    {
        if (!is_object($proxyTarget)) {
            assert(class_exists($type));

            if (count($arguments) === 0) {
                $proxyTarget = new $type;
            } else {
                $class = new ReflectionClass($type);

                try {
                    $proxyTarget = $class->newInstanceArgs($arguments);
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
        }

        $object->__phpunit_setOriginalObject($proxyTarget);
    }
}
