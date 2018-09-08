<?php
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
 * Mock Object Code Generator
 */
class Generator
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
     *
     * @return MockObject
     */
    public function getMock($type, $methods = [], array $arguments = [], $mockClassName = '', $callOriginalConstructor = true, $callOriginalClone = true, $callAutoload = true, $cloneArguments = true, $callOriginalMethods = false, $proxyTarget = null, $allowMockingUnknownTypes = true, $returnValueGeneration = true)
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
            } else {
                if (!\class_exists($type, $callAutoload) &&
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
        }

        if (null !== $methods) {
            foreach ($methods as $method) {
                if (!\preg_match('~[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*~', $method)) {
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

        if ($callOriginalConstructor === false && $callOriginalMethods === true) {
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
     * @param string   $originalClassName
     * @param string[] $arguments
     * @param string   $mockClassName
     * @param bool     $callOriginalConstructor
     * @param bool     $callOriginalClone
     * @param bool     $callAutoload
     * @param array    $mockedMethods
     * @param bool     $cloneArguments
     *
     * @throws \ReflectionException
     * @throws RuntimeException
     * @throws Exception
     *
     * @return MockObject
     */
    public function getMockForAbstractClass($originalClassName, array $arguments = [], $mockClassName = '', $callOriginalConstructor = true, $callOriginalClone = true, $callAutoload = true, $mockedMethods = [], $cloneArguments = true)
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
     * @param string   $traitName
     * @param string[] $arguments
     * @param string   $mockClassName
     * @param bool     $callOriginalConstructor
     * @param bool     $callOriginalClone
     * @param bool     $callAutoload
     * @param array    $mockedMethods
     * @param bool     $cloneArguments
     *
     * @throws \ReflectionException
     * @throws RuntimeException
     * @throws Exception
     *
     * @return MockObject
     */
    public function getMockForTrait($traitName, array $arguments = [], $mockClassName = '', $callOriginalConstructor = true, $callOriginalClone = true, $callAutoload = true, $mockedMethods = [], $cloneArguments = true)
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
            TypeName::fromQualifiedName($traitName),
            'Trait_'
        );

        $classTemplate = $this->getTemplate('trait_class.tpl');

        $classTemplate->setVar(
            [
                'prologue'   => 'abstract ',
                'class_name' => $className->getQualifiedName(),
                'trait_name' => $traitName,
            ]
        );

        $this->evalClass(
            $classTemplate->render(),
            $className->getQualifiedName()
        );

        return $this->getMockForAbstractClass(
            $className->getQualifiedName(),
            $arguments,
            $mockClassName,
            $callOriginalConstructor,
            $callOriginalClone,
            $callAutoload,
            $mockedMethods,
            $cloneArguments
        );
    }

    /**
     * Returns an object for the specified trait.
     *
     * @param string   $traitName
     * @param string[] $arguments
     * @param string   $traitClassName
     * @param bool     $callOriginalConstructor
     * @param bool     $callOriginalClone
     * @param bool     $callAutoload
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

        if ('' === $traitClassName) {
            $className = $this->generateClassName(
                TypeName::fromQualifiedName($traitName),
                'Trait_'
            );
        } else {
            $className = TypeName::fromQualifiedName($traitClassName);
        }

        $classTemplate = $this->getTemplate('trait_class.tpl');

        $classTemplate->setVar(
            [
                'prologue'   => '',
                'class_name' => $className->getQualifiedName(),
                'trait_name' => $traitName,
            ]
        );

        return $this->getObject($classTemplate->render(), $className->getQualifiedName());
    }

    /**
     * @param string|string[] $type
     * @param null|string[]   $methods
     * @param string          $mockClassName
     * @param bool            $callOriginalClone
     * @param bool            $callAutoload
     * @param bool            $cloneArguments
     * @param bool            $callOriginalMethods
     *
     * @throws \ReflectionException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     *
     * @return array
     */
    public function generate($type, array $methods = null, $mockClassName = '', $callOriginalClone = true, $callAutoload = true, $cloneArguments = true, $callOriginalMethods = false)
    {
        if (\is_array($type)) {
            \sort($type);
        }

        if ($mockClassName !== '') {
            $mockClass = $this->generateMock(
                $type,
                $methods,
                TypeName::fromQualifiedName($mockClassName),
                $callOriginalClone,
                $callAutoload,
                $cloneArguments,
                $callOriginalMethods
            );

            return [
                'code'          => $mockClass->generateCode(),
                'mockClassName' => $mockClass->getClassName()->getQualifiedName(),
            ];
        }
        $key = \md5(
            \is_array($type) ? \implode('_', $type) : $type .
            \serialize($methods) .
            \serialize($callOriginalClone) .
            \serialize($cloneArguments) .
            \serialize($callOriginalMethods)
        );

        if (!isset(self::$cache[$key])) {
            $mockClass = $this->generateMock(
                $type,
                $methods,
                null,
                $callOriginalClone,
                $callAutoload,
                $cloneArguments,
                $callOriginalMethods
            );
            self::$cache[$key] =  [
                'code'          => $mockClass->generateCode(),
                'mockClassName' => $mockClass->getClassName()->getQualifiedName(),
            ];
        }

        return self::$cache[$key];
    }

    /**
     * @param string $wsdlFile
     * @param string $className
     *
     * @throws RuntimeException
     *
     * @return string
     */
    public function generateClassFromWsdl($wsdlFile, $className, array $methods = [], array $options = [])
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
            $nameStart = \strpos($method, ' ') + 1;
            $nameEnd   = \strpos($method, '(');
            $name      = \substr($method, $nameStart, $nameEnd - $nameStart);

            if (empty($methods) || \in_array($name, $methods, true)) {
                $args    = \explode(
                    ',',
                    \substr(
                        $method,
                        $nameEnd + 1,
                        \strpos($method, ')') - $nameEnd - 1
                    )
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
     *
     * @return MockMethod[]
     */
    public function mockMethods(array $methods, bool $callOriginalMethods, bool $cloneArguments): array
    {
        $mockedMethods = [];

        foreach ($methods as $method) {
            if (($method->isPublic() || $method->isAbstract()) && $this->canMockMethod($method)) {
                $mockedMethods[] = MockMethod::fromReflection($method, $callOriginalMethods, $cloneArguments);
            }
        }

        return $mockedMethods;
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
     *
     * @return MockObject
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
                $instantiator = new Instantiator;
                $object       = $instantiator->instantiate($className);
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
     * @param string|string[] $type
     * @param null|string[]   $explicitMethods
     * @param TypeName        $mockClassName
     * @param bool            $callOriginalClone
     * @param bool            $callAutoload
     * @param bool            $cloneArguments
     * @param bool            $callOriginalMethods
     *
     * @throws \InvalidArgumentException
     * @throws \ReflectionException
     * @throws RuntimeException
     *
     * @return MockType
     */
    private function generateMock($type, $explicitMethods, $mockClassName, $callOriginalClone, $callAutoload, $cloneArguments, $callOriginalMethods)
    {
        $additionalInterfaces = [];
        $mockMethods          = new MockMethodSet;

        if (\is_array($type)) {
            foreach ($type as $_type) {
                if (!\interface_exists($_type, $callAutoload)) {
                    throw new RuntimeException(
                        \sprintf(
                            'Interface "%s" does not exist.',
                            $_type
                        )
                    );
                }

                $typeClass = new ReflectionClass($_type);

                foreach ($this->mockMethods($typeClass->getMethods(), $callOriginalMethods, $cloneArguments) as $method) {
                    if ($mockMethods->hasMethod($method->getName())) {
                        throw new RuntimeException(
                            \sprintf(
                                'Duplicate method "%s" not allowed.',
                                $method->getName()
                            )
                        );
                    }

                    $mockMethods->addMethods($method);
                }

                $additionalInterfaces[] = $_type;
            }

            $type = \implode('_', $type);
        }

        $originalType = $this->originalType(TypeName::fromQualifiedName($type), $callAutoload);

        if (null === $mockClassName) {
            $mockClassName = $this->generateClassName($originalType->getName(), 'Mock_');
        }

        if ($originalType->isFinal()) {
            throw new RuntimeException(
                \sprintf(
                    'Class "%s" is declared "final" and cannot be mocked.',
                    $originalType->getName()->getQualifiedName()
                )
            );
        }

        // @see https://github.com/sebastianbergmann/phpunit/issues/2995
        if ($originalType->isInterface() && $originalType->implementsInterface(\Throwable::class)) {
            $additionalInterfaces[] = $originalType->getName()->getQualifiedName();

            $originalType = $this->originalType(
                TypeName::fromQualifiedName(\Exception::class),
                $callAutoload
            );
            $mockClassName = $this->generateClassName($originalType->getName(), 'Mock_');
        }

        // https://github.com/sebastianbergmann/phpunit-mock-objects/issues/103
        if ($originalType->isInterface() && $originalType->implementsInterface(Traversable::class) &&
            !$originalType->implementsInterface(Iterator::class) &&
            !$originalType->implementsInterface(IteratorAggregate::class)) {
            $additionalInterfaces[] = Iterator::class;
            $iteratorClass          = new ReflectionClass(Iterator::class);

            $mockMethods->addMethods(
                ...$this->mockMethods($iteratorClass->getMethods(), $callOriginalMethods, $cloneArguments)
            );
        }

        if (\is_array($explicitMethods) && empty($explicitMethods)) {
            $mockMethods->addMethods(
                ...$this->mockMethods($originalType->getMethods(), $callOriginalMethods, $cloneArguments)
            );
        }

        if (\is_array($explicitMethods)) {
            foreach ($explicitMethods as $methodName) {
                if ($originalType->hasMethod($methodName)) {
                    $method = $originalType->getMethod($methodName);

                    if ($this->canMockMethod($method)) {
                        $mockMethods->addMethods(
                            MockMethod::fromReflection($method, $callOriginalMethods, $cloneArguments)
                        );
                    }
                } else {
                    $mockMethods->addMethods(
                        MockMethod::fromName(
                            $originalType->getName(),
                            $methodName,
                            $cloneArguments
                        )
                    );
                }
            }
        }

        return new MockType(
            $originalType,
            $mockClassName,
            $additionalInterfaces,
            $mockMethods,
            $callAutoload,
            $callOriginalClone
        );
    }

    private function generateClassName(TypeName $originalTypeName, string $prefix): TypeName
    {
        do {
            $className = $prefix . $originalTypeName->getSimpleName() . '_' .
                         \substr(\md5(\mt_rand()), 0, 8);
        } while (\class_exists($className, false));

        return TypeName::fromQualifiedName($className);
    }

    /**
     * @return bool
     */
    private function canMockMethod(ReflectionMethod $method)
    {
        return !($method->isConstructor() || $method->isFinal() || $method->isPrivate() || $this->isMethodNameBlacklisted($method->getName()));
    }

    /**
     * Returns whether a method name is blacklisted
     *
     * @param string $name
     *
     * @return bool
     */
    private function isMethodNameBlacklisted($name)
    {
        return isset(self::BLACKLISTED_METHOD_NAMES[$name]);
    }

    /**
     * @param string $template
     *
     * @throws \InvalidArgumentException
     *
     * @return Text_Template
     */
    private function getTemplate($template)
    {
        $filename = __DIR__ . \DIRECTORY_SEPARATOR . 'Generator' . \DIRECTORY_SEPARATOR . $template;

        if (!isset(self::$templates[$filename])) {
            self::$templates[$filename] = new Text_Template($filename);
        }

        return self::$templates[$filename];
    }

    private function originalType(TypeName $originalName, bool $callAutoload): OriginalType
    {
        if (\class_exists($originalName->getQualifiedName(), $callAutoload)
            || \interface_exists($originalName->getQualifiedName(), $callAutoload)
        ) {
            return new OriginalTypeReflection(
                new \ReflectionClass($originalName->getQualifiedName())
            );
        }

        return new OriginalTypeGenerated($originalName);
    }
}
