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
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id: Class.php 827 2007-07-20 10:18:53Z sb $
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.1.6
 */

require_once 'PHPUnit/Util/Filter.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Source file helpers.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.1.6
 */
class PHPUnit_Util_SourceFile
{
    protected $filename;
    protected $lines = array();
    protected $fillup = array();
    protected $tokens = array();
    protected $loc;
    protected $cloc;

    /**
     * Constructor.
     *
     * @param  string $filename
     * @throws RuntimeException
     * @access public
     */
    public function __construct($filename)
    {
        if (is_readable($filename)) {
            $this->filename = $filename;
            $this->lines    = file($filename);
            $this->tokens   = token_get_all(file_get_contents($filename));

            $this->countLines();
        } else {
            throw new RuntimeException;
        }
    }

    /**
     * @return int
     * @access public
     */
    public function getFillup($line)
    {
        return $this->fillup[$line - 1];
    }

    /**
     * Lines.
     *
     * @return array
     * @access public
     */
    public function getLines()
    {
        return $this->lines;
    }

    /**
     * Tokens.
     *
     * @return array
     * @access public
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * Lines of Code (LOC).
     *
     * @return int
     * @access public
     */
    public function getLoc()
    {
        return $this->loc;
    }

    /**
     * Comment Lines of Code (CLOC).
     *
     * @return int
     * @access public
     */
    public function getCloc()
    {
        return $this->cloc;
    }

    /**
     * Non-Comment Lines of Code (NCLOC).
     *
     * @return int
     * @access public
     */
    public function getNcloc()
    {
        return $this->loc - $this->cloc;
    }

    /**
     * @author Aidan Lister <aidan@php.net>
     * @author Sebastian Bergmann <sb@sebastian-bergmann.de>
     * @return array
     * @access public
     */
    public function highlight()
    {
        $lines      = $this->lines;
        $stringFlag = FALSE;
        $i          = 0;
        $result     = array();
        $result[$i] = '';

        foreach ($this->tokens as $j => $token) {
            if (is_string($token)) {
                if ($token === '"' && $this->tokens[$j - 1] !== '\\') {
                    $result[$i] .= sprintf(
                      '<span class="string">%s</span>',

                      htmlspecialchars($token)
                    );

                    $stringFlag = !$stringFlag;   
                } else {
                    $result[$i] .= sprintf(
                      '<span class="keyword">%s</span>',

                      htmlspecialchars($token)
                    );
                }

                continue;
            }

            list ($token, $value) = $token;

            $value = str_replace(
              array("\t", ' '),
              array('&nbsp;&nbsp;&nbsp;&nbsp;', '&nbsp;'),
              htmlspecialchars($value)
            );

            if ($value === "\n") {
                $result[++$i] = '';
            } else {
                $lines = explode("\n", $value);              

                foreach ($lines as $jj => $line) {
                    $line = trim($line);

                    if ($line !== '') {
                        if ($stringFlag) {
                            $colour = 'string';
                        } else {
                            $colour = $this->tokenToColor($token);
                        }

                        $result[$i] .= sprintf(
                          '<span class="%s">%s</span>',

                          $colour,
                          $line
                        );
                    }

                    if (isset($lines[$jj + 1])) {
                        $result[++$i] = '';
                    }
                }
            }
        }

        unset($result[count($result)-1]);

        return $result;
    }

    /**
     * @access protected
     */
    protected function countLines()
    {
        $this->loc  = count($this->lines);
        $this->cloc = 0;
        $width      = 0;

        for ($i = 0; $i < $this->loc; $i++) {
            $lines[$i] = rtrim($this->lines[$i]);

            if (strlen($this->lines[$i]) > $width) {
                $width = strlen($this->lines[$i]);
            }
        }

        for ($i = 0; $i < $this->loc; $i++) {
            $this->fillup[$i] = $width - strlen($this->lines[$i]);
        }

        foreach ($this->tokens as $i => $token) {
            if (is_string($token)) {
                continue;
            }

            list ($token, $value) = $token;

            if ($token == T_COMMENT || $token == T_DOC_COMMENT) {
                $this->cloc += count(explode("\n", $value));
            }
        }
    }

    /**
     * @author Aidan Lister <aidan@php.net>
     * @author Sebastian Bergmann <sb@sebastian-bergmann.de>
     * @param  string $token
     * @return string
     * @access protected
     */
    protected function tokenToColor($token)
    {
        switch ($token) {
            case T_CONSTANT_ENCAPSED_STRING: return 'string';
            case T_INLINE_HTML: return 'html';
            case T_COMMENT:
            case T_DOC_COMMENT: return 'comment';
            case T_ABSTRACT:
            case T_ARRAY:
            case T_ARRAY_CAST:
            case T_AS:
            case T_BOOLEAN_AND:
            case T_BOOLEAN_OR:
            case T_BOOL_CAST:
            case T_BREAK:
            case T_CASE:
            case T_CATCH:
            case T_CLASS:
            case T_CLONE:
            case T_CONCAT_EQUAL:
            case T_CONTINUE:
            case T_DEFAULT:
            case T_DOUBLE_ARROW:
            case T_DOUBLE_CAST:
            case T_ECHO:
            case T_ELSE:
            case T_ELSEIF:
            case T_EMPTY:
            case T_ENDDECLARE:
            case T_ENDFOR:
            case T_ENDFOREACH:
            case T_ENDIF:
            case T_ENDSWITCH:
            case T_ENDWHILE:
            case T_END_HEREDOC:
            case T_EXIT:
            case T_EXTENDS:
            case T_FINAL:
            case T_FOREACH:
            case T_FUNCTION:
            case T_GLOBAL:
            case T_IF:
            case T_INC:
            case T_INCLUDE:
            case T_INCLUDE_ONCE:
            case T_INSTANCEOF:
            case T_INT_CAST:
            case T_ISSET:
            case T_IS_EQUAL:
            case T_IS_IDENTICAL:
            case T_IS_NOT_IDENTICAL:
            case T_IS_SMALLER_OR_EQUAL:
            case T_NEW:
            case T_OBJECT_CAST:
            case T_OBJECT_OPERATOR:
            case T_PAAMAYIM_NEKUDOTAYIM:
            case T_PRIVATE:
            case T_PROTECTED:
            case T_PUBLIC:
            case T_REQUIRE:
            case T_REQUIRE_ONCE:
            case T_RETURN:
            case T_SL:
            case T_SL_EQUAL:
            case T_SR:
            case T_SR_EQUAL:
            case T_START_HEREDOC:
            case T_STATIC:
            case T_STRING_CAST:
            case T_THROW:
            case T_TRY:
            case T_UNSET_CAST:
            case T_VAR:
            case T_WHILE: return 'keyword';
            case T_CLOSE_TAG:
            case T_OPEN_TAG:
            case T_OPEN_TAG_WITH_ECHO:
            default: return 'default';
        }
    }
}
?>
