<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2008, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @author     Jan Borsodi <jb@ez.no>
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2008 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.0.0
 */

require_once 'PHPUnit/Util/Class.php';
require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Framework/MockObject/Matcher.php';
require_once 'PHPUnit/Framework/MockObject/Invocation.php';
require_once 'PHPUnit/Framework/MockObject/MockObject.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Provides generation of mock classes and objects from existing classes.
 *
 * The mocked class will contain all the methods of the original class but with
 * a different implementation which will call the current
 * PHPUnit_Framework_MockObject_InvocationMocker object, this objects takes
 * care of checking expectations and stubs.
 * It is also possible to define which methods are mocked by passing an array
 * of method names.
 *
 * The simplest way to define a mock object is to do:
 *
 * <code>
 * PHPUnit_Framework_MockObject_Mock::generate('MyClass');
 * $o = new Mock_MyClass;
 * </code>
 *
 * The generate() method returns an object which can be queried.
 *
 * <code>
 * $m = PHPUnit_Framework_MockObject::generate('MyClass');
 * $o = new $m->mockClassName;
 * print "original class was: . $m->className;
 * </code>
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Jan Borsodi <jb@ez.no>
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2008 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 */
class PHPUnit_Framework_MockObject_Mock
{
    public $mockClassName;
    public $className;
    public $fullClassName;
    public $namespaceName;
    public $methods;
    protected $callOriginalConstructor;
    protected $callOriginalClone;
    protected $callAutoload;
    protected static $cache = array();

    public function __construct($className, array $methods = array(), $mockClassName = '', $callOriginalConstructor = TRUE, $callOriginalClone = TRUE, $callAutoload = TRUE)
    {
        $classNameParts = explode('::', $className);

        if (count($classNameParts) > 1) {
            $className           = array_pop($classNameParts);
            $namespaceName       = join('::', $classNameParts);
            $this->fullClassName = $namespaceName . '::' . $className;
        } else {
            $namespaceName       = '';
            $this->fullClassName = $className;
        }

        if ($mockClassName === '') {
            do {
                $mockClassName = 'Mock_' . $className . '_' . substr(md5(microtime()), 0, 8);
            }
            while (class_exists($mockClassName, FALSE));
        }

        else if (class_exists($mockClassName, FALSE)) {
            throw new RuntimeException(
              sprintf(
                'Class "%s" already exists.',
                $mockClassName
              )
            );
        }

        $isClass     = class_exists($className, $callAutoload);
        $isInterface = interface_exists($className, $callAutoload);

        if (empty($methods) && ($isClass || $isInterface)) {
            $methods = get_class_methods($className);
        }

        if ($isInterface) {
            $callOriginalConstructor = FALSE;
        }

        $this->mockClassName           = $mockClassName;
        $this->className               = $className;
        $this->namespaceName           = $namespaceName;
        $this->methods                 = $methods;
        $this->callOriginalConstructor = $callOriginalConstructor;
        $this->callOriginalClone       = $callOriginalClone;
        $this->callAutoload            = $callAutoload;
    }

    public static function generate($className, array $methods = array(), $mockClassName = '', $callOriginalConstructor = TRUE, $callOriginalClone = TRUE, $callAutoload = TRUE)
    {
        if ($mockClassName == '') {
            $key = md5(
              $className .
              serialize($methods) .
              serialize($callOriginalConstructor) .
              serialize($callOriginalClone)
            );

            if (!isset(self::$cache[$key])) {
                self::$cache[$key] = self::generateMock(
                  $className,
                  $methods,
                  $mockClassName,
                  $callOriginalConstructor,
                  $callOriginalClone,
                  $callAutoload
                );
            }

            return self::$cache[$key];
        }

        return self::generateMock(
          $className,
          $methods,
          $mockClassName,
          $callOriginalConstructor,
          $callOriginalClone,
          $callAutoload
        );
    }

    protected static function generateMock($className, array $methods, $mockClassName, $callOriginalConstructor, $callOriginalClone, $callAutoload)
    {
        $mock = new PHPUnit_Framework_MockObject_Mock(
          $className,
          $methods,
          $mockClassName,
          $callOriginalConstructor,
          $callOriginalClone,
          $callAutoload
        );

        $mock->generateClass();

        return $mock;
    }

    protected function generateClass()
    {
        if (!class_exists($this->fullClassName, $this->callAutoload) && !interface_exists($this->fullClassName, $this->callAutoload)) {
            $code = 'class ' . $this->className . ' {}';

            if (!empty($this->namespaceName)) {
                $code = 'namespace ' . $this->namespaceName . ';' . $code;
            }

            eval($code);
        }

        try {
            $class = new ReflectionClass($this->fullClassName);

            if ($class->isFinal()) {
                throw new RuntimeException(
                  sprintf(
                    'Class "%s" is declared "final" and cannot be mocked.',
                    $this->fullClassName
                  )
                );
            }

            $code = $this->generateClassDefinition($class);

            eval($code);
        }

        catch (Exception $e) {
            throw new RuntimeException(
              sprintf(
                'Failed to generate mock class "%s" for class "%s".\n%s',
                $this->mockClassName,
                $this->fullClassName,
                $e->getMessage()
              )
            );
        }
    }

    protected function generateClassDefinition(ReflectionClass $class)
    {
        $code = 'class ';

        if ($class->isInterface()) {
            $code .= sprintf(
              "%s implements %s%s, PHPUnit_Framework_MockObject_MockObject {\n",
              $this->mockClassName,
              !empty($this->namespaceName) ? $this->namespaceName . '::' : '',
              $this->className
            );
        } else {
            $code .= sprintf(
              "%s extends %s%s implements PHPUnit_Framework_MockObject_MockObject {\n",
              $this->mockClassName,
              !empty($this->namespaceName) ? $this->namespaceName . '::' : '',
              $this->className
            );
        }

        $code .= $this->generateMockApi($class);

        foreach ($this->methods as $methodName) {
            try {
                $method = $class->getMethod($methodName);

                if ($this->canMockMethod($method)) {
                    $code .= $this->generateMethodDefinitionFromExisting($method);
                }
            }

            catch (ReflectionException $e) {
                $code .= $this->generateMethodDefinition($class->getName(), $methodName, 'public');
            }
        }

        $code .= "}\n";

        return $code;
    }

    protected function canMockMethod(ReflectionMethod $method)
    {
        $className  = $method->getDeclaringClass()->getName();
        $methodName = $method->getName();

        if ($method->isFinal() ||
            $methodName == '__construct' || $methodName == $className ||
            $methodName == '__destruct'  || $method->getName() == '__clone') {
            return FALSE;
        }

        return TRUE;
    }

    protected function generateMethodDefinitionFromExisting(ReflectionMethod $method)
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
            $modifier .= ' static';
        }

        if ($method->returnsReference()) {
            $reference = '&';
        } else {
            $reference = '';
        }

        return $this->generateMethodDefinition(
          $method->getDeclaringClass()->getName(),
          $method->getName(),
          $modifier,
          $reference,
          PHPUnit_Util_Class::getMethodParameters($method)
        );
    }

    protected function generateMethodDefinition($className, $methodName, $modifier, $reference = '', $parameters = '')
    {
        return sprintf(
          "\n    %s function %s%s(%s) {\n" .
          "        \$args   = func_get_args();\n" .
          "        \$result = \$this->invocationMocker->invoke(\n" .
          "          new PHPUnit_Framework_MockObject_Invocation(\$this, \"%s\", \"%s\", \$args)\n" .
          "        );\n\n" .
          "        return \$result;\n" .
          "    }\n",

          $modifier,
          $reference,
          $methodName,
          $parameters,
          $className,
          $methodName
        );
    }

    protected function generateMockApi(ReflectionClass $class)
    {
        if ($this->callOriginalConstructor) {
            $constructorCode = $this->generateConstructorCodeWithParentCall($class);
        } else {
            $constructorCode = $this->generateConstructorCode($class);
        }

        if ($this->callOriginalClone && $class->hasMethod('__clone')) {
            $cloneCode = $this->generateCloneCodeWithParentCall();
        } else {
            $cloneCode = $this->generateCloneCode();
        }

        return sprintf(
          "    private \$invocationMocker;\n\n" .
          "%s" .
          "%s" .
          "    public function getInvocationMocker() {\n" .
          "        return \$this->invocationMocker;\n" .
          "    }\n\n" .
          "    public function expects(PHPUnit_Framework_MockObject_Matcher_Invocation \$matcher) {\n" .
          "        return \$this->invocationMocker->expects(\$matcher);\n" .
          "    }\n\n" .
          "    public function verify() {\n" .
          "        \$this->invocationMocker->verify();\n" .
          "    }\n",

          $constructorCode,
          $cloneCode
        );
    }

    protected function generateConstructorCode(ReflectionClass $class)
    {
        if (!$this->callOriginalConstructor) {
            return "    public function __construct() {\n" .
                   "        \$this->invocationMocker = new PHPUnit_Framework_MockObject_InvocationMocker(\$this);\n" .
                   "    }\n\n";
        }

        $className   = $class->getName();
        $constructor = FALSE;

        if ($class->hasMethod('__construct')) {
            $constructor = $class->getMethod('__construct');
        }

        else if ($class->hasMethod($className)) {
            $constructor = $class->getMethod($className);
        }

        return sprintf(
          "    public function __construct(%s) {\n" .
          "        \$this->invocationMocker = new PHPUnit_Framework_MockObject_InvocationMocker(\$this);\n" .
          "    }\n\n",

          $constructor !== FALSE ? PHPUnit_Util_Class::getMethodParameters($constructor) : ''
        );
    }

    protected function generateConstructorCodeWithParentCall(ReflectionClass $class)
    {
        $constructor = $this->getConstructor($class);

        if ($constructor) {
            return sprintf(
              "    public function __construct(%s) {\n" .
              "        \$args = func_get_args();\n" .
              "        \$this->invocationMocker = new PHPUnit_Framework_MockObject_InvocationMocker;\n" .
              "        \$class = new ReflectionClass(\$this);\n" .
              "        \$class->getParentClass()->getConstructor()->invokeArgs(\$this, \$args);\n" .
              "    }\n\n",

              PHPUnit_Util_Class::getMethodParameters($constructor)
            );
        } else {
            return $this->generateConstructorCode($class);
        }
    }

    protected function generateCloneCode()
    {
        return "    public function __clone() {\n" .
               "        \$this->invocationMocker = clone \$this->invocationMocker;\n" .
               "    }\n\n";
    }

    protected function generateCloneCodeWithParentCall()
    {
        return "    public function __clone() {\n" .
               "        \$this->invocationMocker = clone \$this->invocationMocker;\n" .
               "        parent::__clone();\n" .
               "    }\n\n";
    }

    protected function getConstructor(ReflectionClass $class)
    {
        $className   = $class->getName();
        $constructor = NULL;

        if ($class->hasMethod('__construct')) {
            $constructor = $class->getMethod('__construct');
        }

        else if ($class->hasMethod($className)) {
            $constructor = $class->getMethod($className);
        }

        return $constructor;
    }
}
?>
