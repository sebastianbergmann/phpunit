<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2007, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.0.0
 */

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
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 */
class PHPUnit_Framework_MockObject_Mock
{
    public $mockClassName;
    public $className;
    public $methods;

    public function __construct($className, array $methods = array(), $mockClassName = '')
    {
        $this->mockClassName = $mockClassName;

        if ($this->mockClassName === '') {
            do {
                $random = substr(md5(microtime()), 0, 8);
            }
            while (class_exists('Mock_' . $className . '_' . $random, FALSE));

            $this->mockClassName = 'Mock_' . $className . '_' . $random;
        }

        if (empty($methods)) {
            $methods = get_class_methods($className);
        }

        $this->className = $className;
        $this->methods   = $methods;
    }

    public static function generate($className, array $methods = array(), $mockClassName = '')
    {
        $mock = new PHPUnit_Framework_MockObject_Mock($className, $methods, $mockClassName);

        if (!class_exists($mock->mockClassName, FALSE)) {
            $mock->generateClass();
        }

        return $mock;
    }

    protected function generateClass()
    {
        if (class_exists($this->mockClassName, FALSE)) {
            throw new RuntimeException("Mock class <{$this->mockClassName}> already exists, cannot generate");
        }

        try {
            $class = new ReflectionClass($this->className);

            if ($class->isFinal()) {
                throw new RuntimeException("Class <{$this->className}> is a finalized class, cannot make mock version of it");
            }

            $code = $this->generateClassDefinition($class);

            eval($code);
        }

        catch (Exception $e) {
            throw new RuntimeException("Failed to generate mock class <{$this->mockClassName}> for class <{$this->className}>, caught an exception:\n" . $e->getMessage());
        }
    }

    protected function generateClassDefinition(ReflectionClass $class)
    {
        $code = 'class ';

        if ($class->isInterface()) {
            $code .= "{$this->mockClassName} implements {$this->className}, PHPUnit_Framework_MockObject_MockObject {\n";
        } else {
            $code .= "{$this->mockClassName} extends {$this->className} implements PHPUnit_Framework_MockObject_MockObject {\n";
        }
        $code .= $this->generateMockApi($class);

        foreach($class->getMethods() as $method) {
            if (!$this->canMockMethod($method)) {
                continue;
            }

            if (!$this->shouldMockMethod($method)) {
                continue;
            }

            $code .= $this->generateMethodDefinition($method);
        }

        $code .= "}\n";

        return $code;
    }

    protected function canMockMethod(ReflectionMethod $method)
    {
        if ($method->isConstructor() || $method->isDestructor()) {
            return FALSE;
        }

        if ($method->isFinal()) {
            return FALSE;
        }

        switch ($method->getName())
        {
            case '__construct':
            case '__destruct':
            case '__clone': {
                return FALSE;
            }
            break;

            default: {
                return TRUE;
            }
        }
    }

    protected function shouldMockMethod(ReflectionMethod $method)
    {
        return in_array($method->getName(), $this->methods);
    }

    protected function generateMethodDefinition(ReflectionMethod $method)
    {
        $code = "\n    ";

        if ($method->isPrivate()) {
            $code .= 'private ';
        }

        else if ($method->isProtected()) {
            $code .= 'protected ';
        }

        else {
            $code .= 'public ';
        }

        if ($method->isStatic()) {
            $code .= 'static ';
        }

        $code .= 'function ';

        if ($method->returnsReference()) {
            $code .= '&';
        }

        $code .= sprintf(
          "%s(%s) {\n" .
          "        \$args = func_get_args();\n" .
          "        return \$this->invocationMocker->invoke(new PHPUnit_Framework_MockObject_Invocation(\$this, %s, %s, \$args));\n" .
          "    }\n",

          $method->getName(),
          $this->generateMethodParameters($method),
          var_export($method->getDeclaringClass()->getName(), TRUE),
          var_export($method->getName(), TRUE)
        );

        return $code;
    }

    protected function generateMockApi(ReflectionClass $class)
    {
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

          $this->generateConstructorCode($class),
          $this->generateCloneCode($class)
        );
    }

    protected function generateConstructorCode(ReflectionClass $class)
    {
        $constructor = $class->getConstructor();

        if ($constructor) {
            return sprintf(
              "    public function __construct(%s) {\n" .
              "        \$this->invocationMocker = new PHPUnit_Framework_MockObject_InvocationMocker(\$this);\n" .
              "        parent::%s(%s);\n" .
              "    }\n\n",

              $this->generateMethodParameters($constructor),
              $constructor->getName(),
              $this->generateMethodParameters($constructor, TRUE)
            );
        } else {
            return "    public function __construct() {\n" .
                   "        \$this->invocationMocker = new PHPUnit_Framework_MockObject_InvocationMocker(\$this);\n" .
                   "    }\n\n";
        }
    }

    protected function generateCloneCode(ReflectionClass $class)
    {
        if ($class->hasMethod('__clone')) {
            return "    public function __clone() {\n" .
                   "        \$this->invocationMocker = clone \$this->invocationMocker;\n" .
                   "        parent::__clone();\n" .
                   "    }\n\n";
        } else {
            return "    public function __clone() {\n" .
                   "        \$this->invocationMocker = clone \$this->invocationMocker;\n" .
                   "    }\n\n";
        }
    }

    protected function generateMethodParameters(ReflectionMethod $method, $asCall = FALSE)
    {
        $list = array();

        foreach($method->getParameters() as $parameter) {
            $name = '$' . $parameter->getName();

            if ($asCall) {
                $list[] = $name;
            } else {
                $typeHint = '';

                if ($parameter->isArray()) {
                    $typeHint = 'array ';
                } else {
                    $class = $parameter->getClass();

                    if ($class) {
                        $typeHint = $class->getName() . ' ';
                    }
                }

                $default = '';

                if ($parameter->isDefaultValueAvailable()) {
                    $value   = $parameter->getDefaultValue();
                    $default = ' = ' . var_export($value, TRUE);
                }

                $ref = '';

                if ($parameter->isPassedByReference()) {
                    $ref = '&';
                }

                $list[] = $typeHint . $ref . $name . $default;
            }
        }

        return join(', ', $list);
    }
}
?>
