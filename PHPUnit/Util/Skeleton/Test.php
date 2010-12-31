<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2011, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @package    PHPUnit
 * @subpackage Util_Skeleton
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2002-2011 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.3.0
 */

require_once 'Text/Template.php';

/**
 * Generator for test class skeletons from classes.
 *
 * @package    PHPUnit
 * @subpackage Util_Skeleton
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2002-2011 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.3.0
 */
class PHPUnit_Util_Skeleton_Test extends PHPUnit_Util_Skeleton
{
    /**
     * @var    array
     */
    protected $methodNameCounter = array();

    /**
     * Constructor.
     *
     * @param string $inClassName
     * @param string $inSourceFile
     * @param string $outClassName
     * @param string $outSourceFile
     * @throws RuntimeException
     */
    public function __construct($inClassName, $inSourceFile = '', $outClassName = '', $outSourceFile = '')
    {
        if (class_exists($inClassName)) {
            $reflector    = new ReflectionClass($inClassName);
            $inSourceFile = $reflector->getFileName();

            if ($inSourceFile === FALSE) {
                $inSourceFile = '<internal>';
            }

            unset($reflector);
        } else {
            if (empty($inSourceFile)) {
                $possibleFilenames = array(
                  $inClassName . '.php',
                  PHPUnit_Util_Filesystem::classNameToFilename($inClassName)
                );

                foreach ($possibleFilenames as $possibleFilename) {
                    if (is_file($possibleFilename)) {
                        $inSourceFile = $possibleFilename;
                    }
                }
            }

            if (empty($inSourceFile)) {
                throw new PHPUnit_Framework_Exception(
                  sprintf(
                    'Neither "%s" nor "%s" could be opened.',
                    $possibleFilenames[0],
                    $possibleFilenames[1]
                  )
                );
            }

            if (!is_file($inSourceFile)) {
                throw new PHPUnit_Framework_Exception(
                  sprintf(
                    '"%s" could not be opened.',

                    $inSourceFile
                  )
                );
            }

            $inSourceFile = realpath($inSourceFile);
            include_once $inSourceFile;

            if (!class_exists($inClassName)) {
                throw new PHPUnit_Framework_Exception(
                  sprintf(
                    'Could not find class "%s" in "%s".',

                    $inClassName,
                    $inSourceFile
                  )
                );
            }
        }

        if (empty($outClassName)) {
            $outClassName = $inClassName . 'Test';
        }

        if (empty($outSourceFile)) {
            $outSourceFile = dirname($inSourceFile) . DIRECTORY_SEPARATOR . $outClassName . '.php';
        }

        parent::__construct(
          $inClassName, $inSourceFile, $outClassName, $outSourceFile
        );
    }

    /**
     * Generates the test class' source.
     *
     * @param  boolean $verbose
     * @return mixed
     */
    public function generate($verbose = FALSE)
    {
        $class             = new ReflectionClass(
                               $this->inClassName['fullyQualifiedClassName']
                             );
        $methods           = '';
        $incompleteMethods = '';

        foreach ($class->getMethods() as $method) {
            if (!$method->isConstructor() &&
                !$method->isAbstract() &&
                 $method->isPublic() &&
                 $method->getDeclaringClass()->getName() == $this->inClassName['fullyQualifiedClassName']) {
                $assertAnnotationFound = FALSE;

                if (preg_match_all('/@assert(.*)$/Um', $method->getDocComment(), $annotations)) {
                    foreach ($annotations[1] as $annotation) {
                        if (preg_match('/\((.*)\)\s+([^\s]*)\s+(.*)/', $annotation, $matches)) {
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

                                case '>': {
                                    $assertion = 'GreaterThan';
                                }
                                break;

                                case '>=': {
                                    $assertion = 'GreaterThanOrEqual';
                                }
                                break;

                                case '<': {
                                    $assertion = 'LessThan';
                                }
                                break;

                                case '<=': {
                                    $assertion = 'LessThanOrEqual';
                                }
                                break;

                                case 'throws': {
                                    $assertion = 'exception';
                                }
                                break;

                                default: {
                                    throw new PHPUnit_Framework_Exception(
                                      sprintf(
                                        'Token "%s" could not be parsed in @assert annotation.',
                                        $matches[2]
                                      )
                                    );
                                }
                            }

                            if ($assertion == 'exception') {
                                $template = 'TestMethodException';
                            }

                            else if ($assertion == 'Equals' &&
                                     strtolower($matches[3]) == 'true') {
                                $assertion = 'True';
                                $template  = 'TestMethodBool';
                            }

                            else if ($assertion == 'NotEquals' &&
                                     strtolower($matches[3]) == 'true') {
                                $assertion = 'False';
                                $template  = 'TestMethodBool';
                            }

                            else if ($assertion == 'Equals' &&
                                     strtolower($matches[3]) == 'false') {
                                $assertion = 'False';
                                $template  = 'TestMethodBool';
                            }

                            else if ($assertion == 'NotEquals' &&
                                     strtolower($matches[3]) == 'false') {
                                $assertion = 'True';
                                $template  = 'TestMethodBool';
                            }

                            else {
                                $template = 'TestMethod';
                            }

                            if ($method->isStatic()) {
                                $template .= 'Static';
                            }

                            $methodTemplate = new Text_Template(
                              sprintf(
                                '%s%sTemplate%s%s.tpl',

                                dirname(__FILE__),
                                DIRECTORY_SEPARATOR,
                                DIRECTORY_SEPARATOR,
                                $template
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
                                'annotation'     => trim($annotation),
                                'arguments'      => $matches[1],
                                'assertion'      => isset($assertion) ? $assertion : '',
                                'expected'       => $matches[3],
                                'origMethodName' => $origMethodName,
                                'className'      => $this->inClassName['fullyQualifiedClassName'],
                                'methodName'     => $methodName
                              )
                            );

                            $methods .= $methodTemplate->render();

                            $assertAnnotationFound = TRUE;
                        }
                    }
                }

                if (!$assertAnnotationFound) {
                    $methodTemplate = new Text_Template(
                      sprintf(
                        '%s%sTemplate%sIncompleteTestMethod.tpl',

                        dirname(__FILE__),
                        DIRECTORY_SEPARATOR,
                        DIRECTORY_SEPARATOR
                      )
                    );

                    $methodTemplate->setVar(
                      array(
                        'methodName' => ucfirst($method->getName())
                      )
                    );

                    $incompleteMethods .= $methodTemplate->render();
                }
            }
        }

        $classTemplate = new Text_Template(
          sprintf(
            '%s%sTemplate%sTestClass.tpl',

            dirname(__FILE__),
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
          )
        );

        if ($this->inSourceFile != '<internal>') {
            $requireClassFile = sprintf(
              "\n\nrequire_once '%s';",

              $this->inSourceFile
            );
        } else {
            $requireClassFile = '';
        }

        if ($this->outClassName['namespace'] != '') {
            $namespace = "\nnamespace " .
                         $this->outClassName['namespace'] . ";\n";
        } else {
            $namespace = '';
        }

        $classTemplate->setVar(
          array(
            'namespace'          => $namespace,
            'namespaceSeparator' => !empty($namespace) ? '\\' : '',
            'className'          => $this->inClassName['className'],
            'testClassName'      => $this->outClassName['className'],
            'requireClassFile'   => $requireClassFile,
            'methods'            => $methods . $incompleteMethods,
            'date'               => date('Y-m-d'),
            'time'               => date('H:i:s')
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
}
