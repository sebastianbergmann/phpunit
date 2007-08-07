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
require_once 'PHPUnit/Util/Metrics/Function.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Class-Level Metrics.
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
class PHPUnit_Util_Metrics_Class
{
    protected $aif           = 0;
    protected $ahf           = 0;
    protected $coverage      = 0;
    protected $dit           = 0;
    protected $impl          = 0;
    protected $loc           = 0;
    protected $locExecutable = 0;
    protected $locExecuted   = 0;
    protected $mif           = 0;
    protected $mhf           = 0;
    protected $noc           = 0;
    protected $pf            = 0;
    protected $vars          = 0;
    protected $varsNp        = 0;
    protected $varsI         = 0;
    protected $wmc           = 0;
    protected $wmcNp         = 0;
    protected $wmcI          = 0;

    protected $class;
    protected $methods = array();
    protected $publicMethods = 0;

    protected static $cache = array();
    protected static $nocCache = array();

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

        $this->calculateAttributeMetrics();
        $this->calculateMethodMetrics();
        $this->calculateNumberOfChildren();
        $this->calculatePolymorphismFactor();
        $this->calculateCodeCoverage($codeCoverage);

        $this->dit  = count(PHPUnit_Util_Class::getHierarchy($class->getName()));
        $this->impl = count($class->getInterfaces());

        $className = $class->getName();

        foreach ($this->class->getMethods() as $method) {
            if ($method->getDeclaringClass()->getName() == $className) {
                $this->methods[$method->getName()] = PHPUnit_Util_Metrics_Function::factory($method, $codeCoverage);
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

        if (!isset(self::$cache[$className]) ||
           (!empty($codeCoverage) && self::$cache[$className]->coverage == 0)) {
            self::$cache[$className] = new PHPUnit_Util_Metrics_Class($class, $codeCoverage);
        }

        return self::$cache[$className];
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
     * Returns the Attribute Inheritance Factor (AIF) for the class.
     *
     * @return integer
     * @access public
     * @see    http://www.aivosto.com/project/help/pm-oo-mood.html
     */
    public function getAIF()
    {
        return $this->aif;
    }

    /**
     * Returns the Attribute Hiding Factor (AHF) for the class.
     *
     * @return integer
     * @access public
     * @see    http://www.aivosto.com/project/help/pm-oo-mood.html
     */
    public function getAHF()
    {
        return $this->ahf;
    }

    /**
     * Returns the Class Size (CSZ) of the class.
     *
     * @return integer
     * @access public
     * @see    http://www.aivosto.com/project/help/pm-oo-misc.html
     */
    public function getCSZ()
    {
        return count($this->methods) + $this->vars;
    }

    /**
     * Returns the Class Interface Size (CIS) of the class.
     *
     * @return integer
     * @access public
     * @see    http://www.aivosto.com/project/help/pm-oo-misc.html
     */
    public function getCIS()
    {
        return $this->publicMethods + $this->varsNp;
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
     * Returns the Depth of Inheritance Tree (DIT) for the class.
     *
     * @return integer
     * @access public
     * @see    http://www.aivosto.com/project/help/pm-oo-ck.html
     */
    public function getDIT()
    {
        return $this->dit;
    }

    /**
     * Returns the Number of Interfaces Implemented by the class (IMPL).
     *
     * @return integer
     * @access public
     * @see    http://www.aivosto.com/project/help/pm-oo-misc.html
     */
    public function getIMPL()
    {
        return $this->impl;
    }

    /**
     * Returns the Method Inheritance Factor (MIF) for the class.
     *
     * @return float
     * @access public
     * @see    http://www.aivosto.com/project/help/pm-oo-mood.html
     */
    public function getMIF()
    {
        return $this->mif;
    }

    /**
     * Returns the Method Hiding Factor (MHF) for the class.
     *
     * @return float
     * @access public
     * @see    http://www.aivosto.com/project/help/pm-oo-mood.html
     */
    public function getMHF()
    {
        return $this->mhf;
    }

    /**
     * Returns the Number of Children (NOC) for the class.
     *
     * @return integer
     * @access public
     * @see    http://www.aivosto.com/project/help/pm-oo-ck.html
     */
    public function getNOC()
    {
        return $this->noc;
    }

    /**
     * Returns the Polymorphism Factor (PF) for the class.
     *
     * @return float
     * @access public
     * @see    http://www.aivosto.com/project/help/pm-oo-mood.html
     */
    public function getPF()
    {
        return $this->pf;
    }

    /**
     * Returns the Number of Variables (VARS) defined by the class.
     *
     * @return integer
     * @access public
     * @see    http://www.aivosto.com/project/help/pm-oo-misc.html
     */
    public function getVARS()
    {
        return $this->vars;
    }

    /**
     * Returns the Number of Non-Private Variables (VARSnp) defined by the class.
     *
     * @return integer
     * @access public
     * @see    http://www.aivosto.com/project/help/pm-oo-misc.html
     */
    public function getVARSnp()
    {
        return $this->varsNp;
    }

    /**
     * Returns the Number of Variables (VARSi) defined and inherited by the class.
     *
     * @return integer
     * @access public
     * @see    http://www.aivosto.com/project/help/pm-oo-misc.html
     */
    public function getVARSi()
    {
        return $this->varsI;
    }

    /**
     * Returns the Weighted Methods Per Class (WMC) for the class.
     *
     * @return integer
     * @access public
     * @see    http://www.aivosto.com/project/help/pm-oo-ck.html
     */
    public function getWMC()
    {
        return $this->wmc;
    }

    /**
     * Returns the Weighted Non-Private Methods Per Class (WMCnp) for the class.
     *
     * @return integer
     * @access public
     * @see    http://www.aivosto.com/project/help/pm-oo-misc.html
     */
    public function getWMCnp()
    {
        return $this->wmcNp;
    }

    /**
     * Returns the Weighted Inherited Methods Per Class (WMCi) for the class.
     *
     * @return integer
     * @access public
     * @see    http://www.aivosto.com/project/help/pm-oo-misc.html
     */
    public function getWMCi()
    {
        return $this->wmcI;
    }

    /**
     * Calculates the Attribute Inheritance Factor (AIF) and
     * Attribute Hiding Factor (AHF) metrics for the class.
     *
     * @access protected
     */
    protected function calculateAttributeMetrics()
    {
        $attributes          = 0;
        $hiddenAttributes    = 0;
        $inheritedAttributes = 0;

        foreach ($this->class->getProperties() as $attribute) {
            if ($attribute->isPublic()) {
                $this->varsNp++;
            } else {
                $hiddenAttributes++;
            }

            if ($attribute->getDeclaringClass()->getName() == $this->class->getName()) {
                $this->vars++;
            } else {
                $inheritedAttributes++;
            }

            $this->varsI++;
            $attributes++;
        }

        if ($attributes > 0) {
            $this->aif = (100 * $inheritedAttributes) / $attributes;
            $this->ahf = (100 * $hiddenAttributes) / $attributes;
        }
    }

    /**
     * Calculates the Method Inheritance Factor (MIF)
     * Method Hiding Factor (MHF), Weighted Methods Per Class (WMC),
     * Weighted Non-Private Methods Per Class (WMCnp), and
     * Weighted Inherited Methods Per Class (WMCi) metrics for the class.
     *
     * @access protected
     */
    protected function calculateMethodMetrics()
    {
        $methods          = 0;
        $hiddenMethods    = 0;
        $inheritedMethods = 0;

        foreach ($this->methods as $method) {
            $ccn = $method->getCCN();

            if ($method->getMethod()->getDeclaringClass()->getName() == $this->class->getName()) {
                $this->wmc += $ccn;

                if ($method->isPublic()) {
                    $this->publicMethods++;
                    $this->wmcNp += $ccn;
                }
            } else {
                $inheritedMethods++;
            }

            if (!$method->isPublic()) {
                $hiddenMethods++;
            }

            $this->wmcI += $ccn;
            $methods++;
        }

        if ($methods > 0) {
            $this->mif = (100 * $inheritedMethods) / $methods;
            $this->mhf = (100 * $hiddenMethods) / $methods;
        }
    }

    /**
     * Calculates the Number of Children (NOC) metric for the class.
     *
     * @access protected
     */
    protected function calculateNumberOfChildren()
    {
        $className = $this->class->getName();

        if (!isset(self::$nocCache[$className])) {
            self::$nocCache = array();
        }

        if (empty(self::$nocCache)) {
            foreach (get_declared_classes() as $_className) {
                $class  = new ReflectionClass($_className);
                $parent = $class->getParentClass();

                if ($parent !== FALSE) {
                    $parentName = $parent->getName();

                    if (isset(self::$nocCache[$parentName])) {
                        self::$nocCache[$parentName]++;
                    } else {
                        self::$nocCache[$parentName] = 1;
                    }
                }
            }
        }

        if (isset(self::$nocCache[$className])) {
            $this->noc = self::$nocCache[$className];
        }
    }

    /**
     * Calculates the Polymorphism Factor (PF) metric for the class.
     *
     * @param  ReflectionClass $class
     * @access protected
     */
    protected function calculatePolymorphismFactor()
    {
        $parentClass = $this->class->getParentClass();

        if ($parentClass !== FALSE) {
            $overridableMethods = array();

            foreach ($parentClass->getMethods() as $method) {
                if (!$method->isPrivate() && !$method->isFinal() && !$method->isAbstract()) {
                    $overridableMethods[] = $method->getName();
                }
            }

            if (!empty($overridableMethods)) {
                $overriddenMethods = 0;

                foreach ($this->class->getMethods() as $method) {
                    if ($method->getDeclaringClass()->getName() == $this->class->getName()) {
                        $methodName = $method->getName();

                        if (in_array($methodName, $overridableMethods)) {
                            $overriddenMethods++;
                        }
                    }
                }

                $this->pf = (100 * $overriddenMethods) / count($overridableMethods);
            }
        }
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

        $this->coverage       = $statistics['coverage'];
        $this->loc            = $statistics['loc'];
        $this->locExecutable  = $statistics['locExecutable'];
        $this->loclocExecuted = $statistics['locExecuted'];
    }
}
?>
