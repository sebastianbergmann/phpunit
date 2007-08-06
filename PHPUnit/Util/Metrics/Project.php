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

require_once 'PHPUnit/Util/Metrics/File.php';
require_once 'PHPUnit/Util/Filter.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Project-Level Metrics.
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
class PHPUnit_Util_Metrics_Project
{
    protected $classes   = array();
    protected $files     = array();
    protected $functions = array();

    protected $cls     = 0;
    protected $clsa    = 0;
    protected $clsc    = 0;
    protected $interfs = 0;
    protected $roots   = 0;
    protected $leafs   = 0;
    protected $maxDit  = 0;

    /**
     * Constructor.
     *
     * @param  array $files
     * @param  array $codeCoverage
     * @access public
     */
    public function __construct(Array $files, &$codeCoverage = array())
    {
        foreach ($files as $file) {
            $this->files[$file] = PHPUnit_Util_Metrics_File::factory($file, $codeCoverage);

            foreach ($this->files[$file]->getFunctions() as $function) {
                $this->functions[$function->getFunction()->getName()] = $function;
            }

            foreach ($this->files[$file]->getClasses() as $class) {
                $className = $class->getClass()->getName();

                $this->classes[$className] = $class;

                if ($class->getClass()->isInterface()) {
                    $this->interfs++;
                } else {
                    if ($class->getClass()->isAbstract()) {
                        $this->clsa++;
                    } else {
                        $this->clsc++;
                    }

                    $this->cls++;
                }
            }
        }

        foreach ($this->classes as $class) {
            if ($class->getNOC() == 0) {
                $this->leafs++;
            }

            else if ($class->getClass()->getParentClass() === FALSE) {
                $this->roots++;
            }

            $this->maxDit = max($this->maxDit, $class->getDit());
        }
    }

    /**
     * Returns the classes of this project.
     *
     * @return array
     * @access public
     */
    public function getClasses()
    {
        return $this->classes;
    }

    /**
     * A class.
     *
     * @param  string $className
     * @return ReflectionClass
     * @access public
     */
    public function getClass($className)
    {
        return $this->classes[$className];
    }

    /**
     * Returns the files of this project.
     *
     * @return array
     * @access public
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * A file.
     *
     * @param  string $className
     * @return ReflectionClass
     * @access public
     */
    public function getFile($filename)
    {
        return $this->files[$filename];
    }

    /**
     * Functions.
     *
     * @return array
     * @access public
     */
    public function getFunctions()
    {
        return $this->functions;
    }

    /**
     * A function.
     *
     * @param  string $functionName
     * @return ReflectionClass
     * @access public
     */
    public function getFunction($functionName)
    {
        return $this->functions[$functionName];
    }

    /**
     * Returns the Number of Classes (CLS) for the project.
     *
     * @return integer
     * @access public
     * @see    http://www.aivosto.com/project/help/pm-oo-misc.html
     */
    public function getCLS()
    {
        return $this->cls;
    }

    /**
     * Returns the Number of Abstract Classes (CLSa) for the project.
     *
     * @return integer
     * @access public
     * @see    http://www.aivosto.com/project/help/pm-oo-misc.html
     */
    public function getCLSa()
    {
        return $this->clsa;
    }

    /**
     * Returns the Number of Concrete Classes (CLSc) for the project.
     *
     * @return integer
     * @access public
     * @see    http://www.aivosto.com/project/help/pm-oo-misc.html
     */
    public function getCLSc()
    {
        return $this->clsc;
    }

    /**
     * Returns the Number of Root Classes (ROOTS) for the project.
     *
     * @return integer
     * @access public
     * @see    http://www.aivosto.com/project/help/pm-oo-misc.html
     */
    public function getRoots()
    {
        return $this->roots;
    }

    /**
     * Returns the Number of Leaf Classes (LEAFS) for the project.
     *
     * @return integer
     * @access public
     * @see    http://www.aivosto.com/project/help/pm-oo-misc.html
     */
    public function getLeafs()
    {
        return $this->leafs;
    }

    /**
     * Returns the Number of Interfaces (INTERFS) for the project.
     *
     * @return integer
     * @access public
     * @see    http://www.aivosto.com/project/help/pm-oo-misc.html
     */
    public function getInterfs()
    {
        return $this->interfs;
    }

    /**
     * Returns the Maximum Depth of Intheritance Tree (maxDIT) for the project.
     *
     * @return integer
     * @access public
     * @see    http://www.aivosto.com/project/help/pm-oo-misc.html
     */
    public function getMaxDit()
    {
        return $this->maxDit;
    }
}
?>
