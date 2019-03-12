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

use Doctrine\Instantiator\Exception\ExceptionInterface as InstantiatorException;
use Doctrine\Instantiator\Instantiator;
use Iterator;
use IteratorAggregate;
use PHPUnit\Framework\Exception;
use PHPUnit\Util\InvalidArgumentHelper;
use ReflectionClass;
use ReflectionMethod;
use SoapClient;
use Text_Template;
use Traversable;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Generator
{
    /**
     * @var array
     */
    private const BLACKLISTED_METHOD_NAMES = [
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
     * @var Text_Template[]
     */
    private static $templates = [];

    /**
     * Returns a mock object for the specified class.
     *
     * @param string|string[] $type
     * @param array           $methods
     * @param string          $mockClassName
     * @param bool            $callOriginalConstructor
     * @param bool            $callOriginalClone
     * @param bool            $callAutoload
     * @param bool            $cloneArguments
     * @param bool            $callOriginalMethods
     * @param object          $proxyTarget
     * @param bool            $allowMockingUnknownTypes
     * @param bool            $returnValueGeneration
     *
     * @throws Exception
     * @throws RuntimeException
     * @throws \PHPUnit\Framework\Exception
     * @throws \ReflectionException
     */
    public function getMock($type, $methods = [], array $arguments = [], $mockClassName = '', $callOriginalConstructor = true, $callOriginalClone = true, $callAutoload = true, $cloneArguments = true, $callOriginalMethods = false, $proxyTarget = null, $allowMockingUnknownTypes = true, $returnValueGeneration = true): MockObject
    {
        if (!\is_array($type) && !\is_string($type)) {
            throw InvalidArgumentHelper::factory(1, 'array or string');
        }

        if (!\is_string($mockClassName)) {
            throw InvalidArgumentHelper::factory(4, 'string');
        }

        if (!\is_array($methods) && null !== $methods) {
            throw InvalidArgumentHelper::factory(2, 'array', $methods);
        }

        if ($type === 'Traversable' || $type === '\\Traversable') {
            $type = 'Iterator';
        }

        if (\is_array($type)) {
            $type = \array_unique(
                \array_map(
                    function ($type) {
                        if ($type === 'Traversable' ||
                            $type === '\\Traversable' ||
                            $type === '\\Iterator') {
                            return 'Iterator';
                        }

                        return $type;
                    },
                    $type
                )
            );
        }

        if (!$allowMockingUnknownTypes) {
            if (\is_array($type)) {
                foreach ($type as $_type) {
                    if (!\class_exists($_type, $callAutoload) &&
                        !\interface_exists($_type, $callAutoload)) {
                        throw new RuntimeException(
                            \sprintf(
                                'Cannot stub or mock class or interface "%s" which does not exist',
                                $_type
                            )
                        );
                    }
                }
            } elseif (!\class_exists($type, $callAutoload) &&
                !\interface_exists($type, $callAutoload)
            ) {
                throw new RuntimeException(
                    \sprintf(
                        'Cannot stub or mock class or interface "%s" which does not exist',
                        $type
                    )
                );
            }
        }

        if (null !== $methods) {
            foreach ($methods as $method) {
                if (!\preg_match('~[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*~', (string) $method)) {
                    throw new RuntimeException(
                        \sprintf(
                            'Cannot stub or mock method with invalid name "%s"',
                            $method
                        )
                    );
                }
            }

            if ($methods !== \array_unique($methods)) {
                throw new RuntimeException(
                    \sprintf(
                        'Cannot stub or mock using a method list that contains duplicates: "%s" (duplicate: "%s")',
                        \implode(', ', $methods),
                        \implode(', ', \array_unique(\array_diff_assoc($methods, \array_unique($methods))))
                    )
                );
            }
        }

        if ($mockClassName !== '' && \class_exists($mockClassName, false)) {
            $reflect = new ReflectionClass($mockClassName);

            if (!$reflect->implementsInterface(MockObject::class)) {
                throw new RuntimeException(
                    \sprintf(
                        'Class "%s" already exists.',
                        $mockClassName
                    )
                );
            }
        }

        if (!$callOriginalConstructor && $callOriginalMethods) {
            throw new RuntimeException(
                'Proxying to original methods requires invoking the original constructor'
            );
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
            $mock['code'],
            $mock['mockClassName'],
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
     * methods of the class mocked. Concrete methods to mock can be specified with
     * the last parameter
     *
     * @param string $originalClassName
     * @param string $mockClassName
     * @param bool   $callOriginalConstructor
     * @param bool   $callOriginalClone
     * @param bool   $callAutoload
     * @param array  $mockedMethods
     * @param bool   $cloneArguments
     *
     * @throws \ReflectionException
     * @throws RuntimeException
     * @throws Exception
     */
    public function getMockForAbstractClass($originalClassName, array $arguments = [], $mockClassName = '', $callOriginalConstructor = true, $callOriginalClone = true, $callAutoload = true, $mockedMethods = [], $cloneArguments = true): MockObject
    {
        if (!\is_string($originalClassName)) {
            throw InvalidArgumentHelper::factory(1, 'string');
        }

        if (!\is_string($mockClassName)) {
            throw InvalidArgumentHelper::factory(3, 'string');
        }

        if (\class_exists($originalClassName, $callAutoload) ||
            \interface_exists($originalClassName, $callAutoload)) {
            $reflector = new ReflectionClass($originalClassName);
            $methods   = $mockedMethods;

            foreach ($reflector->getMethods() as $method) {
                if ($method->isAbstract() && !\in_array($method->getName(), $methods, true)) {
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

        throw new RuntimeException(
            \sprintf('Class "%s" does not exist.', $originalClassName)
        );
    }

    /**
     * Returns a mock object for the specified trait with all abstract methods
     * of the trait mocked. Concrete methods to mock can be specified with the
     * `$mockedMethods` parameter.
     *
     * @param string $traitName
     * @param string $mockClassName
     * @param bool   $callOriginalConstructor
     * @param bool   $callOriginalClone
     * @param bool   $callAutoload
     * @param array  $mockedMethods
     * @param bool   $cloneArguments
     *
     * @throws \ReflectionException
     * @throws RuntimeException
     * @throws Exception
     */
    public function getMockForTrait($traitName, array $arguments = [], $mockClassName = '', $callOriginalConstructor = true, $callOriginalClone = true, $callAutoload = true, $mockedMethods = [], $cloneArguments = true): MockObject
    {
        if (!\is_string($traitName)) {
            throw InvalidArgumentHelper::factory(1, 'string');
        }

        if (!\is_string($mockClassName)) {
            throw InvalidArgumentHelper::factory(3, 'string');
        }

        if (!\trait_exists($traitName, $callAutoload)) {
            throw new RuntimeException(
                \sprintf(
                    'Trait "%s" does not exist.',
                    $traitName
                )
            );
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

        $this->evalClass(
            $classTemplate->render(),
            $className['className']
        );

        return $this->getMockForAbstractClass($className['className'], $arguments, $mockClassName, $callOriginalConstructor, $callOriginalClone, $callAutoload, $mockedMethods, $cloneArguments);
    }

    /**
     * Returns an object for the specified trait.
     *
     * @param string $traitName
     * @param string $traitClassName
     * @param bool   $callOriginalConstructor
     * @param bool   $callOriginalClone
     * @param bool   $callAutoload
     *
     * @throws \ReflectionException
     * @throws RuntimeException
     * @throws Exception
     *
     * @return object
     */
    public function getObjectForTrait($traitName, array $arguments = [], $traitClassName = '', $callOriginalConstructor = true, $callOriginalClone = true, $callAutoload = true)
    {
        if (!\is_string($traitName)) {
            throw InvalidArgumentHelper::factory(1, 'string');
        }

        if (!\is_string($traitClassName)) {
            throw InvalidArgumentHelper::factory(3, 'string');
        }

        if (!\trait_exists($traitName, $callAutoload)) {
            throw new RuntimeException(
                \sprintf(
                    'Trait "%s" does not exist.',
                    $traitName
                )
            );
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

        return $this->getObject($classTemplate->render(), $className['className']);
    }

    /**
     * @param array|string $type
     * @param array        $methods
     * @param string       $mockClassName
     * @param bool         $callOriginalClone
     * @param bool         $callAutoload
     * @param bool         $cloneArguments
     * @param bool         $callOriginalMethods
     *
     * @throws \ReflectionException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     */
    public function generate($type, array $methods = null, $mockClassName = '', $callOriginalClone = true, $callAutoload = true, $cloneArguments = true, $callOriginalMethods = false): array
    {
        if (\is_array($type)) {
            \sort($type);
        }

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
        $key = \md5(
            \is_array($type) ? \implode('_', $type) : $type .
            \serialize($methods) .
            \serialize($callOriginalClone) .
            \serialize($cloneArguments) .
            \serialize($callOriginalMethods)
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
     * @param string $wsdlFile
     * @param string $className
     *
     * @throws RuntimeException
     */
    public function generateClassFromWsdl($wsdlFile, $className, array $methods = [], array $options = []): string
    {
        if (!\extension_loaded('soap')) {
            throw new RuntimeException(
                'The SOAP extension is required to generate a mock object from WSDL.'
            );
        }

        $options  = \array_merge($options, ['cache_wsdl' => \WSDL_CACHE_NONE]);
        $client   = new SoapClient($wsdlFile, $options);
        $_methods = \array_unique($client->__getFunctions());
        unset($client);

        \sort($_methods);

        $methodTemplate = $this->getTemplate('wsdl_method.tpl');
        $methodsBuffer  = '';

        foreach ($_methods as $method) {
            \preg_match_all('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*\(/', $method, $matches, \PREG_OFFSET_CAPTURE);
            $lastFunction = \array_pop($matches[0]);
            $nameStart    = $lastFunction[1];
            $nameEnd      = $nameStart + \strlen($lastFunction[0]) - 1;
            $name         = \str_replace('(', '', $lastFunction[0]);

            if (empty($methods) || \in_array($name, $methods, true)) {
                $args = \explode(
                    ',',
                    \str_replace(')', '', \substr($method, $nameEnd + 1))
                );

                foreach (\range(0, \count($args) - 1) as $i) {
                    $args[$i] = \substr($args[$i], \strpos($args[$i], '$'));
                }

                $methodTemplate->setVar(
                    [
                        'method_name' => $name,
                        'arguments'   => \implode(', ', $args),
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

        if (\strpos($className, '\\') !== false) {
            $parts     = \explode('\\', $className);
            $className = \array_pop($parts);
            $namespace = 'namespace ' . \implode('\\', $parts) . ';' . "\n\n";
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
     * @param string $className
     *
     * @throws \ReflectionException
     *
     * @return string[]
     */
    public function getClassMethods($className): array
    {
        $class   = new ReflectionClass($className);
        $methods = [];

        foreach ($class->getMethods() as $method) {
            if ($method->isPublic() || $method->isAbstract()) {
                $methods[] = $method->getName();
            }
        }

        return $methods;
    }

    /**
     * @throws \ReflectionException
     * @throws RuntimeException
     *
     * @return MockMethod[]
     */
    public function mockClassMethods(string $className, bool $callOriginalMethods, bool $cloneArguments): array
    {
        $class   = new ReflectionClass($className);
        $methods = [];

        foreach ($class->getMethods() as $method) {
            if (($method->isPublic() || $method->isAbstract()) && $this->canMockMethod($method)) {
                $methods[] = MockMethod::fromReflection($method, $callOriginalMethods, $cloneArguments);
            }
        }

        return $methods;
    }

    /**
     * @param string       $code
     * @param string       $className
     * @param array|string $type
     * @param bool         $callOriginalConstructor
     * @param bool         $callAutoload
     * @param bool         $callOriginalMethods
     * @param object       $proxyTarget
     * @param bool         $returnValueGeneration
     *
     * @throws \ReflectionException
     * @throws RuntimeException
     */
    private function getObject($code, $className, $type = '', $callOriginalConstructor = false, $callAutoload = false, array $arguments = [], $callOriginalMethods = false, $proxyTarget = null, $returnValueGeneration = true)
    {
        $this->evalClass($code, $className);

        if ($callOriginalConstructor &&
            \is_string($type) &&
            !\interface_exists($type, $callAutoload)) {
            if (\count($arguments) === 0) {
                $object = new $className;
            } else {
                $class  = new ReflectionClass($className);
                $object = $class->newInstanceArgs($arguments);
            }
        } else {
            try {
                $object       = (new Instantiator)->instantiate($className);
            } catch (InstantiatorException $exception) {
                throw new RuntimeException($exception->getMessage());
            }
        }

        if ($callOriginalMethods) {
            if (!\is_object($proxyTarget)) {
                if (\count($arguments) === 0) {
                    $proxyTarget = new $type;
                } else {
                    $class       = new ReflectionClass($type);
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
     * @param string $code
     * @param string $className
     */
    private function evalClass($code, $className): void
    {
        if (!\class_exists($className, false)) {
            eval($code);
        }
    }

    /**
     * @param array|string $type
     * @param null|array   $explicitMethods
     * @param string       $mockClassName
     * @param bool         $callOriginalClone
     * @param bool         $callAutoload
     * @param bool         $cloneArguments
     * @param bool         $callOriginalMethods
     *
     * @throws \ReflectionException
     * @throws RuntimeException
     */
    private function generateMock($type, $explicitMethods, $mockClassName, $callOriginalClone, $callAutoload, $cloneArguments, $callOriginalMethods): array
    {
        $classTemplate       = $this->getTemplate('mocked_class.tpl');

        $additionalInterfaces = [];
        $cloneTemplate        = '';
        $isClass              = false;
        $isInterface          = false;
        $class                = null;
        $mockMethods          = new MockMethodSet;

        if (\is_array($type)) {
            $interfaceMethods = [];

            foreach ($type as $_type) {
                if (!\interface_exists($_type, $callAutoload)) {
                    throw new RuntimeException(
                        \sprintf(
                            'Interface "%s" does not exist.',
                            $_type
                        )
                    );
                }

                $additionalInterfaces[] = $_type;
                $typeClass              = new ReflectionClass($_type);

                foreach ($this->getClassMethods($_type) as $method) {
                    if (\in_array($method, $interfaceMethods, true)) {
                        throw new RuntimeException(
                            \sprintf(
                                'Duplicate method "%s" not allowed.',
                                $method
                            )
                        );
                    }

                    $methodReflection = $typeClass->getMethod($method);

                    if ($this->canMockMethod($methodReflection)) {
                        $mockMethods->addMethods(
                            MockMethod::fromReflection($methodReflection, $callOriginalMethods, $cloneArguments)
                        );

                        $interfaceMethods[] = $method;
                    }
                }
            }

            unset($interfaceMethods);
        }

        $mockClassName = $this->generateClassName(
            $type,
            $mockClassName,
            'Mock_'
        );

        if (\class_exists($mockClassName['fullClassName'], $callAutoload)) {
            $isClass = true;
        } elseif (\interface_exists($mockClassName['fullClassName'], $callAutoload)) {
            $isInterface = true;
        }

        if (!$isClass && !$isInterface) {
            $prologue = 'class ' . $mockClassName['originalClassName'] . "\n{\n}\n\n";

            if (!empty($mockClassName['namespaceName'])) {
                $prologue = 'namespace ' . $mockClassName['namespaceName'] .
                            " {\n\n" . $prologue . "}\n\n" .
                            "namespace {\n\n";

                $epilogue = "\n\n}";
            }

            $cloneTemplate = $this->getTemplate('mocked_clone.tpl');
        } else {
            $class = new ReflectionClass($mockClassName['fullClassName']);

            if ($class->isFinal()) {
                throw new RuntimeException(
                    \sprintf(
                        'Class "%s" is declared "final" and cannot be mocked.',
                        $mockClassName['fullClassName']
                    )
                );
            }

            // @see https://github.com/sebastianbergmann/phpunit/issues/2995
            if ($isInterface && $class->implementsInterface(\Throwable::class)) {
                $additionalInterfaces[] = $class->getName();
                $isInterface            = false;

                $mockClassName = $this->generateClassName(
                    \Exception::class,
                    '',
                    'Mock_'
                );

                $class = new ReflectionClass($mockClassName['fullClassName']);
            }

            // https://github.com/sebastianbergmann/phpunit-mock-objects/issues/103
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
                        $cloneTemplate = $this->getTemplate('unmocked_clone.tpl');
                    } else {
                        $cloneTemplate = $this->getTemplate('mocked_clone.tpl');
                    }
                }
            } else {
                $cloneTemplate = $this->getTemplate('mocked_clone.tpl');
            }
        }

        if (\is_object($cloneTemplate)) {
            $cloneTemplate = $cloneTemplate->render();
        }

        if ($explicitMethods === [] &&
            ($isClass || $isInterface)) {
            $mockMethods->addMethods(
                ...$this->mockClassMethods($mockClassName['fullClassName'], $callOriginalMethods, $cloneArguments)
            );
        }

        if (\is_array($explicitMethods)) {
            foreach ($explicitMethods as $methodName) {
                if ($class !== null && $class->hasMethod($methodName)) {
                    $method = $class->getMethod($methodName);

                    if ($this->canMockMethod($method)) {
                        $mockMethods->addMethods(
                            MockMethod::fromReflection($method, $callOriginalMethods, $cloneArguments)
                        );
                    }
                } else {
                    $mockMethods->addMethods(
                        MockMethod::fromName(
                            $mockClassName['fullClassName'],
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
            $configurable[] = \strtolower($mockMethod->getName());
        }

        $method = '';

        if (!$mockMethods->hasMethod('method') && (!isset($class) || !$class->hasMethod('method'))) {
            $method = $this->getTemplate('mocked_class_method.tpl')->render();
        }

        $classTemplate->setVar(
            [
                'prologue'          => $prologue ?? '',
                'epilogue'          => $epilogue ?? '',
                'class_declaration' => $this->generateMockClassDeclaration(
                    $mockClassName,
                    $isInterface,
                    $additionalInterfaces
                ),
                'clone'             => $cloneTemplate,
                'mock_class_name'   => $mockClassName['className'],
                'mocked_methods'    => $mockedMethods,
                'method'            => $method,
                'configurable'      => '[' . \implode(
                    ', ',
                    \array_map(
                        function ($m) {
                            return '\'' . $m . '\'';
                        },
                        $configurable
                    )
                ) . ']',
            ]
        );

        return [
            'code'          => $classTemplate->render(),
            'mockClassName' => $mockClassName['className'],
        ];
    }

    /**
     * @param array|string $type
     * @param string       $className
     * @param string       $prefix
     */
    private function generateClassName($type, $className, $prefix): array
    {
        if (\is_array($type)) {
            $type = \implode('_', $type);
        }

        if ($type[0] === '\\') {
            $type = \substr($type, 1);
        }

        $classNameParts = \explode('\\', $type);

        if (\count($classNameParts) > 1) {
            $type          = \array_pop($classNameParts);
            $namespaceName = \implode('\\', $classNameParts);
            $fullClassName = $namespaceName . '\\' . $type;
        } else {
            $namespaceName = '';
            $fullClassName = $type;
        }

        if ($className === '') {
            do {
                $className = $prefix . $type . '_' .
                             \substr(\md5((string) \mt_rand()), 0, 8);
            } while (\class_exists($className, false));
        }

        return [
            'className'         => $className,
            'originalClassName' => $type,
            'fullClassName'     => $fullClassName,
            'namespaceName'     => $namespaceName,
        ];
    }

    /**
     * @param bool $isInterface
     */
    private function generateMockClassDeclaration(array $mockClassName, $isInterface, array $additionalInterfaces = []): string
    {
        $buffer = 'class ';

        $additionalInterfaces[] = MockObject::class;
        $interfaces             = \implode(', ', $additionalInterfaces);

        if ($isInterface) {
            $buffer .= \sprintf(
                '%s implements %s',
                $mockClassName['className'],
                $interfaces
            );

            if (!\in_array($mockClassName['originalClassName'], $additionalInterfaces)) {
                $buffer .= ', ';

                if (!empty($mockClassName['namespaceName'])) {
                    $buffer .= $mockClassName['namespaceName'] . '\\';
                }

                $buffer .= $mockClassName['originalClassName'];
            }
        } else {
            $buffer .= \sprintf(
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
        return !($method->isConstructor() || $method->isFinal() || $method->isPrivate() || $this->isMethodNameBlacklisted($method->getName()));
    }

    /**
     * Returns whether a method name is blacklisted
     *
     * @param string $name
     */
    private function isMethodNameBlacklisted($name): bool
    {
        return isset(self::BLACKLISTED_METHOD_NAMES[$name]);
    }

    /**
     * @param string $template
     */
    private function getTemplate($template): Text_Template
    {
        $filename = __DIR__ . \DIRECTORY_SEPARATOR . 'Generator' . \DIRECTORY_SEPARATOR . $template;

        if (!isset(self::$templates[$filename])) {
            self::$templates[$filename] = new Text_Template($filename);
        }

        return self::$templates[$filename];
    }
}
