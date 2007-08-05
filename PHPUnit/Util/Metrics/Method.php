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
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.2.0
 */

require_once 'PHPUnit/Util/Class.php';
require_once 'PHPUnit/Util/Filter.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Method-Level Metrics.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.2.0
 */
class PHPUnit_Util_Metrics_Method
{
    protected $ccn = 1;

    protected $method;

    protected static $cache = array();

    /**
     * Constructor.
     *
     * @param  ReflectionMethod $method
     * @access protected
     */
    protected function __construct(ReflectionMethod $method)
    {
        $this->method = $method;

        $this->calculateCCN();
    }

    /**
     * Factory.
     *
     * @param  ReflectionMethod $method
     * @return PHPUnit_Util_Metrics_Method
     * @access public
     * @static
     */
    public static function factory(ReflectionMethod $method)
    {
        $className  = $method->getDeclaringClass()->getName();
        $methodName = $method->getName();

        if (!isset(self::$cache[$className][$methodName])) {
            self::$cache[$className][$methodName] = new PHPUnit_Util_Metrics_Method($method);
        }

        return self::$cache[$className][$methodName];
    }

    /**
     * Returns the method.
     *
     * @return ReflectionMethod
     * @access public
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Returns the Cyclomatic Complexity Number (CCN) for the method.
     * This is also known as the McCabe metric.
     *
     * Each method has a minimum value of 1 per default. For each of the
     * following PHP keywords/statements this value gets incremented by one:
     *
     *   - if
     *   - for
     *   - foreach
     *   - while
     *   - case
     *   - catch
     *   - AND, &&
     *   - OR, ||
     *   - ?, :
     *
     * Note that 'else', 'default', and 'finally' don't increment the value
     * any further. On the other hand, a simple method with a 'switch'
     * statement and a huge block of 'case 'statements can have a surprisingly
     * high value (still it has the same value when converting a 'switch'
     * block to an equivalent sequence of 'if' statements).
     *
     * @return integer
     * @access public
     * @see    http://en.wikipedia.org/wiki/Cyclomatic_complexity
     */
    public function getCCN()
    {
        return $this->ccn;
    }

    /**
     * Calculates the Cyclomatic Complexity Number (CCN) for the method.
     *
     * @access protected
     */
    protected function calculateCCN()
    {
        $source = PHPUnit_Util_Class::getMethodSource(
          $this->method->getDeclaringClass()->getName(), $this->method->getName()
        );

        $tokens = token_get_all('<?php' . $source . '?>');

        foreach ($tokens as $i => $token) {
            if (is_string($token)) {
                $token = trim($token);

                if ($token == '?' || $token == ':') {
                    $this->ccn++;
                }

                continue;
            }

            list ($token, $value) = $token;

            switch ($token) {
                case T_IF:
                case T_FOR:
                case T_FOREACH:
                case T_WHILE:
                case T_CASE:
                case T_CATCH:
                case T_BOOLEAN_AND:
                case T_LOGICAL_AND:
                case T_BOOLEAN_OR:
                case T_LOGICAL_OR: {
                    $this->ccn++;
                }
                break;
            }
        }
    }
}
?>
