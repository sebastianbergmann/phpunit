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
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2008 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
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
 * @copyright  2002-2008 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.2.0
 */
class PHPUnit_Util_Metrics_Function extends PHPUnit_Util_Metrics
{
    protected $coverage      = 0;
    protected $loc           = 0;
    protected $locExecutable = 0;
    protected $locExecuted   = 0;

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
            $this->tokens = token_get_all('<?php' . $source . '?>');
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
     * Returns the Code Coverage for the method.
     *
     * @return float
     */
    public function getCoverage()
    {
        return $this->coverage;
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
}
?>
