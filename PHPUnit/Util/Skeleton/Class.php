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
 * @since      File available since Release 3.3.0
 */

require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Util/Template.php';
require_once 'PHPUnit/Util/Skeleton.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Generator for class skeletons from test classes.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2009 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.3.0
 */
class PHPUnit_Util_Skeleton_Class extends PHPUnit_Util_Skeleton
{
    protected $tokens = array();

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

        $this->tokens = token_get_all(file_get_contents($inSourceFile));

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

        foreach ($this->findMethods() as $method) {
            $methodTemplate = new PHPUnit_Util_Template(
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

        $classTemplate = new PHPUnit_Util_Template(
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
    protected function findMethods()
    {
        $methods   = array();
        $numTokens = count($this->tokens);
        $variables = $this->findVariablesThatReferenceClass();

        for ($i = 0; $i < $numTokens; $i++) {
            if (is_array($this->tokens[$i])) {
                if ($this->tokens[$i][0] == T_DOUBLE_COLON &&
                    $this->tokens[$i-1][0] == T_STRING &&
                    $this->tokens[$i+1][0] == T_STRING &&
                    trim($this->tokens[$i+2]) == '(' &&
                    !in_array($this->tokens[$i+1][1], $methods)) {
                    $methods[] = $this->tokens[$i+1][1];
                }

                else if ($this->tokens[$i][0] == T_OBJECT_OPERATOR &&
                    is_string($this->tokens[$i+2]) &&
                    trim($this->tokens[$i+2]) == '(' &&
                    in_array($this->findVariableName($i), $variables) &&
                    !in_array($this->tokens[$i+1][1], $methods)) {
                    $methods[] = $this->tokens[$i+1][1];
                }
            }
        }

        sort($methods);

        return $methods;
    }

    /**
     * Returns the variables used in test methods
     * that reference the class under test.
     *
     * @return array
     */
    protected function findVariablesThatReferenceClass()
    {
        $inNew     = FALSE;
        $numTokens = count($this->tokens);
        $variables = array();

        for ($i = 0; $i < $numTokens; $i++) {
            if (is_string($this->tokens[$i])) {
                if (trim($this->tokens[$i]) == ';') {
                    $inNew = FALSE;
                }

                continue;
            }

            list ($_token, $_value) = $this->tokens[$i];

            switch ($_token) {
                case T_NEW: {
                    $inNew = TRUE;
                }
                break;

                case T_STRING: {
                    if ($inNew) {
                        if ($_value == $this->outClassName['fullyQualifiedClassName']) {
                            $variables[] = $this->findVariableName($i);
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
     * @param  integer $start
     * @return mixed
     */
    protected function findVariableName($start)
    {
        for ($i = $start - 1; $i >= 0; $i--) {
            if (is_array($this->tokens[$i]) &&
                $this->tokens[$i][0] == T_VARIABLE) {
                $variable = $this->tokens[$i][1];

                if (is_array($this->tokens[$i+1]) &&
                    $this->tokens[$i+1][0] == T_OBJECT_OPERATOR) {
                    $variable .= '->' . $this->tokens[$i+2][1];
                }

                return $variable;
            }
        }

        return FALSE;
    }
}
?>
