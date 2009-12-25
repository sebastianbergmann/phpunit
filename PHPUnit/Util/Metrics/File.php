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

require_once 'PHPUnit/Util/File.php';
require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Util/Metrics.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * File-Level Metrics.
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
class PHPUnit_Util_Metrics_File extends PHPUnit_Util_Metrics
{
    protected $coverage      = 0;
    protected $loc           = 0;
    protected $cloc          = 0;
    protected $ncloc         = 0;
    protected $locExecutable = 0;
    protected $locExecuted   = 0;

    protected $filename;
    protected $classes = array();
    protected $functions = array();
    protected $lines = array();
    protected $tokens = array();

    protected static $cache = array();

    /**
     * Constructor.
     *
     * @param  string $filename
     * @param  array  $codeCoverage
     * @throws RuntimeException
     */
    protected function __construct($filename, &$codeCoverage = array())
    {
        if (!file_exists($filename)) {
            throw new RuntimeException(
              sprintf(
                'File "%s" not found.',
                $filename
              )
            );
        }

        $this->filename = $filename;
        $this->lines    = file($filename);
        $this->tokens   = token_get_all(file_get_contents($filename));

        $this->countLines();
        $this->setCoverage($codeCoverage);

        foreach (PHPUnit_Util_File::getClassesInFile($filename) as $className => $class) {
            $this->classes[$className] = PHPUnit_Util_Metrics_Class::factory(
              new ReflectionClass($className), $codeCoverage
            );
        }

        foreach (PHPUnit_Util_File::getFunctionsInFile($filename) as $functionName => $function) {
            $this->functions[$functionName] = PHPUnit_Util_Metrics_Function::factory(
              new ReflectionFunction($functionName), $codeCoverage
            );
        }
    }

    /**
     * Factory.
     *
     * @param  string $filename
     * @param  array  $codeCoverage
     * @return PHPUnit_Util_Metrics_File
     */
    public static function factory($filename, &$codeCoverage = array())
    {
        if (!isset(self::$cache[$filename])) {
            self::$cache[$filename] = new PHPUnit_Util_Metrics_File($filename, $codeCoverage);
        }

        else if (!empty($codeCoverage) && self::$cache[$filename]->getCoverage() == 0) {
            self::$cache[$filename]->setCoverage($codeCoverage);
        }

        return self::$cache[$filename];
    }

    /**
     * @param  array $codeCoverage
     */
    public function setCoverage(array &$codeCoverage)
    {
        if (!empty($codeCoverage)) {
            $this->calculateCodeCoverage($codeCoverage);

            foreach ($this->classes as $class) {
                $class->setCoverage($codeCoverage);
            }

            foreach ($this->functions as $function) {
                $function->setCoverage($codeCoverage);
            }
        }
    }

    /**
     * Returns the path to the file.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->filename;
    }

    /**
     * Classes.
     *
     * @return array
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
     */
    public function getClass($className)
    {
        return $this->classes[$className];
    }

    /**
     * Functions.
     *
     * @return array
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
     */
    public function getFunction($functionName)
    {
        return $this->functions[$functionName];
    }

    /**
     * Lines.
     *
     * @return array
     */
    public function getLines()
    {
        return $this->lines;
    }

    /**
     * Tokens.
     *
     * @return array
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * Returns the Code Coverage for the file.
     *
     * @return float
     */
    public function getCoverage()
    {
        return $this->coverage;
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
     * Comment Lines of Code (CLOC).
     *
     * @return int
     */
    public function getCloc()
    {
        return $this->cloc;
    }

    /**
     * Non-Comment Lines of Code (NCLOC).
     *
     * @return int
     */
    public function getNcloc()
    {
        return $this->ncloc;
    }

    /**
     * Calculates the Code Coverage for the class.
     *
     * @param  array $codeCoverage
     */
    protected function calculateCodeCoverage(&$codeCoverage)
    {
        $statistics = PHPUnit_Util_CodeCoverage::getStatistics(
          $codeCoverage,
          $this->filename,
          1,
          count($this->lines)
        );

        $this->coverage      = $statistics['coverage'];
        $this->loc           = $statistics['loc'];
        $this->locExecutable = $statistics['locExecutable'];
        $this->locExecuted   = $statistics['locExecuted'];
    }

    /**
     */
    protected function countLines()
    {
        $this->loc  = count($this->lines);
        $this->cloc = 0;

        foreach ($this->tokens as $i => $token) {
            if (is_string($token)) {
                continue;
            }

            list ($token, $value) = $token;

            if ($token == T_COMMENT || $token == T_DOC_COMMENT) {
                $this->cloc += count(explode("\n", $value));
            }
        }

        $this->ncloc = $this->loc - $this->cloc;
    }
}
?>
