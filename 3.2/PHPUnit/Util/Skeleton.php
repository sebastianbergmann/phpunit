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
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRIC
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 2.1.0
 */

require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Util/Template.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Generator for TestCase skeletons.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.1.0
 */
class PHPUnit_Util_Skeleton
{
    /**
     * @var    string
     * @access protected
     */
    protected $className;

    /**
     * @var    string
     * @access protected
     */
    protected $classSourceFile;

    /**
     * @var    string
     * @access protected
     */
    protected $testSourceFile;

    /**
     * @var    array
     * @access protected
     */
    protected $methodNameCounter = array();

    /**
     * Constructor.
     *
     * @param  string  $className
     * @param  string  $classSourceFile
     * @throws RuntimeException
     * @access public
     */
    public function __construct($className, $classSourceFile = '')
    {
        $this->className      = $className;
        $this->testSourceFile = $className . 'Test.php';

        if (class_exists($className)) {
            $this->classSourceFile = '<internal>';
        }

        else if (empty($classSourceFile) && is_file($className . '.php')) {
            $this->classSourceFile = $className . '.php';
        }

        else if (empty($classSourceFile) ||
                 is_file(str_replace('_', '/', $className) . '.php')) {
            $this->classSourceFile = str_replace('_', '/', $className) . '.php';
            $this->testSourceFile  = str_replace('_', '/', $className) . 'Test.php';
        }

        else if (empty($classSourceFile)) {
            throw new RuntimeException(
              sprintf(
                'Neither "%s.php" nor "%s.php" could be opened.',
                $className,
                str_replace('_', '/', $className)
              )
            );
        }

        else if (!is_file($classSourceFile)) {
            throw new RuntimeException(
              sprintf(
                '"%s" could not be opened.',

                $classSourceFile
              )
            );
        } else {
            $this->classSourceFile = $classSourceFile;
        }

        if ($this->classSourceFile != '<internal>') {
            include_once $this->classSourceFile;
        }

        if (!class_exists($className)) {
            throw new RuntimeException(
              sprintf(
                'Could not find class "%s" in "%s".',

                $className,
                realpath($this->classSourceFile)
              )
            );
        }
    }

    /**
     * Generates the test class' source.
     *
     * @param  boolean $verbose
     * @return mixed
     * @access public
     */
    public function generate($verbose = FALSE)
    {
        $class             = new ReflectionClass($this->className);
        $methods           = '';
        $incompleteMethods = '';

        foreach ($class->getMethods() as $method) {
            if (!$method->isConstructor() &&
                !$method->isAbstract() &&
                 $method->isPublic() &&
                 $method->getDeclaringClass()->getName() == $this->className) {
                $testTagFound = FALSE;

                if (preg_match_all('/@assert(.*)$/Um', $method->getDocComment(), $annotations)) {
                    foreach ($annotations[1] as $annotation) {
                        if (preg_match('/\((.*)\) (.*) (.*)/', $annotation, $matches)) {
                            switch ($matches[2]) {
                                case '==': {
                                    $assertion = 'Equals';
                                }
                                break;

                                case '!=': {
                                    $assertion = 'NotEquals';
                                }
                                break;

                                case '===': {
                                    $assertion = 'Same';
                                }
                                break;

                                case '!==': {
                                    $assertion = 'NotSame';
                                }
                                break;

                                default: {
                                    throw new RuntimeException;
                                }
                            }

                            $methodTemplate = new PHPUnit_Util_Template(
                                sprintf(
                                '%s%sSkeleton%sTestMethod.tpl',

                                dirname(__FILE__),
                                DIRECTORY_SEPARATOR,
                                DIRECTORY_SEPARATOR
                                )
                            );

                            $origMethodName = $method->getName();
                            $methodName     = ucfirst($origMethodName);

                            if (isset($this->methodNameCounter[$methodName])) {
                                $this->methodNameCounter[$methodName]++;
                            } else {
                                $this->methodNameCounter[$methodName] = 1;
                            }

                            if ($this->methodNameCounter[$methodName] > 1) {
                                $methodName .= $this->methodNameCounter[$methodName];
                            }

                            $methodTemplate->setVar(
                                array(
                                'annotation',
                                'arguments',
                                'assertion',
                                'class',
                                'expected',
                                'origMethodName',
                                'methodName'
                                ),
                                array(
                                trim($token[1]),
                                $matches[1],
                                $assertion,
                                $this->className,
                                $matches[3],
                                $origMethodName,
                                $methodName
                                )
                            );

                            $methods .= $methodTemplate->render();

                            $testTagFound = TRUE;
                        }
                    }
                }

                if (!$testTagFound) {
                    $methodTemplate = new PHPUnit_Util_Template(
                      sprintf(
                        '%s%sSkeleton%sIncompleteTestMethod.tpl',

                        dirname(__FILE__),
                        DIRECTORY_SEPARATOR,
                        DIRECTORY_SEPARATOR
                      )
                    );

                    $methodTemplate->setVar(
                      'methodName',
                      ucfirst($method->getName())
                    );

                    $incompleteMethods .= $methodTemplate->render();
                }
            }
        }

        $classTemplate = new PHPUnit_Util_Template(
          sprintf(
            '%s%sSkeleton%sTestClass.tpl',

            dirname(__FILE__),
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
          )
        );

        if ($this->classSourceFile != '<internal>') {
            $requireClassFile = sprintf(
              "\n\nrequire_once '%s';",

              $this->classSourceFile
            );
        } else {
            $requireClassFile = '';
        }

        $classTemplate->setVar(
          array(
            'className',
            'requireClassFile',
            'methods',
            'date',
            'time'
          ),
          array(
            $this->className,
            $requireClassFile,
            !empty($methods) ? $methods : $incompleteMethods,
            date('Y-m-d'),
            date('H:i:s')
          )
        );

        if (!$verbose) {
            return $classTemplate->render();
        } else {
            return array(
              'code'       => $classTemplate->render(),
              'incomplete' => empty($methods)
            );
        }
    }

    /**
     * Generates the test class and writes it to a source file.
     *
     * @param  string  $file
     * @access public
     */
    public function write($file = '')
    {
        if ($file == '') {
            $file = $this->testSourceFile;
        }

        if ($fp = @fopen($file, 'wt')) {
            @fwrite($fp, $this->generate());
            @fclose($fp);
        }
    }

    /**
     * @return string
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function getTestSourceFile()
    {
        return $this->testSourceFile;
    }
}
?>
