<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2010-2011, Sebastian Bergmann <sb@sebastian-bergmann.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    PHPUnit_MockObject
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2010-2011 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://github.com/sebastianbergmann/phpunit-mock-objects
 * @since      File available since Release 1.0.0
 */

require_once 'Text/Template.php';

/**
 * Mock Object Code Generator
 *
 * @package    PHPUnit_MockObject
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2010-2011 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://github.com/sebastianbergmann/phpunit-mock-objects
 * @since      Class available since Release 1.0.0
 */
class PHPUnit_Framework_MockObject_Generator
{
    /**
     * @var array
     */
    protected static $cache = array();

    /**
     * @var array
     */
    protected static $blacklistedMethodNames = array(
      '__clone' => TRUE,
      'abstract' => TRUE,
      'and' => TRUE,
      'array' => TRUE,
      'as' => TRUE,
      'break' => TRUE,
      'case' => TRUE,
      'catch' => TRUE,
      'class' => TRUE,
      'clone' => TRUE,
      'const' => TRUE,
      'continue' => TRUE,
      'declare' => TRUE,
      'default' => TRUE,
      'die' => TRUE,
      'do' => TRUE,
      'echo' => TRUE,
      'else' => TRUE,
      'elseif' => TRUE,
      'empty' => TRUE,
      'enddeclare' => TRUE,
      'endfor' => TRUE,
      'endforeach' => TRUE,
      'endif' => TRUE,
      'endswitch' => TRUE,
      'endwhile' => TRUE,
      'eval' => TRUE,
      'exit' => TRUE,
      'extends' => TRUE,
      'final' => TRUE,
      'for' => TRUE,
      'foreach' => TRUE,
      'function' => TRUE,
      'global' => TRUE,
      'goto' => TRUE,
      'if' => TRUE,
      'implements' => TRUE,
      'include' => TRUE,
      'include_once' => TRUE,
      'instanceof' => TRUE,
      'interface' => TRUE,
      'isset' => TRUE,
      'list' => TRUE,
      'namespace' => TRUE,
      'new' => TRUE,
      'or' => TRUE,
      'print' => TRUE,
      'private' => TRUE,
      'protected' => TRUE,
      'public' => TRUE,
      'require' => TRUE,
      'require_once' => TRUE,
      'return' => TRUE,
      'static' => TRUE,
      'switch' => TRUE,
      'throw' => TRUE,
      'try' => TRUE,
      'unset' => TRUE,
      'use' => TRUE,
      'var' => TRUE,
      'while' => TRUE,
      'xor' => TRUE
    );

    /**
     * @var boolean
     */
    protected static $soapLoaded = NULL;

    /**
     * Returns a mock object for the specified class.
     *
     * @param  string  $originalClassName
     * @param  array   $methods
     * @param  array   $arguments
     * @param  string  $mockClassName
     * @param  boolean $callOriginalConstructor
     * @param  boolean $callOriginalClone
     * @param  boolean $callAutoload
     * @return object
     * @throws InvalidArgumentException
     * @since  Method available since Release 1.0.0
     */
    public static function getMock($originalClassName, $methods = array(), array $arguments = array(), $mockClassName = '', $callOriginalConstructor = TRUE, $callOriginalClone = TRUE, $callAutoload = TRUE)
    {
        if (!is_string($originalClassName)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }

        if (!is_string($mockClassName)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(4, 'string');
        }

        if (!is_array($methods) && !is_null($methods)) {
            throw new InvalidArgumentException;
        }

        if ($mockClassName != '' && class_exists($mockClassName, FALSE)) {
            throw new PHPUnit_Framework_Exception(
              sprintf(
                'Class "%s" already exists.',
                $mockClassName
              )
            );
        }

        $mock = self::generate(
          $originalClassName,
          $methods,
          $mockClassName,
          $callOriginalClone,
          $callAutoload
        );

        if (!class_exists($mock['mockClassName'], FALSE)) {
            eval($mock['code']);
        }

        if ($callOriginalConstructor &&
            !interface_exists($originalClassName, $callAutoload)) {
            if (count($arguments) == 0) {
                $mockObject = new $mock['mockClassName'];
            } else {
                $mockClass  = new ReflectionClass($mock['mockClassName']);
                $mockObject = $mockClass->newInstanceArgs($arguments);
            }
        } else {
            // Use a trick to create a new object of a class
            // without invoking its constructor.
            $mockObject = unserialize(
              sprintf(
                'O:%d:"%s":0:{}',
                strlen($mock['mockClassName']), $mock['mockClassName']
              )
            );
        }

        return $mockObject;
    }

    /**
     * Returns a mock object for the specified abstract class with all abstract
     * methods of the class mocked. Concrete methods are not mocked.
     *
     * @param  string  $originalClassName
     * @param  array   $arguments
     * @param  string  $mockClassName
     * @param  boolean $callOriginalConstructor
     * @param  boolean $callOriginalClone
     * @param  boolean $callAutoload
     * @return object
     * @since  Method available since Release 1.0.0
     * @throws InvalidArgumentException
     */
    public static function getMockForAbstractClass($originalClassName, array $arguments = array(), $mockClassName = '', $callOriginalConstructor = TRUE, $callOriginalClone = TRUE, $callAutoload = TRUE)
    {
        if (!is_string($originalClassName)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }

        if (!is_string($mockClassName)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(3, 'string');
        }

        if (class_exists($originalClassName, $callAutoload)) {
            $methods   = array();
            $reflector = new ReflectionClass($originalClassName);

            foreach ($reflector->getMethods() as $method) {
                if ($method->isAbstract()) {
                    $methods[] = $method->getName();
                }
            }

            if (empty($methods)) {
                $methods = NULL;
            }

            return self::getMock(
              $originalClassName,
              $methods,
              $arguments,
              $mockClassName,
              $callOriginalConstructor,
              $callOriginalClone,
              $callAutoload
            );
        } else {
            throw new PHPUnit_Framework_Exception(
              sprintf(
                'Class "%s" does not exist.',
                $originalClassName
              )
            );
        }
    }

    /**
     * @param  string  $originalClassName
     * @param  array   $methods
     * @param  string  $mockClassName
     * @param  boolean $callOriginalClone
     * @param  boolean $callAutoload
     * @return array
     */
    public static function generate($originalClassName, array $methods = NULL, $mockClassName = '', $callOriginalClone = TRUE, $callAutoload = TRUE)
    {
        if ($mockClassName == '') {
            $key = md5(
              $originalClassName .
              serialize($methods) .
              serialize($callOriginalClone)
            );

            if (isset(self::$cache[$key])) {
                return self::$cache[$key];
            }
        }

        $mock = self::generateMock(
          $originalClassName,
          $methods,
          $mockClassName,
          $callOriginalClone,
          $callAutoload
        );

        if (isset($key)) {
            self::$cache[$key] = $mock;
        }

        return $mock;
    }

    /**
     * @param  string $wsdlFile
     * @param  string $originalClassName
     * @param  array  $methods
     * @return array
     */
    public static function generateClassFromWsdl($wsdlFile, $originalClassName, array $methods = array())
    {
        if (self::$soapLoaded === NULL) {
            self::$soapLoaded = extension_loaded('soap');
        }

        if (self::$soapLoaded) {
            $client   = new SOAPClient($wsdlFile);
            $_methods = array_unique($client->__getFunctions());
            unset($client);

            $templateDir    = dirname(__FILE__) . DIRECTORY_SEPARATOR .
                              'Generator' . DIRECTORY_SEPARATOR;
            $methodTemplate = new Text_Template(
                                $templateDir . 'wsdl_method.tpl'
                              );
            $methodsBuffer  = '';

            foreach ($_methods as $method) {
                $nameStart = strpos($method, ' ') + 1;
                $nameEnd   = strpos($method, '(');
                $name      = substr($method, $nameStart, $nameEnd - $nameStart);

                if (empty($methods) || in_array($name, $methods)) {
                    $args    = explode(
                                 ',',
                                 substr(
                                   $method,
                                   $nameEnd + 1,
                                   strpos($method, ')') - $nameEnd - 1
                                 )
                               );
                    $numArgs = count($args);

                    for ($i = 0; $i < $numArgs; $i++) {
                        $args[$i] = substr($args[$i], strpos($args[$i], '$'));
                    }

                    $methodTemplate->setVar(
                      array(
                        'method_name' => $name,
                        'arguments'   => join(', ', $args)
                      )
                    );

                    $methodsBuffer .= $methodTemplate->render();
                }
            }

            $classTemplate = new Text_Template(
              $templateDir . 'wsdl_class.tpl'
            );

            $classTemplate->setVar(
              array(
                'class_name' => $originalClassName,
                'wsdl'       => $wsdlFile,
                'methods'    => $methodsBuffer
              )
            );

            return $classTemplate->render();
        } else {
            throw new PHPUnit_Framework_Exception(
              'The SOAP extension is required to generate a mock object ' .
              'from WSDL.'
            );
        }
    }

    /**
     * @param  string     $originalClassName
     * @param  array|null $methods
     * @param  string     $mockClassName
     * @param  boolean    $callOriginalClone
     * @param  boolean    $callAutoload
     * @return array
     */
    protected static function generateMock($originalClassName, $methods, $mockClassName, $callOriginalClone, $callAutoload)
    {
        $templateDir   = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Generator' .
                         DIRECTORY_SEPARATOR;
        $classTemplate = new Text_Template(
                           $templateDir . 'mocked_class.tpl'
                         );
        $cloneTemplate = '';
        $isClass       = FALSE;
        $isInterface   = FALSE;

        $mockClassName = self::generateMockClassName(
          $originalClassName, $mockClassName
        );

        if (class_exists($mockClassName['fullClassName'], $callAutoload)) {
            $isClass = TRUE;
        } else {
            if (interface_exists($mockClassName['fullClassName'], $callAutoload)) {
                $isInterface = TRUE;
            }
        }

        if (!class_exists($mockClassName['fullClassName'], $callAutoload) &&
            !interface_exists($mockClassName['fullClassName'], $callAutoload)) {
            $prologue = 'class ' . $mockClassName['className'] . "\n{\n}\n\n";

            if (!empty($mockClassName['namespaceName'])) {
                $prologue = 'namespace ' . $mockClassName['namespaceName'] .
                            " {\n\n" . $prologue . "}\n\n" .
                            "namespace {\n\n";

                $epilogue = "\n\n}";
            }

            $cloneTemplate = new Text_Template(
              $templateDir . 'mocked_clone.tpl'
            );
        } else {
            $class = new ReflectionClass($mockClassName['fullClassName']);

            if ($class->isFinal()) {
                throw new PHPUnit_Framework_Exception(
                  sprintf(
                    'Class "%s" is declared "final" and cannot be mocked.',
                    $mockClassName['fullClassName']
                  )
                );
            }

            if ($class->hasMethod('__clone')) {
                $cloneMethod = $class->getMethod('__clone');

                if (!$cloneMethod->isFinal()) {
                    if ($callOriginalClone && !$isInterface) {
                        $cloneTemplate = new Text_Template(
                          $templateDir . 'unmocked_clone.tpl'
                        );
                    } else {
                        $cloneTemplate = new Text_Template(
                          $templateDir . 'mocked_clone.tpl'
                        );
                    }
                }
            } else {
                $cloneTemplate = new Text_Template(
                  $templateDir . 'mocked_clone.tpl'
                );
            }
        }

        if (is_object($cloneTemplate)) {
            $cloneTemplate = $cloneTemplate->render();
        }

        if (is_array($methods) && empty($methods) &&
            ($isClass || $isInterface)) {
            $methods = get_class_methods($mockClassName['fullClassName']);
        }

        if (!is_array($methods)) {
            $methods = array();
        }

        $constructor   = NULL;
        $mockedMethods = '';

        if (isset($class)) {
            if ($class->hasMethod('__construct')) {
                $constructor = $class->getMethod('__construct');
            }

            else if ($class->hasMethod($originalClassName)) {
                $constructor = $class->getMethod($originalClassName);
            }

            foreach ($methods as $methodName) {
                try {
                    $method = $class->getMethod($methodName);

                    if (self::canMockMethod($method)) {
                        $mockedMethods .= self::generateMockedMethodDefinitionFromExisting(
                          $templateDir, $method
                        );
                    }
                }

                catch (ReflectionException $e) {
                    $mockedMethods .= self::generateMockedMethodDefinition(
                      $templateDir, $mockClassName['fullClassName'], $methodName
                    );
                }
            }
        } else {
            foreach ($methods as $methodName) {
                $mockedMethods .= self::generateMockedMethodDefinition(
                  $templateDir, $mockClassName['fullClassName'], $methodName
                );
            }
        }

        $classTemplate->setVar(
          array(
            'prologue'          => isset($prologue) ? $prologue : '',
            'epilogue'          => isset($epilogue) ? $epilogue : '',
            'class_declaration' => self::generateMockClassDeclaration(
                                     $mockClassName, $isInterface
                                   ),
            'clone'             => $cloneTemplate,
            'mock_class_name'   => $mockClassName['mockClassName'],
            'mocked_methods'    => $mockedMethods
          )
        );

        return array(
          'code'          => $classTemplate->render(),
          'mockClassName' => $mockClassName['mockClassName']
        );
    }

    /**
     * @param  string $originalClassName
     * @param  string $mockClassName
     * @return array
     */
    protected static function generateMockClassName($originalClassName, $mockClassName)
    {
        if ($originalClassName[0] == '\\') {
            $originalClassName = substr($originalClassName, 1);
        }

        $classNameParts = explode('\\', $originalClassName);

        if (count($classNameParts) > 1) {
            $originalClassName = array_pop($classNameParts);
            $namespaceName     = join('\\', $classNameParts);
            $fullClassName     = $namespaceName . '\\' . $originalClassName;
        } else {
            $namespaceName = '';
            $fullClassName = $originalClassName;
        }

        if ($mockClassName == '') {
            do {
                $mockClassName = 'Mock_' . $originalClassName . '_' .
                                 substr(md5(microtime()), 0, 8);
            }
            while (class_exists($mockClassName, FALSE));
        }

        return array(
          'mockClassName' => $mockClassName,
          'className'     => $originalClassName,
          'fullClassName' => $fullClassName,
          'namespaceName' => $namespaceName
        );
    }

    /**
     * @param  array   $mockClassName
     * @param  boolean $isInterface
     * @return array
     */
    protected static function generateMockClassDeclaration(array $mockClassName, $isInterface)
    {
        $buffer = 'class ';

        if ($isInterface) {
            $buffer .= sprintf(
              "%s implements PHPUnit_Framework_MockObject_MockObject, %s%s",
              $mockClassName['mockClassName'],
              !empty($mockClassName['namespaceName']) ? $mockClassName['namespaceName'] . '\\' : '',
              $mockClassName['className']
            );
        } else {
            $buffer .= sprintf(
              "%s extends %s%s implements PHPUnit_Framework_MockObject_MockObject",
              $mockClassName['mockClassName'],
              !empty($mockClassName['namespaceName']) ? $mockClassName['namespaceName'] . '\\' : '',
              $mockClassName['className']
            );
        }

        return $buffer;
    }

    /**
     * @param  string           $templateDir
     * @param  ReflectionMethod $method
     * @return string
     */
    protected static function generateMockedMethodDefinitionFromExisting($templateDir, ReflectionMethod $method)
    {
        if ($method->isPrivate()) {
            $modifier = 'private';
        }

        else if ($method->isProtected()) {
            $modifier = 'protected';
        }

        else {
            $modifier = 'public';
        }

        if ($method->isStatic()) {
            $static = TRUE;
        } else {
            $static = FALSE;
        }

        if ($method->returnsReference()) {
            $reference = '&';
        } else {
            $reference = '';
        }

        return self::generateMockedMethodDefinition(
          $templateDir,
          $method->getDeclaringClass()->getName(),
          $method->getName(),
          $modifier,
          PHPUnit_Util_Class::getMethodParameters($method),
          PHPUnit_Util_Class::getMethodParameters($method, TRUE),
          $reference,
          $static
        );
    }

    /**
     * @param  string  $templateDir
     * @param  string  $className
     * @param  string  $methodName
     * @param  string  $modifier
     * @param  string  $arguments_decl
     * @param  string  $arguments_call
     * @param  string  $reference
     * @param  boolean $static
     * @return string
     */
    protected static function generateMockedMethodDefinition($templateDir, $className, $methodName, $modifier = 'public', $arguments_decl = '', $arguments_call = '', $reference = '', $static = FALSE)
    {
        if ($static) {
            $template = new Text_Template(
              $templateDir . 'mocked_static_method.tpl'
            );
        } else {
            $template = new Text_Template(
              $templateDir . 'mocked_object_method.tpl'
            );
        }

        $template->setVar(
          array(
            'arguments_decl'  => $arguments_decl,
            'arguments_call'  => $arguments_call,
            'arguments_count' => !empty($arguments_call) ? count(explode(',', $arguments_call)) : 0,
            'class_name'      => $className,
            'method_name'     => $methodName,
            'modifier'        => $modifier,
            'reference'       => $reference
          )
        );

        return $template->render();
    }

    /**
     * @param  ReflectionMethod $method
     * @return boolean
     */
    protected static function canMockMethod(ReflectionMethod $method)
    {
        if ($method->isConstructor() || $method->isFinal() ||
            isset(self::$blacklistedMethodNames[$method->getName()])) {
            return FALSE;
        }

        return TRUE;
    }
}
