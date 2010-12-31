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
 * @subpackage Util
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2002-2011 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.4.0
 */

if (!defined('T_NAMESPACE')) {
    define('T_NAMESPACE', 377);
}

/**
 * File helpers.
 *
 * @package    PHPUnit
 * @subpackage Util
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2002-2011 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.4.0
 */
class PHPUnit_Util_File
{
    /**
     * @var array
     */
    protected static $cache = array();

    /**
     * Returns information on the classes declared in a sourcefile.
     *
     * @param  string $filename
     * @return array
     */
    public static function getClassesInFile($filename)
    {
        if (!isset(self::$cache[$filename])) {
            self::$cache[$filename] = self::parseFile($filename);
        }

        return self::$cache[$filename];
    }

    /**
     * Parses a file for class and method information.
     *
     * @param  string $filename
     * @return array
     */
    protected static function parseFile($filename)
    {
        $result = array();

        $tokens                     = token_get_all(
                                        file_get_contents($filename)
                                      );
        $numTokens                  = count($tokens);
        $blocks                     = array();
        $line                       = 1;
        $currentBlock               = FALSE;
        $currentNamespace           = FALSE;
        $currentClass               = FALSE;
        $currentFunction            = FALSE;
        $currentFunctionStartLine   = FALSE;
        $currentFunctionTokens      = array();
        $currentDocComment          = FALSE;
        $currentSignature           = FALSE;
        $currentSignatureStartToken = FALSE;

        for ($i = 0; $i < $numTokens; $i++) {
            if ($currentFunction !== FALSE) {
                $currentFunctionTokens[] = $tokens[$i];
            }

            if (is_string($tokens[$i])) {
                if ($tokens[$i] == '{') {
                    if ($currentBlock == T_CLASS) {
                        $block = $currentClass;
                    }

                    else if ($currentBlock == T_FUNCTION) {
                        $currentSignature = '';

                        for ($j = $currentSignatureStartToken; $j < $i; $j++) {
                            if (is_string($tokens[$j])) {
                                $currentSignature .= $tokens[$j];
                            } else {
                                $currentSignature .= $tokens[$j][1];
                            }
                        }

                        $currentSignature = trim($currentSignature);

                        $block                      = $currentFunction;
                        $currentSignatureStartToken = FALSE;
                    }

                    else {
                        $block = FALSE;
                    }

                    array_push($blocks, $block);

                    $currentBlock = FALSE;
                }

                else if ($tokens[$i] == '}') {
                    $block = array_pop($blocks);

                    if ($block !== FALSE && $block !== NULL) {
                        if ($block == $currentFunction) {
                            if ($currentDocComment !== FALSE) {
                                $docComment        = $currentDocComment;
                                $currentDocComment = FALSE;
                            } else {
                                $docComment = '';
                            }

                            $tmp = array(
                              'docComment' => $docComment,
                              'signature'  => $currentSignature,
                              'startLine'  => $currentFunctionStartLine,
                              'endLine'    => $line,
                              'tokens'     => $currentFunctionTokens
                            );

                            if ($currentClass !== FALSE) {
                                $result[$currentClass]['methods'][$currentFunction] = $tmp;
                            }

                            $currentFunction          = FALSE;
                            $currentFunctionStartLine = FALSE;
                            $currentFunctionTokens    = array();
                            $currentSignature         = FALSE;
                        }

                        else if ($block == $currentClass) {
                            $result[$currentClass]['endLine'] = $line;

                            $currentClass          = FALSE;
                            $currentClassStartLine = FALSE;
                        }
                    }
                }

                continue;
            }

            switch ($tokens[$i][0]) {
                case T_HALT_COMPILER: {
                    return;
                }
                break;

                case T_NAMESPACE: {
                    $currentNamespace = $tokens[$i+2][1];

                    for ($j = $i+3; $j < $numTokens; $j += 2) {
                        if ($tokens[$j][0] == T_NS_SEPARATOR) {
                            $currentNamespace .= '\\' . $tokens[$j+1][1];
                        } else {
                            break;
                        }
                    }
                }
                break;

                case T_CURLY_OPEN: {
                    $currentBlock = T_CURLY_OPEN;
                    array_push($blocks, $currentBlock);
                }
                break;

                case T_DOLLAR_OPEN_CURLY_BRACES: {
                    $currentBlock = T_DOLLAR_OPEN_CURLY_BRACES;
                    array_push($blocks, $currentBlock);
                }
                break;

                case T_CLASS: {
                    $currentBlock = T_CLASS;

                    if ($currentNamespace === FALSE) {
                        $currentClass = $tokens[$i+2][1];
                    } else {
                        $currentClass = $currentNamespace . '\\' .
                                        $tokens[$i+2][1];
                    }

                    if ($currentDocComment !== FALSE) {
                        $docComment        = $currentDocComment;
                        $currentDocComment = FALSE;
                    } else {
                        $docComment = '';
                    }

                    $result[$currentClass] = array(
                      'methods'    => array(),
                      'docComment' => $docComment,
                      'startLine'  => $line
                    );
                }
                break;

                case T_FUNCTION: {
                    if (!((is_array($tokens[$i+2]) &&
                          $tokens[$i+2][0] == T_STRING) ||
                         (is_string($tokens[$i+2]) &&
                          $tokens[$i+2] == '&' &&
                          is_array($tokens[$i+3]) &&
                          $tokens[$i+3][0] == T_STRING))) {
                        continue;
                    }

                    $currentBlock             = T_FUNCTION;
                    $currentFunctionStartLine = $line;

                    $done                       = FALSE;
                    $currentSignatureStartToken = $i - 1;

                    do {
                        switch ($tokens[$currentSignatureStartToken][0]) {
                            case T_ABSTRACT:
                            case T_FINAL:
                            case T_PRIVATE:
                            case T_PUBLIC:
                            case T_PROTECTED:
                            case T_STATIC:
                            case T_WHITESPACE: {
                                $currentSignatureStartToken--;
                            }
                            break;

                            default: {
                                $currentSignatureStartToken++;
                                $done = TRUE;
                            }
                        }
                    }
                    while (!$done);

                    if (isset($tokens[$i+2][1])) {
                        $functionName = $tokens[$i+2][1];
                    }

                    else if (isset($tokens[$i+3][1])) {
                        $functionName = $tokens[$i+3][1];
                    }

                    if ($currentNamespace === FALSE) {
                        $currentFunction = $functionName;
                    } else {
                        $currentFunction = $currentNamespace . '\\' .
                                           $functionName;
                    }
                }
                break;

                case T_DOC_COMMENT: {
                    $currentDocComment = $tokens[$i][1];
                }
                break;
            }

            $line += substr_count($tokens[$i][1], "\n");
        }

        return $result;
    }
}
