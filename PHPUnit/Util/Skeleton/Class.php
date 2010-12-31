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
 * Generator for class skeletons from test classes.
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
class PHPUnit_Util_Skeleton_Class extends PHPUnit_Util_Skeleton
{
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
        if (empty($inSourceFile)) {
            $inSourceFile = $inClassName . '.php';
        }

        if (!is_file($inSourceFile)) {
            throw new PHPUnit_Framework_Exception(
              sprintf(
                '"%s" could not be opened.',

                $inSourceFile
              )
            );
        }

        if (empty($outClassName)) {
            $outClassName = substr($inClassName, 0, strlen($inClassName) - 4);
        }

        if (empty($outSourceFile)) {
            $outSourceFile = dirname($inSourceFile) . DIRECTORY_SEPARATOR .
                             $outClassName . '.php';
        }

        parent::__construct(
          $inClassName, $inSourceFile, $outClassName, $outSourceFile
        );
    }

    /**
     * Generates the class' source.
     *
     * @return mixed
     */
    public function generate()
    {
        $methods = '';

        foreach ($this->findTestedMethods() as $method) {
            $methodTemplate = new Text_Template(
              sprintf(
                '%s%sTemplate%sMethod.tpl',

                dirname(__FILE__),
                DIRECTORY_SEPARATOR,
                DIRECTORY_SEPARATOR
              )
            );

            $methodTemplate->setVar(
              array(
                'methodName' => $method,
              )
            );

            $methods .= $methodTemplate->render();
        }

        $classTemplate = new Text_Template(
          sprintf(
            '%s%sTemplate%sClass.tpl',

            dirname(__FILE__),
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
          )
        );

        $classTemplate->setVar(
          array(
            'className' => $this->outClassName['fullyQualifiedClassName'],
            'methods'   => $methods,
            'date'      => date('Y-m-d'),
            'time'      => date('H:i:s')
          )
        );

        return $classTemplate->render();
    }

    /**
     * Returns the methods of the class under test
     * that are called from the test methods.
     *
     * @return array
     */
    protected function findTestedMethods()
    {
        $setUpVariables = array();
        $testedMethods  = array();
        $classes        = PHPUnit_Util_File::getClassesInFile(
                            $this->inSourceFile
                          );
        $testMethods    = $classes[$this->inClassName['fullyQualifiedClassName']]['methods'];
        unset($classes);

        foreach ($testMethods as $name => $testMethod) {
            if (strtolower($name) == 'setup') {
                $setUpVariables = $this->findVariablesThatReferenceClass(
                  $testMethod['tokens']
                );

                break;
            }
        }

        foreach ($testMethods as $name => $testMethod) {
            $argVariables = array();

            if (strtolower($name) == 'setup') {
                continue;
            }

            $start = strpos($testMethod['signature'], '(') + 1;
            $end   = strlen($testMethod['signature']) - $start - 1;
            $args  = substr($testMethod['signature'], $start, $end);

            foreach (explode(',', $args) as $arg) {
                $arg = explode(' ', trim($arg));

                if (count($arg) == 2) {
                    $type = $arg[0];
                    $var  = $arg[1];
                } else {
                    $type = NULL;
                    $var  = $arg[0];
                }

                if ($type == $this->outClassName['fullyQualifiedClassName']) {
                    $argVariables[] = $var;
                }
            }

            $variables = array_unique(
              array_merge(
                $setUpVariables,
                $argVariables,
                $this->findVariablesThatReferenceClass($testMethod['tokens'])
              )
            );

            foreach ($testMethod['tokens'] as $i => $token) {
                // Class::method()
                if (is_array($token) && $token[0] == T_DOUBLE_COLON &&
                    is_array($testMethod['tokens'][$i-1]) &&
                    $testMethod['tokens'][$i-1][0] == T_STRING &&
                    $testMethod['tokens'][$i-1][1] == $this->outClassName['fullyQualifiedClassName'] &&
                    is_array($testMethod['tokens'][$i+1]) &&
                    $testMethod['tokens'][$i+1][0] == T_STRING &&
                    $testMethod['tokens'][$i+2] == '(') {
                    $testedMethods[] = $testMethod['tokens'][$i+1][1];
                }

                // $this->object->method()
                else if (is_array($token) && $token[0] == T_OBJECT_OPERATOR &&
                    in_array($this->findVariableName($testMethod['tokens'], $i), $variables) &&
                    is_array($testMethod['tokens'][$i+2]) &&
                    $testMethod['tokens'][$i+2][0] == T_OBJECT_OPERATOR &&
                    is_array($testMethod['tokens'][$i+3]) &&
                    $testMethod['tokens'][$i+3][0] == T_STRING &&
                    $testMethod['tokens'][$i+4] == '(') {
                    $testedMethods[] = $testMethod['tokens'][$i+3][1];
                }

                // $object->method()
                else if (is_array($token) && $token[0] == T_OBJECT_OPERATOR &&
                    in_array($this->findVariableName($testMethod['tokens'], $i), $variables) &&
                    is_array($testMethod['tokens'][$i+1]) &&
                    $testMethod['tokens'][$i+1][0] == T_STRING &&
                    $testMethod['tokens'][$i+2] == '(') {
                    $testedMethods[] = $testMethod['tokens'][$i+1][1];
                }
            }
        }

        $testedMethods = array_unique($testedMethods);
        sort($testedMethods);

        return $testedMethods;
    }

    /**
     * Returns the variables used in test methods
     * that reference the class under test.
     *
     * @param  array $tokens
     * @return array
     */
    protected function findVariablesThatReferenceClass(array $tokens)
    {
        $inNew     = FALSE;
        $variables = array();

        foreach ($tokens as $i => $token) {
            if (is_string($token)) {
                if (trim($token) == ';') {
                    $inNew = FALSE;
                }

                continue;
            }

            list ($token, $value) = $token;

            switch ($token) {
                case T_NEW: {
                    $inNew = TRUE;
                }
                break;

                case T_STRING: {
                    if ($inNew) {
                        if ($value == $this->outClassName['fullyQualifiedClassName']) {
                            $variables[] = $this->findVariableName(
                              $tokens, $i
                            );
                        }
                    }

                    $inNew = FALSE;
                }
                break;
            }
        }

        return $variables;
    }

    /**
     * Finds the variable name of the object for the method call
     * that is currently being processed.
     *
     * @param  array   $tokens
     * @param  integer $start
     * @return mixed
     */
    protected function findVariableName(array $tokens, $start)
    {
        for ($i = $start - 1; $i >= 0; $i--) {
            if (is_array($tokens[$i]) && $tokens[$i][0] == T_VARIABLE) {
                $variable = $tokens[$i][1];

                if (is_array($tokens[$i+1]) &&
                    $tokens[$i+1][0] == T_OBJECT_OPERATOR &&
                    $tokens[$i+2] != '(' &&
                    $tokens[$i+3] != '(') {
                    $variable .= '->' . $tokens[$i+2][1];
                }

                return $variable;
            }
        }

        return FALSE;
    }
}
