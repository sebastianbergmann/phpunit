<?php
/*
 * This file is part of the phpunit-mock-objects package.
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
use ReflectionException;
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
    private static $cache = [];

    /**
     * @var Text_Template[]
     */
    private static $templates = [];

    /**
     * @var array
     */
    private $blacklistedMethodNames = [
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
     * Returns a mock object for the specified class.
     *
     * @param string|string[] $type
     * @param array           $methods
     * @param array           $arguments
     * @param string          $mockClassName
     * @param bool            $callOriginalConstructor
     * @param bool            $callOriginalClone
     * @param bool            $callAutoload
     * @param bool            $cloneArguments
     * @param bool            $callOriginalMethods
     * @param object          $proxyTarget
     * @param bool            $allowMockingUnknownTypes
     *
     * @throws Exception
     * @throws RuntimeException
     * @throws \PHPUnit\Framework\Exception
     * @throws \ReflectionException
     *
     * @return MockObject
     */
    public function getMock($type, $methods = [], array $arguments = [], $mockClassName = '', $callOriginalConstructor = true, $callOriginalClone = true, $callAutoload = true, $cloneArguments = true, $callOriginalMethods = false, $proxyTarget = null, $allowMockingUnknownTypes = true)
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
            $proxyTarget
        );
    }

    /**
     * Returns a mock object for the specified abstract class with all abstract
     * methods of the class mocked. Concrete methods to mock can be specified with
     * the last parameter
     *
     * @param string $originalClassName
     * @param array  $arguments
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
                if ($method->isAbstract() && !\in_array($method->getName(), $methods)) {
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
     * @param array  $arguments
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
            $traitName,
            '',
            'Trait_'
        );

        $classTemplate = $this->getTemplate('trait_class.tpl');

        $classTemplate->setVar(
            [
                'prologue'   => 'abstract ',
                'class_name' => $className['className'],
                'trait_name' => $traitName
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
     * @param array  $arguments
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
                'trait_name' => $traitName
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
     *
     * @return array
     */
    public function generate($type, array $methods = null, $mockClassName = '', $callOriginalClone = true, $callAutoload = true, $cloneArguments = true, $callOriginalMethods = false)
    {
        if (\is_array($type)) {
            \sort($type);
        }

        if ($mockClassName === '') {
            $key = \md5(
                \is_array($type) ? \implode('_', $type) : $type .
                \serialize($methods) .
                \serialize($callOriginalClone) .
                \serialize($cloneArguments) .
                \serialize($callOriginalMethods)
            );

            if (isset(self::$cache[$key])) {
                return self::$cache[$key];
            }
        }

        $mock = $this->generateMock(
            $type,
            $methods,
            $mockClassName,
            $callOriginalClone,
            $callAutoload,
            $cloneArguments,
            $callOriginalMethods
        );

        if (isset($key)) {
            self::$cache[$key] = $mock;
        }

        return $mock;
    }

    /**
     * @param string $wsdlFile
     * @param string $className
     * @param array  $methods
     * @param array  $options
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

        $options  = \array_merge($options, ['cache_wsdl' => WSDL_CACHE_NONE]);
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

            if (empty($methods) || \in_array($name, $methods)) {
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
                        'arguments'   => \implode(', ', $args)
                    ]
                );

                $methodsBuffer .= $methodTemplate->render();
            }
        }

        $optionsBuffer = 'array(';

        foreach ($options as $key => $value) {
            $optionsBuffer .= $key . ' => ' . $value;
        }

        $optionsBuffer .= ')';

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
                'methods'    => $methodsBuffer
            ]
        );

        return $classTemplate->render();
    }

    /**
     * @param string $className
     *
     * @throws \ReflectionException
     *
     * @return array
     */
    public function getClassMethods($className)
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
     * @param string       $code
     * @param string       $className
     * @param array|string $type
     * @param bool         $callOriginalConstructor
     * @param bool         $callAutoload
     * @param array        $arguments
     * @param bool         $callOriginalMethods
     * @param object       $proxyTarget
     *
     * @throws \ReflectionException
     * @throws RuntimeException
     *
     * @return MockObject
     */
    private function getObject($code, $className, $type = '', $callOriginalConstructor = false, $callAutoload = false, array $arguments = [], $callOriginalMethods = false, $proxyTarget = null)
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

        return $object;
    }

    /**
     * @param string $code
     * @param string $className
     */
    private function evalClass($code, $className)
    {
        if (!\class_exists($className, false)) {
            eval($code);
        }
    }

    /**
     * @param array|string $type
     * @param null|array   $methods
     * @param string       $mockClassName
     * @param bool         $callOriginalClone
     * @param bool         $callAutoload
     * @param bool         $cloneArguments
     * @param bool         $callOriginalMethods
     *
     * @throws \InvalidArgumentException
     * @throws \ReflectionException
     * @throws RuntimeException
     *
     * @return array
     */
    private function generateMock($type, $methods, $mockClassName, $callOriginalClone, $callAutoload, $cloneArguments, $callOriginalMethods)
    {
        $methodReflections   = [];
        $classTemplate       = $this->getTemplate('mocked_class.tpl');

        $additionalInterfaces = [];
        $cloneTemplate        = '';
        $isClass              = false;
        $isInterface          = false;
        $isMultipleInterfaces = false;

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

                $isMultipleInterfaces = true;

                $additionalInterfaces[] = $_type;
                $typeClass              = new ReflectionClass(
                    $this->generateClassName(
                    $_type,
                    $mockClassName,
                    'Mock_'
                    )['fullClassName']
                );

                foreach ($this->getClassMethods($_type) as $method) {
                    if (\in_array($method, $methods)) {
                        throw new RuntimeException(
                            \sprintf(
                                'Duplicate method "%s" not allowed.',
                                $method
                            )
                        );
                    }

                    $methodReflections[$method] = $typeClass->getMethod($method);
                    $methods[]                  = $method;
                }
            }
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

        if (\is_array($methods) && empty($methods) &&
            ($isClass || $isInterface)) {
            $methods = $this->getClassMethods($mockClassName['fullClassName']);
        }

        if (!\is_array($methods)) {
            $methods = [];
        }

        $mockedMethods = '';
        $configurable  = [];

        foreach ($methods as $methodName) {
            if ($methodName !== '__construct' && $methodName !== '__clone') {
                $configurable[] = \strtolower($methodName);
            }
        }

        if (isset($class)) {
            // https://github.com/sebastianbergmann/phpunit-mock-objects/issues/103
            if ($isInterface && $class->implementsInterface(Traversable::class) &&
                !$class->implementsInterface(Iterator::class) &&
                !$class->implementsInterface(IteratorAggregate::class)) {
                $additionalInterfaces[] = Iterator::class;
                $methods                = \array_merge($methods, $this->getClassMethods(Iterator::class));
            }

            foreach ($methods as $methodName) {
                try {
                    $method = $class->getMethod($methodName);

                    if ($this->canMockMethod($method)) {
                        $mockedMethods .= $this->generateMockedMethodDefinitionFromExisting(
                            $method,
                            $cloneArguments,
                            $callOriginalMethods
                        );
                    }
                } catch (ReflectionException $e) {
                    $mockedMethods .= $this->generateMockedMethodDefinition(
                        $mockClassName['fullClassName'],
                        $methodName,
                        $cloneArguments
                    );
                }
            }
        } elseif ($isMultipleInterfaces) {
            foreach ($methods as $methodName) {
                if ($this->canMockMethod($methodReflections[$methodName])) {
                    $mockedMethods .= $this->generateMockedMethodDefinitionFromExisting(
                        $methodReflections[$methodName],
                        $cloneArguments,
                        $callOriginalMethods
                    );
                }
            }
        } else {
            foreach ($methods as $methodName) {
                $mockedMethods .= $this->generateMockedMethodDefinition(
                    $mockClassName['fullClassName'],
                    $methodName,
                    $cloneArguments
                );
            }
        }

        $method = '';

        if (!\in_array('method', $methods) && (!isset($class) || !$class->hasMethod('method'))) {
            $methodTemplate = $this->getTemplate('mocked_class_method.tpl');

            $method = $methodTemplate->render();
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
                'configurable'      => '[' . \implode(', ', \array_map(function ($m) {
                    return '\'' . $m . '\'';
                }, $configurable)) . ']'
            ]
        );

        return [
          'code'          => $classTemplate->render(),
          'mockClassName' => $mockClassName['className']
        ];
    }

    /**
     * @param array|string $type
     * @param string       $className
     * @param string       $prefix
     *
     * @return array
     */
    private function generateClassName($type, $className, $prefix)
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
                             \substr(\md5(\mt_rand()), 0, 8);
            } while (\class_exists($className, false));
        }

        return [
          'className'         => $className,
          'originalClassName' => $type,
          'fullClassName'     => $fullClassName,
          'namespaceName'     => $namespaceName
        ];
    }

    /**
     * @param array $mockClassName
     * @param bool  $isInterface
     * @param array $additionalInterfaces
     *
     * @return string
     */
    private function generateMockClassDeclaration(array $mockClassName, $isInterface, array $additionalInterfaces = [])
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

    /**
     * @param ReflectionMethod $method
     * @param bool             $cloneArguments
     * @param bool             $callOriginalMethods
     *
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     *
     * @return string
     */
    private function generateMockedMethodDefinitionFromExisting(ReflectionMethod $method, $cloneArguments, $callOriginalMethods)
    {
        if ($method->isPrivate()) {
            $modifier = 'private';
        } elseif ($method->isProtected()) {
            $modifier = 'protected';
        } else {
            $modifier = 'public';
        }

        if ($method->isStatic()) {
            $modifier .= ' static';
        }

        if ($method->returnsReference()) {
            $reference = '&';
        } else {
            $reference = '';
        }

        if ($method->hasReturnType()) {
            $returnType = (string) $method->getReturnType();
        } else {
            $returnType = '';
        }

        if (\preg_match('#\*[ \t]*+@deprecated[ \t]*+(.*?)\r?+\n[ \t]*+\*(?:[ \t]*+@|/$)#s', $method->getDocComment(), $deprecation)) {
            $deprecation = \trim(\preg_replace('#[ \t]*\r?\n[ \t]*+\*[ \t]*+#', ' ', $deprecation[1]));
        } else {
            $deprecation = false;
        }

        return $this->generateMockedMethodDefinition(
            $method->getDeclaringClass()->getName(),
            $method->getName(),
            $cloneArguments,
            $modifier,
            $this->getMethodParameters($method),
            $this->getMethodParameters($method, true),
            $returnType,
            $reference,
            $callOriginalMethods,
            $method->isStatic(),
            $deprecation,
            $method->hasReturnType() && $method->getReturnType()->allowsNull()
        );
    }

    /**
     * @param string      $className
     * @param string      $methodName
     * @param bool        $cloneArguments
     * @param string      $modifier
     * @param string      $argumentsForDeclaration
     * @param string      $argumentsForCall
     * @param string      $returnType
     * @param string      $reference
     * @param bool        $callOriginalMethods
     * @param bool        $static
     * @param bool|string $deprecation
     * @param bool        $allowsReturnNull
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    private function generateMockedMethodDefinition($className, $methodName, $cloneArguments = true, $modifier = 'public', $argumentsForDeclaration = '', $argumentsForCall = '', $returnType = '', $reference = '', $callOriginalMethods = false, $static = false, $deprecation = false, $allowsReturnNull = false)
    {
        if ($static) {
            $templateFile = 'mocked_static_method.tpl';
        } else {
            if ($returnType === 'void') {
                $templateFile = \sprintf(
                    '%s_method_void.tpl',
                    $callOriginalMethods ? 'proxied' : 'mocked'
                );
            } else {
                $templateFile = \sprintf(
                    '%s_method.tpl',
                    $callOriginalMethods ? 'proxied' : 'mocked'
                );
            }
        }

        // Mocked interfaces returning 'self' must explicitly declare the
        // interface name as the return type. See
        // https://bugs.php.net/bug.php?id=70722
        if ($returnType === 'self') {
            $returnType = $className;
        }

        if (false !== $deprecation) {
            $deprecation         = "The $className::$methodName method is deprecated ($deprecation).";
            $deprecationTemplate = $this->getTemplate('deprecation.tpl');

            $deprecationTemplate->setVar(
                [
                    'deprecation' => \var_export($deprecation, true),
                ]
            );

            $deprecation = $deprecationTemplate->render();
        }

        $template = $this->getTemplate($templateFile);

        $template->setVar(
            [
                'arguments_decl'  => $argumentsForDeclaration,
                'arguments_call'  => $argumentsForCall,
                'return_delim'    => $returnType ? ': ' : '',
                'return_type'     => $allowsReturnNull ? '?' . $returnType : $returnType,
                'arguments_count' => !empty($argumentsForCall) ? \substr_count($argumentsForCall, ',') + 1 : 0,
                'class_name'      => $className,
                'method_name'     => $methodName,
                'modifier'        => $modifier,
                'reference'       => $reference,
                'clone_arguments' => $cloneArguments ? 'true' : 'false',
                'deprecation'     => $deprecation
            ]
        );

        return $template->render();
    }

    /**
     * @param ReflectionMethod $method
     *
     * @throws \ReflectionException
     *
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
        return isset($this->blacklistedMethodNames[$name]);
    }

    /**
     * Returns the parameters of a function or method.
     *
     * @param ReflectionMethod $method
     * @param bool             $forCall
     *
     * @throws RuntimeException
     *
     * @return string
     */
    private function getMethodParameters(ReflectionMethod $method, $forCall = false)
    {
        $parameters = [];

        foreach ($method->getParameters() as $i => $parameter) {
            $name = '$' . $parameter->getName();

            /* Note: PHP extensions may use empty names for reference arguments
             * or "..." for methods taking a variable number of arguments.
             */
            if ($name === '$' || $name === '$...') {
                $name = '$arg' . $i;
            }

            if ($parameter->isVariadic()) {
                if ($forCall) {
                    continue;
                }

                $name = '...' . $name;
            }

            $nullable        = '';
            $default         = '';
            $reference       = '';
            $typeDeclaration = '';

            if (!$forCall) {
                if ($parameter->hasType() && $parameter->allowsNull()) {
                    $nullable = '?';
                }

                if ($parameter->hasType() && (string) $parameter->getType() !== 'self') {
                    $typeDeclaration = (string) $parameter->getType() . ' ';
                } elseif ($parameter->isArray()) {
                    $typeDeclaration = 'array ';
                } elseif ($parameter->isCallable()) {
                    $typeDeclaration = 'callable ';
                } else {
                    try {
                        $class = $parameter->getClass();
                    } catch (ReflectionException $e) {
                        throw new RuntimeException(
                            \sprintf(
                                'Cannot mock %s::%s() because a class or ' .
                                'interface used in the signature is not loaded',
                                $method->getDeclaringClass()->getName(),
                                $method->getName()
                            ),
                            0,
                            $e
                        );
                    }

                    if ($class !== null) {
                        $typeDeclaration = $class->getName() . ' ';
                    }
                }

                if (!$parameter->isVariadic()) {
                    if ($parameter->isDefaultValueAvailable()) {
                        $value   = $parameter->getDefaultValue();
                        $default = ' = ' . \var_export($value, true);
                    } elseif ($parameter->isOptional()) {
                        $default = ' = null';
                    }
                }
            }

            if ($parameter->isPassedByReference()) {
                $reference = '&';
            }

            $parameters[] = $nullable . $typeDeclaration . $reference . $name . $default;
        }

        return \implode(', ', $parameters);
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
        $filename = __DIR__ . DIRECTORY_SEPARATOR . 'Generator' . DIRECTORY_SEPARATOR . $template;

        if (!isset(self::$templates[$filename])) {
            self::$templates[$filename] = new Text_Template($filename);
        }

        return self::$templates[$filename];
    }
}
