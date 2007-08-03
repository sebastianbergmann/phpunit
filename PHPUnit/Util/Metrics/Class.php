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
 * @since      File available since Release 3.1.6
 */

require_once 'PHPUnit/Util/Class.php';
require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Util/Metrics/Method.php';

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
 * @since      Class available since Release 3.1.6
 */
class PHPUnit_Util_Metrics_Class
{
    protected $aif = 0;
    protected $ahf = 0;
    protected $dit = 0;
    protected $mif = 0;
    protected $mhf = 0;
    protected $noc = 0;
    protected $pf  = 0;
    protected $wmc = 0;

    protected $class;
    protected $methods = array();

    protected static $cache = array();
    protected static $nocCache = array();

    /**
     * Constructor.
     *
     * @param  ReflectionClass $class
     * @access protected
     */
    protected function __construct(ReflectionClass $class)
    {
        $this->class = $class;

        $this->calculateAIFAHF();
        $this->calculateMIFMHF();
        $this->calculateNOC();
        $this->calculatePF();

        $this->dit = count(PHPUnit_Util_Class::getHierarchy($class->getName()));

        foreach ($class->getMethods() as $method) {
            if ($method->getDeclaringClass()->getName() == $class->getName()) {
                $this->methods[$method->getName()] = PHPUnit_Util_Metrics_Method::factory($method);
            }
        }

        foreach ($this->methods as $method) {
            $this->wmc += $method->getCCN();
        }
    }

    /**
     * Factory.
     *
     * @param  ReflectionClass $class
     * @return PHPUnit_Util_Metrics_Class
     * @access public
     * @static
     */
    public static function factory(ReflectionClass $class)
    {
        $className = $class->getName();

        if (!isset(self::$cache[$className])) {
            self::$cache[$className] = new PHPUnit_Util_Metrics_Class($class);
        }

        return self::$cache[$className];
    }

    /**
     * Returns the Attribute Inheritance Factor (AIF) for the class.
     *
     * @return integer
     * @access public
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
     */
    public function getAHF()
    {
        return $this->ahf;
    }

    /**
     * Returns the Cyclomatic Complexity Number (CCN) for a method.
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
     *
     * Note that 'else', 'default', and 'finally' don't increment the value
     * any further. On the other hand, a simple method with a 'switch'
     * statement and a huge block of 'case 'statements can have a surprisingly
     * high value (still it has the same value when converting a 'switch'
     * block to an equivalent sequence of 'if' statements).
     *
     * @param  string $methodName
     * @return integer
     * @access public
     */
    public function getCCN($methodName)
    {
        if (isset($this->methods[$methodName])) {
            return $this->methods[$methodName]->getCCN();
        } else {
            return false;
        }
    }

    /**
     * Returns the Depth of Inheritance Tree (DIT) for the class.
     *
     * @return integer
     * @access public
     */
    public function getDIT()
    {
        return $this->dit;
    }

    /**
     * Returns the Method Inheritance Factor (MIF) for the class.
     *
     * @return float
     * @access public
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
     */
    public function getPF()
    {
        return $this->pf;
    }

    /**
     * Returns the Weighted Methods Per Class (WMC) for the class.
     *
     * @return integer
     * @access public
     */
    public function getWMC()
    {
        return $this->wmc;
    }

    /**
     * Calculates the Attribute Inheritance Factor (AIF) and
     * Attribute Hiding Factor (AHF) metrics for the class.
     *
     * @access protected
     */
    protected function calculateAIFAHF()
    {
        $attributes          = 0;
        $hiddenAttributes    = 0;
        $inheritedAttributes = 0;

        foreach ($this->class->getProperties() as $attribute) {
            if (!$attribute->isPublic()) {
                $hiddenAttributes++;
            }

            if ($attribute->getDeclaringClass()->getName() != $this->class->getName()) {
                $inheritedAttributes++;
            }

            $attributes++;
        }

        if ($attributes > 0) {
            $this->aif = (100 * $inheritedAttributes) / $attributes;
            $this->ahf = (100 * $hiddenAttributes) / $attributes;
        }
    }

    /**
     * Calculates the Method Inheritance Factor (MIF) and
     * Method Hiding Factor (MHF) metrics for the class.
     *
     * @access protected
     */
    protected function calculateMIFMHF()
    {
        $methods          = 0;
        $hiddenMethods    = 0;
        $inheritedMethods = 0;

        foreach ($this->class->getMethods() as $method) {
            if (!$method->isPublic()) {
                $hiddenMethods++;
            }

            if ($method->getDeclaringClass()->getName() != $this->class->getName()) {
                $inheritedMethods++;
            }

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
    protected function calculateNOC()
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
    protected function calculatePF()
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
}
?>
