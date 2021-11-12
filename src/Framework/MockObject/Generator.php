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

use const DIRECTORY_SEPARATOR;
use const PHP_EOL;
use const PHP_MAJOR_VERSION;
use const PREG_OFFSET_CAPTURE;
use const WSDL_CACHE_NONE;
use function array_merge;
use function array_pop;
use function array_unique;
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
use function mt_rand;
use function preg_match;
use function preg_match_all;
use function range;
use function serialize;
use function sort;
use function sprintf;
use function str_replace;
use function strlen;
use function strpos;
use function strtolower;
use function substr;
use function trait_exists;
use Doctrine\Instantiator\Exception\ExceptionInterface as InstantiatorException;
use Doctrine\Instantiator\Instantiator;
use Exception;
use Iterator;
use IteratorAggregate;
use PHPUnit\Framework\InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;
use SebastianBergmann\Template\Exception as TemplateException;
use SebastianBergmann\Template\Template;
use SoapClient;
use SoapFault;
use Throwable;
use Traversable;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Generator
{
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
     * @var array
     */
    private static $cache = [];

    /**
     * @var Template[]
     */
    private static $templates = [];

    /**
     * Returns a mock object for the specified class.
     *
     * @param null|array $methods
     *
     * @throws \PHPUnit\Framework\InvalidArgumentException
     * @throws ClassAlreadyExistsException
     * @throws ClassIsFinalException
     * @throws DuplicateMethodException
     * @throws Exception
     * @throws InvalidMethodNameException
     * @throws OriginalConstructorInvocationRequiredException
     * @throws ReflectionException
     * @throws RuntimeException
     * @throws UnknownTypeException
     */
    public function getMock(string $type, $methods = [], array $arguments = [], string $mockClassName = '', bool $callOriginalConstructor = true, bool $callOriginalClone = true, bool $callAutoload = true, bool $cloneArguments = true, bool $callOriginalMethods = false, object $proxyTarget = null, bool $allowMockingUnknownTypes = true, bool $returnValueGeneration = true): MockObject
    {
        if (!is_array($methods) && null !== $methods) {
            throw InvalidArgumentException::create(2, 'array');
        }

        if ($type === 'Traversable' || $type === '\\Traversable') {
            $type = 'Iterator';
        }

        if (!$allowMockingUnknownTypes && !class_exists($type, $callAutoload) && !interface_exists($type, $callAutoload)) {
            throw new UnknownTypeException($type);
        }

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

        if ($mockClassName !== '' && class_exists($mockClassName, false)) {
            try {
                $reflector = new ReflectionClass($mockClassName);
                // @codeCoverageIgnoreStart
            } catch (\ReflectionException $e) {
                throw new ReflectionException(
                    $e->getMessage(),
                    (int) $e->getCode(),
                    $e
                );
            }
            // @codeCoverageIgnoreEnd

            if (!$reflector->implementsInterface(MockObject::class)) {
                throw new ClassAlreadyExistsException($mockClassName);
            }
        }

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

        return $this->getObject(
            $mock,
            $type,
            $callOriginalConstructor,
            $callAutoload,
            $arguments,
            $callOriginalMethods,
            $proxyTarget,
            $returnValueGeneration
        );
    }

    /**
     * Returns a mock object for the specified abstract class with all abstract
     * methods of the class mocked.
     *
     * Concrete methods to mock can be specified with the $mockedMethods parameter.
     *
     * @psalm-template RealInstanceType of object
     * @psalm-param class-string<RealInstanceType> $originalClassName
     * @psalm-return MockObject&RealInstanceType
     *
     * @throws \PHPUnit\Framework\InvalidArgumentException
     * @throws ClassAlreadyExistsException
     * @throws ClassIsFinalException
     * @throws DuplicateMethodException
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
                    (int) $e->getCode(),
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
     * @throws \PHPUnit\Framework\InvalidArgumentException
     * @throws ClassAlreadyExistsException
     * @throws ClassIsFinalException
     * @throws DuplicateMethodException
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

        $classTemplate = $this->getTemplate('trait_class.tpl');

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

        $classTemplate = $this->getTemplate('trait_class.tpl');

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
            $callAutoload,
            $arguments
        );
    }

    /**
     * @throws ClassIsFinalException
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

        $options = array_merge($options, ['cache_wsdl' => WSDL_CACHE_NONE]);

        try {
            $client   = new SoapClient($wsdlFile, $options);
            $_methods = array_unique($client->__getFunctions());
            unset($client);
        } catch (SoapFault $e) {
            throw new RuntimeException(  // @psalm-suppress InvalidArgument
                $e->getMessage(),
                (int) $e->getCode()
            );
        }

        sort($_methods);

        $methodTemplate = $this->getTemplate('wsdl_method.tpl');
        $methodsBuffer  = '';

        foreach ($_methods as $method) {
            preg_match_all('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*\(/', $method, $matches, PREG_OFFSET_CAPTURE);
            $lastFunction = array_pop($matches[0]);
            $nameStart    = $lastFunction[1];
            $nameEnd      = $nameStart + strlen($lastFunction[0]) - 1;
            $name         = str_replace('(', '', $lastFunction[0]);

            if (empty($methods) || in_array($name, $methods, true)) {
                $args = explode(
                    ',',
                    str_replace(')', '', substr($method, $nameEnd + 1))
                );

                foreach (range(0, count($args) - 1) as $i) {
                    $parameterStart = strpos($args[$i], '$');

                    if (!$parameterStart) {
                        continue;
                    }

                    $args[$i] = substr($args[$i], $parameterStart);
                }

                $methodTemplate->setVar(
                    [
                        'method_name' => $name,
                        'arguments'   => implode(', ', $args),
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

        $classTemplate = $this->getTemplate('wsdl_class.tpl');
        $namespace     = '';

        if (strpos($className, '\\') !== false) {
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
     * @return string[]
     */
    public function getClassMethods(string $className): array
    {
        try {
            $class = new ReflectionClass($className);
            // @codeCoverageIgnoreStart
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                $e->getMessage(),
                (int) $e->getCode(),
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
     * @return MockMethod[]
     */
    public function mockClassMethods(string $className, bool $callOriginalMethods, bool $cloneArguments): array
    {
        try {
            $class = new ReflectionClass($className);
            // @codeCoverageIgnoreStart
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }
        // @codeCoverageIgnoreEnd

        $methods = [];

        foreach ($class->getMethods() as $method) {
            if (($method->isPublic() || $method->isAbstract()) && $this->canMockMethod($method)) {
                $methods[] = MockMethod::fromReflection($method, $callOriginalMethods, $cloneArguments);
            }
        }

        return $methods;
    }

    /**
     * @throws ReflectionException
     *
     * @return MockMethod[]
     */
    public function mockInterfaceMethods(string $interfaceName, bool $cloneArguments): array
    {
        try {
            $class = new ReflectionClass($interfaceName);
            // @codeCoverageIgnoreStart
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                $e->getMessage(),
                (int) $e->getCode(),
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
     * @return ReflectionMethod[]
     */
    private function userDefinedInterfaceMethods(string $interfaceName): array
    {
        try {
            // @codeCoverageIgnoreStart
            $interface = new ReflectionClass($interfaceName);
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                $e->getMessage(),
                (int) $e->getCode(),
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
    private function getObject(MockType $mockClass, $type = '', bool $callOriginalConstructor = false, bool $callAutoload = false, array $arguments = [], bool $callOriginalMethods = false, object $proxyTarget = null, bool $returnValueGeneration = true)
    {
        $className = $mockClass->generate();

        if ($callOriginalConstructor) {
            if (count($arguments) === 0) {
                $object = new $className;
            } else {
                try {
                    $class = new ReflectionClass($className);
                    // @codeCoverageIgnoreStart
                } catch (\ReflectionException $e) {
                    throw new ReflectionException(
                        $e->getMessage(),
                        (int) $e->getCode(),
                        $e
                    );
                }
                // @codeCoverageIgnoreEnd

                $object = $class->newInstanceArgs($arguments);
            }
        } else {
            try {
                $object = (new Instantiator)->instantiate($className);
            } catch (InstantiatorException $e) {
                throw new RuntimeException($e->getMessage());
            }
        }

        if ($callOriginalMethods) {
            if (!is_object($proxyTarget)) {
                if (count($arguments) === 0) {
                    $proxyTarget = new $type;
                } else {
                    try {
                        $class = new ReflectionClass($type);
                        // @codeCoverageIgnoreStart
                    } catch (\ReflectionException $e) {
                        throw new ReflectionException(
                            $e->getMessage(),
                            (int) $e->getCode(),
                            $e
                        );
                    }
                    // @codeCoverageIgnoreEnd

                    $proxyTarget = $class->newInstanceArgs($arguments);
                }
            }

            $object->__phpunit_setOriginalObject($proxyTarget);
        }

        if ($object instanceof MockObject) {
            $object->__phpunit_setReturnValueGeneration($returnValueGeneration);
        }

        return $object;
    }

    /**
     * @throws ClassIsFinalException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    private function generateMock(string $type, ?array $explicitMethods, string $mockClassName, bool $callOriginalClone, bool $callAutoload, bool $cloneArguments, bool $callOriginalMethods): MockClass
    {
        $classTemplate        = $this->getTemplate('mocked_class.tpl');
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
                    (int) $e->getCode(),
                    $e
                );
            }
            // @codeCoverageIgnoreEnd

            if ($class->isFinal()) {
                throw new ClassIsFinalException($_mockClassName['fullClassName']);
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
                        (int) $e->getCode(),
                        $e
                    );
                }
                // @codeCoverageIgnoreEnd

                foreach ($this->userDefinedInterfaceMethods($_mockClassName['fullClassName']) as $method) {
                    $methodName = $method->getName();

                    if ($class->hasMethod($methodName)) {
                        try {
                            $classMethod = $class->getMethod($methodName);
                            // @codeCoverageIgnoreStart
                        } catch (\ReflectionException $e) {
                            throw new ReflectionException(
                                $e->getMessage(),
                                (int) $e->getCode(),
                                $e
                            );
                        }
                        // @codeCoverageIgnoreEnd

                        if (!$this->canMockMethod($classMethod)) {
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
                try {
                    $cloneMethod = $class->getMethod('__clone');
                    // @codeCoverageIgnoreStart
                } catch (\ReflectionException $e) {
                    throw new ReflectionException(
                        $e->getMessage(),
                        (int) $e->getCode(),
                        $e
                    );
                }
                // @codeCoverageIgnoreEnd

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
                    try {
                        $method = $class->getMethod($methodName);
                        // @codeCoverageIgnoreStart
                    } catch (\ReflectionException $e) {
                        throw new ReflectionException(
                            $e->getMessage(),
                            (int) $e->getCode(),
                            $e
                        );
                    }
                    // @codeCoverageIgnoreEnd

                    if ($this->canMockMethod($method)) {
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
            $configurable[] = new ConfigurableMethod($mockMethod->getName(), $mockMethod->getReturnType());
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

    private function canMockMethod(ReflectionMethod $method): bool
    {
        return !($this->isConstructor($method) || $method->isFinal() || $method->isPrivate() || $this->isMethodNameExcluded($method->getName()));
    }

    private function isMethodNameExcluded(string $name): bool
    {
        return isset(self::EXCLUDED_METHOD_NAMES[$name]);
    }

    /**
     * @throws RuntimeException
     */
    private function getTemplate(string $template): Template
    {
        $filename = __DIR__ . DIRECTORY_SEPARATOR . 'Generator' . DIRECTORY_SEPARATOR . $template;

        if (!isset(self::$templates[$filename])) {
            try {
                self::$templates[$filename] = new Template($filename);
            } catch (TemplateException $e) {
                throw new RuntimeException(
                    $e->getMessage(),
                    (int) $e->getCode(),
                    $e
                );
            }
        }

        return self::$templates[$filename];
    }

    /**
     * @see https://github.com/sebastianbergmann/phpunit/issues/4139#issuecomment-605409765
     */
    private function isConstructor(ReflectionMethod $method): bool
    {
        $methodName = strtolower($method->getName());

        if ($methodName === '__construct') {
            return true;
        }

        if (PHP_MAJOR_VERSION >= 8) {
            return false;
        }

        $className = strtolower($method->getDeclaringClass()->getName());

        return $methodName === $className;
    }
}
