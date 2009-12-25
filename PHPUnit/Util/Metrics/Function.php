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
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.2.0
 */

require_once 'PHPUnit/Util/Class.php';
require_once 'PHPUnit/Util/Filter.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Function- and Method-Level Metrics.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2009 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.2.0
 */
class PHPUnit_Util_Metrics_Function extends PHPUnit_Util_Metrics
{
    protected $ccn           = 1;
    protected $npath         = 1;
    protected $coverage      = 0;
    protected $crap;
    protected $loc           = 0;
    protected $locExecutable = 0;
    protected $locExecuted   = 0;
    protected $parameters    = 0;

    protected $function;
    protected $scope;
    protected $tokens;

    protected $dependencies = array();

    protected static $cache = array();

    /**
     * Constructor.
     *
     * @param  string                              $scope
     * @param  ReflectionFunction|ReflectionMethod $function
     * @param  array                               $codeCoverage
     */
    protected function __construct($scope, $function, &$codeCoverage = array())
    {
        $this->scope    = $scope;
        $this->function = $function;

        $source = PHPUnit_Util_Class::getMethodSource(
          $scope, $function->getName()
        );

        if ($source !== FALSE) {
            $this->tokens     = token_get_all('<?php' . $source . '?>');
            $this->parameters = $function->getNumberOfParameters();

            $this->calculateCCN();
            $this->calculateNPath();
            $this->calculateDependencies();
        }

        $this->setCoverage($codeCoverage);
    }

    /**
     * Factory.
     *
     * @param  ReflectionFunction|ReflectionMethod $function
     * @param  array                               $codeCoverage
     * @return PHPUnit_Util_Metrics_Method
     */
    public static function factory($function, &$codeCoverage = array())
    {
        if ($function instanceof ReflectionMethod) {
            $scope = $function->getDeclaringClass()->getName();
        } else {
            $scope = 'global';
        }

        $name = $function->getName();

        if (!isset(self::$cache[$scope][$name])) {
            self::$cache[$scope][$name] = new PHPUnit_Util_Metrics_Function($scope, $function, $codeCoverage);
        }

        else if (!empty($codeCoverage) && self::$cache[$scope][$name]->getCoverage() == 0) {
            self::$cache[$scope][$name]->setCoverage($codeCoverage);
        }

        return self::$cache[$scope][$name];
    }

    /**
     * @param  array $codeCoverage
     */
    public function setCoverage(array &$codeCoverage)
    {
        if (!empty($codeCoverage)) {
            $this->calculateCodeCoverage($codeCoverage);
            $this->calculateCrapIndex();
        }
    }

    /**
     * Returns the function.
     *
     * @return ReflectionFunction
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * Returns the method.
     * Alias for getFunction().
     *
     * @return ReflectionMethod
     */
    public function getMethod()
    {
        return $this->function;
    }

    /**
     * Returns the names of the classes this function or method depends on.
     *
     * @return array
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     * Lines of Code (LOC).
     *
     * @return int
     */
    public function getLoc()
    {
        return $this->loc;
    }

    /**
     * Executable Lines of Code (ELOC).
     *
     * @return int
     */
    public function getLocExecutable()
    {
        return $this->locExecutable;
    }

    /**
     * Executed Lines of Code.
     *
     * @return int
     */
    public function getLocExecuted()
    {
        return $this->locExecuted;
    }

    /**
     * Number of Parameters.
     *
     * @return int
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Returns the Cyclomatic Complexity Number (CCN) for the method.
     * This is also known as the McCabe metric.
     *
     * Each method has a minimum value of 1 per default. For each of the
     * following PHP keywords/statements this value gets incremented by one:
     *
     *   - if
     *   - elseif
     *   - for
     *   - foreach
     *   - while
     *   - case
     *   - catch
     *   - AND, &&
     *   - OR, ||
     *   - ?
     *
     * Note that 'else', 'default', and 'finally' don't increment the value
     * any further. On the other hand, a simple method with a 'switch'
     * statement and a huge block of 'case 'statements can have a surprisingly
     * high value (still it has the same value when converting a 'switch'
     * block to an equivalent sequence of 'if' statements).
     *
     * @return integer
     * @see    http://en.wikipedia.org/wiki/Cyclomatic_complexity
     */
    public function getCCN()
    {
        return $this->ccn;
    }

    /**
     * Returns the Change Risk Analysis and Predictions (CRAP) index for the
     * method.
     *
     * @return float
     * @see    http://www.artima.com/weblogs/viewpost.jsp?thread=210575
     */
    public function getCrapIndex()
    {
        return $this->crap;
    }

    /**
     * Returns the Code Coverage for the method.
     *
     * @return float
     */
    public function getCoverage()
    {
        return $this->coverage;
    }

    /**
     * Returns the NPath Complexity for the method.
     *
     * @return integer
     */
    public function getNPath()
    {
        return $this->npath;
    }

    /**
     * Calculates the Cyclomatic Complexity Number (CCN) for the method.
     *
     */
    protected function calculateCCN()
    {
        foreach ($this->tokens as $token) {
            if (is_string($token)) {
                $token = trim($token);

                if ($token == '?') {
                    $this->ccn++;
                }

                continue;
            }

            list ($token, $value) = $token;

            switch ($token) {
                case T_IF:
                case T_ELSEIF:
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

    /**
     * Calculates the NPath Complexity for the method.
     *
     */
    protected function calculateNPath()
    {
        $npathStack = array();
        $stack      = array();

        foreach ($this->tokens as $token) {
            if (is_string($token)) {
                $token = trim($token);

                if ($token == '?') {
                    $this->npath = ($this->npath + 1) * $this->npath;
                }

                if ($token == '{') {
                    if (isset($scope)) {
                        array_push($stack, $scope);
                        array_push($npathStack, $this->npath);
                        $this->npath = 1;
                    } else {
                        array_push($stack, NULL);
                    }
                }

                if ($token == '}') {
                    $scope = array_pop($stack);

                    if ($scope !== NULL) {
                        switch ($scope) {
                            case T_WHILE:
                            case T_DO:
                            case T_FOR:
                            case T_FOREACH:
                            case T_IF:
                            case T_TRY:
                            case T_SWITCH: {
                                $this->npath = ($this->npath + 1) * array_pop($npathStack);
                            }
                            break;

                            case T_ELSE:
                            case T_CATCH:
                            case T_CASE: {
                                $this->npath = ($this->npath - 1) + array_pop($npathStack);
                            }
                            break;
                        }
                    }
                }

                continue;
            }

            list ($token, $value) = $token;

            switch ($token) {
                case T_WHILE:
                case T_DO:
                case T_FOR:
                case T_FOREACH:
                case T_IF:
                case T_TRY:
                case T_SWITCH:
                case T_ELSE:
                case T_CATCH:
                case T_CASE: {
                    $scope = $token;
                }
                break;
            }
        }
    }

    /**
     * Calculates the Code Coverage for the method.
     *
     * @param  array $codeCoverage
     */
    protected function calculateCodeCoverage(&$codeCoverage)
    {
        $statistics = PHPUnit_Util_CodeCoverage::getStatistics(
          $codeCoverage,
          $this->function->getFileName(),
          $this->function->getStartLine(),
          $this->function->getEndLine()
        );

        $this->coverage      = $statistics['coverage'];
        $this->loc           = $statistics['loc'];
        $this->locExecutable = $statistics['locExecutable'];
        $this->locExecuted   = $statistics['locExecuted'];
    }

    /**
     * Calculates the Change Risk Analysis and Predictions (CRAP) index for the
     * method.
     *
     */
    protected function calculateCrapIndex()
    {
        if ($this->coverage == 0) {
            $this->crap = pow($this->ccn, 2) + $this->ccn;
        }

        else if ($this->coverage >= 95) {
            $this->crap = $this->ccn;
        }

        else {
            $this->crap = pow($this->ccn, 2) * pow(1 - $this->coverage/100, 3) + $this->ccn;
        }
    }

    /**
     * Calculates the dependencies for this function or method.
     *
     */
    protected function calculateDependencies()
    {
        foreach ($this->function->getParameters() as $parameter) {
            try {
                $class = $parameter->getClass();

                if ($class) {
                    $className = $class->getName();

                    if ($className != $this->scope && !in_array($className, $this->dependencies)) {
                        $this->dependencies[] = $className;
                    }
                }
            }

            catch (ReflectionException $e) {
            }
        }

        $inNew = FALSE;

        foreach ($this->tokens as $token) {
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
                        if ($value != $this->scope && class_exists($value, FALSE)) {
                            try {
                                $class = new ReflectionClass($value);

                                if ($class->isUserDefined() && !in_array($value, $this->dependencies)) {
                                    $this->dependencies[] = $value;
                                }
                            }

                            catch (ReflectionException $e) {
                            }
                        }
                    }

                    $inNew = FALSE;
                }
                break;
            }
        }
    }
}
?>
