<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2009, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2009 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.4.0
 */

require_once 'Text/Template.php';
require_once 'PHPUnit/Framework/MockObject/Matcher.php';
require_once 'PHPUnit/Framework/MockObject/Invocation.php';
require_once 'PHPUnit/Framework/MockObject/MockObject.php';
require_once 'PHPUnit/Util/Class.php';
require_once 'PHPUnit/Util/Filter.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Mock Object Code Generator
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2009 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.4.0
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
      '__destruct' => TRUE,
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
      'do' => TRUE,
      'else' => TRUE,
      'elseif' => TRUE,
      'enddeclare' => TRUE,
      'endfor' => TRUE,
      'endforeach' => TRUE,
      'endif' => TRUE,
      'endswitch' => TRUE,
      'endwhile' => TRUE,
      'extends' => TRUE,
      'final' => TRUE,
      'for' => TRUE,
      'foreach' => TRUE,
      'function' => TRUE,
      'global' => TRUE,
      'goto' => TRUE,
      'if' => TRUE,
      'implements' => TRUE,
      'interface' => TRUE,
      'instanceof' => TRUE,
      'namespace' => TRUE,
      'new' => TRUE,
      'or' => TRUE,
      'private' => TRUE,
      'protected' => TRUE,
      'public' => TRUE,
      'static' => TRUE,
      'switch' => TRUE,
      'throw' => TRUE,
      'try' => TRUE,
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
            $_methods = $client->__getFunctions();
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
     * @param  string  $originalClassName
     * @param  array   $methods
     * @param  string  $mockClassName
     * @param  boolean $callOriginalClone
     * @param  boolean $callAutoload
     * @return array
     */
    protected static function generateMock($originalClassName, array $methods = NULL, $mockClassName, $callOriginalClone, $callAutoload)
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
                            ";\n\n" . $prologue;
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
                    if ($callOriginalClone) {
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
            'class_declaration' => self::generateMockClassDeclaration(
                                     $mockClassName, $isInterface
                                   ),
            'clone'             => $cloneTemplate,
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
          $reference
        );
    }

    /**
     * @param  string  $templateDir
     * @param  string  $className
     * @param  string  $methodName
     * @param  string  $modifier
     * @param  string  $arguments
     * @param  string  $reference
     * @return string
     */
    protected static function generateMockedMethodDefinition($templateDir, $className, $methodName, $modifier = 'public', $arguments = '', $reference = '')
    {
        $template = new Text_Template(
          $templateDir . 'mocked_method.tpl'
        );

        $template->setVar(
          array(
            'arguments'   => $arguments,
            'class_name'  => $className,
            'method_name' => $methodName,
            'modifier'    => $modifier,
            'reference'   => $reference
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
        if ($method->isConstructor() ||
            $method->isFinal() ||
            $method->isStatic() ||
            isset(self::$blacklistedMethodNames[$method->getName()])) {
            return FALSE;
        }

        return TRUE;
    }
}
?>
