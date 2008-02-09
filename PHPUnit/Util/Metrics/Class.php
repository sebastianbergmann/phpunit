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
require_once 'PHPUnit/Util/Metrics.php';
require_once 'PHPUnit/Util/Filter.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Class-Level Metrics.
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
class PHPUnit_Util_Metrics_Class extends PHPUnit_Util_Metrics
{
    protected $coverage      = 0;
    protected $loc           = 0;
    protected $locExecutable = 0;
    protected $locExecuted   = 0;

    protected $class;
    protected $methods = array();
    protected $inheritedMethods = array();

    protected static $cache = array();

    /**
     * Constructor.
     *
     * @param  ReflectionClass $class
     * @param  array           $codeCoverage
     * @access protected
     */
    protected function __construct(ReflectionClass $class, &$codeCoverage = array())
    {
        $this->class = $class;

        $className = $class->getName();

        $this->setCoverage($codeCoverage);

        $this->dit  = count(PHPUnit_Util_Class::getHierarchy($class->getName())) - 1;
        $this->impl = count($class->getInterfaces());

        foreach ($this->class->getMethods() as $method) {
            if ($method->getDeclaringClass()->getName() == $className) {
                $this->methods[$method->getName()] = PHPUnit_Util_Metrics_Function::factory($method, $codeCoverage);
            } else {
                $this->inheritedMethods[$method->getName()] = PHPUnit_Util_Metrics_Function::factory($method, $codeCoverage);
            }
        }
    }

    /**
     * Factory.
     *
     * @param  ReflectionClass $class
     * @param  array           $codeCoverage
     * @return PHPUnit_Util_Metrics_Class
     * @access public
     * @static
     */
    public static function factory(ReflectionClass $class, &$codeCoverage = array())
    {
        $className = $class->getName();

        if (!isset(self::$cache[$className])) {
            self::$cache[$className] = new PHPUnit_Util_Metrics_Class($class, $codeCoverage);
        }

        else if (!empty($codeCoverage) && self::$cache[$className]->getCoverage() == 0) {
            self::$cache[$className]->setCoverage($codeCoverage);
        }

        return self::$cache[$className];
    }

    /**
     * @param  array $codeCoverage
     * @access public
     */
    public function setCoverage(array &$codeCoverage)
    {
        if (!empty($codeCoverage)) {
            $this->calculateCodeCoverage($codeCoverage);

            foreach ($this->methods as $method) {
                $method->setCoverage($codeCoverage);
            }
        }
    }

    /**
     * Returns the class.
     *
     * @return ReflectionClass
     * @access public
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Returns the methods of this class.
     *
     * @return array
     * @access public
     */
    public function getMethods()
    {
        return $this->methods;
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
     * Executable Lines of Code (ELOC).
     *
     * @return int
     * @access public
     */
    public function getLocExecutable()
    {
        return $this->locExecutable;
    }

    /**
     * Executed Lines of Code.
     *
     * @return int
     * @access public
     */
    public function getLocExecuted()
    {
        return $this->locExecuted;
    }

    /**
     * Returns the Code Coverage for the class.
     *
     * @return float
     * @access public
     */
    public function getCoverage()
    {
        return $this->coverage;
    }

    /**
     * Calculates the Code Coverage for the class.
     *
     * @param  array $codeCoverage
     * @access protected
     */
    protected function calculateCodeCoverage(&$codeCoverage)
    {
        $statistics = PHPUnit_Util_CodeCoverage::getStatistics(
          $codeCoverage,
          $this->class->getFileName(),
          $this->class->getStartLine(),
          $this->class->getEndLine()
        );

        $this->coverage      = $statistics['coverage'];
        $this->loc           = $statistics['loc'];
        $this->locExecutable = $statistics['locExecutable'];
        $this->locExecuted   = $statistics['locExecuted'];
    }
}
?>
