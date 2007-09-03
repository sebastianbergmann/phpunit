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

require_once 'PHPUnit/Runner/Version.php';
require_once 'PHPUnit/Util/Metrics/Project.php';
require_once 'PHPUnit/Util/Class.php';
require_once 'PHPUnit/Util/CodeCoverage.php';
require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Util/Printer.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Generates an XML logfile with software metrics information using the
 * PMD format "documented" at
 * http://svn.atlassian.com/fisheye/browse/~raw,r=7084/public/contrib/bamboo/bamboo-pmd-plugin/trunk/src/test/resources/test-pmd-report.xml
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
class PHPUnit_Util_Log_PMD extends PHPUnit_Util_Printer
{
    public static $THRESHOLD_CLASS_DIT                 = 6;
    public static $THRESHOLD_CLASS_ELOC                = 1000;
    public static $THRESHOLD_CLASS_VARSNP              = 15;
    public static $THRESHOLD_CLASS_PUBLIC_METHODS      = 45;
    public static $THRESHOLD_FUNCTION_CCN              = 10;
    public static $THRESHOLD_FUNCTION_NPATH            = 200;
    public static $THRESHOLD_FUNCTION_ELOC             = 100;
    public static $THRESHOLD_FUNCTION_PARAMETERS       = 10;
    public static $THRESHOLD_COVERAGE_LOW_UPPER_BOUND  = 35;
    public static $THRESHOLD_COVERAGE_HIGH_LOWER_BOUND = 70;

    protected $added;

    /**
     * @param  PHPUnit_Framework_TestResult $result
     * @access public
     */
    public function process(PHPUnit_Framework_TestResult $result)
    {
        $codeCoverage = $result->getCodeCoverageInformation();
        $summary      = PHPUnit_Util_CodeCoverage::getSummary($codeCoverage);
        $files        = array_keys($summary);
        $metrics      = new PHPUnit_Util_Metrics_Project($files, $summary);

        $document = new DOMDocument('1.0', 'UTF-8');
        $document->formatOutput = TRUE;

        $pmd = $document->createElement('pmd');
        $pmd->setAttribute('version', 'PHPUnit ' . PHPUnit_Runner_Version::id());
        $document->appendChild($pmd);

        foreach ($metrics->getFiles() as $fileName => $fileMetrics) {
            $xmlFile = $document->createElement('file');
            $xmlFile->setAttribute('name', $fileName);

            $this->added = FALSE;

            foreach ($fileMetrics->getClasses() as $className => $classMetrics) {
                if (!$classMetrics->getClass()->isInterface()) {
                    $classStartLine = $classMetrics->getClass()->getStartLine();
                    $classEndLine   = $classMetrics->getClass()->getEndLine();
                    $classPackage   = $classMetrics->getPackage();

                    $dit = $classMetrics->getDIT();

                    if (is_int(self::$THRESHOLD_CLASS_DIT) &&
                        self::$THRESHOLD_CLASS_DIT > 0 &&
                        $dit > self::$THRESHOLD_CLASS_DIT) {
                        $this->addViolation(
                          sprintf(
                            'Depth of Inheritance Tree (DIT) is %d but should not exceed %d.',
                            $dit,
                            self::$THRESHOLD_CLASS_DIT
                          ),
                          $xmlFile,
                          'DepthOfInheritanceTree',
                          $classStartLine,
                          $classEndLine,
                          $classPackage,
                          $className
                        );

                        $this->added = TRUE;
                    }

                    $locExecutable = $classMetrics->getLocExecutable();

                    if (is_int(self::$THRESHOLD_CLASS_ELOC) &&
                        self::$THRESHOLD_CLASS_ELOC > 0 &&
                        $locExecutable > self::$THRESHOLD_CLASS_ELOC) {
                        $this->addViolation(
                          sprintf(
                            "Class has %d lines of executable code.\n" .
                            'This is an indication that the class may be ' .
                            'trying to do too much. Try to break it down, ' .
                            'and reduce the size to something manageable.',
                            $locExecutable
                          ),
                          $xmlFile,
                          'ExcessiveClassLength',
                          $classStartLine,
                          $classEndLine,
                          $classPackage,
                          $className
                        );

                        $this->added = TRUE;
                    }

                    $varsNp = $classMetrics->getVARSnp();

                    if (is_int(self::$THRESHOLD_CLASS_VARSNP) &&
                        self::$THRESHOLD_CLASS_VARSNP > 0 &&
                        $varsNp > self::$THRESHOLD_CLASS_VARSNP) {
                        $this->addViolation(
                          sprintf(
                            "Class has %d public fields.\n" .
                            'Classes that have too many fields could be redesigned ' .
                            'to have fewer fields, possibly through some nested ' .
                            'object grouping of some of the information. For ' .
                            'example, a class with city/state/zip fields could ' .
                            'instead have one Address field.',
                            $varsNp
                          ),
                          $xmlFile,
                          'TooManyFields',
                          $classStartLine,
                          $classEndLine,
                          $classPackage,
                          $className
                        );

                        $this->added = TRUE;
                    }


                    $publicMethods = $classMetrics->getPublicMethods();

                    if (is_int(self::$THRESHOLD_CLASS_PUBLIC_METHODS) &&
                        self::$THRESHOLD_CLASS_PUBLIC_METHODS > 0 &&
                        $publicMethods > self::$THRESHOLD_CLASS_PUBLIC_METHODS) {
                        $this->addViolation(
                          sprintf(
                            "Class has %d public methods.\n" .
                            'A large number of public methods and attributes ' .
                            'declared in a class can indicate the class may need ' .
                            'to be broken up as increased effort will be required ' .
                            'to thoroughly test it.',
                            $publicMethods
                          ),
                          $xmlFile,
                          'ExcessivePublicCount',
                          $classStartLine,
                          $classEndLine,
                          $classPackage,
                          $className
                        );

                        $this->added = TRUE;
                    }

                    foreach ($classMetrics->getMethods() as $methodName => $methodMetrics) {
                        if (!$methodMetrics->getMethod()->isAbstract()) {
                            $this->processFunctionOrMethod($xmlFile, $methodMetrics, $classPackage);
                        }
                    }
                }
            }

            foreach ($fileMetrics->getFunctions() as $functionName => $functionMetrics) {
                $this->processFunctionOrMethod($xmlFile, $functionMetrics);
            }

            if ($this->added) {
                $pmd->appendChild($xmlFile);
            }
        }

        $this->write($document->saveXML());
        $this->flush();
    }

    /**
     * @param  string     $violation
     * @param  DOMElement $element
     * @param  string     $rule
     * @param  integer    $line
     * @param  integer    $toLine
     * @param  string     $package
     * @param  string     $class
     * @param  string     $method
     * @access public
     */
    protected function addViolation($violation, DOMElement $element, $rule, $line = '', $toLine = '', $package = '', $class = '', $method = '', $function = '')
    {
        $violationXml = $element->appendChild(
          $element->ownerDocument->createElement('violation', $violation)
        );

        $violationXml->setAttribute('rule', $rule);

        if (!empty($line)) {
            $violationXml->setAttribute('line', $line);
        }

        if (!empty($toLine)) {
            $violationXml->setAttribute('to-line', $toLine);
        }

        if (empty($package)) {
            $package = 'global';
        }

        if (!empty($package)) {
            $violationXml->setAttribute('package', $package);
        }

        if (!empty($class)) {
            $violationXml->setAttribute('class', $class);
        }

        if (!empty($method)) {
            $violationXml->setAttribute('method', $method);
        }

        if (!empty($function)) {
            $violationXml->setAttribute('function', $function);
        }
    }

    protected function processFunctionOrMethod(DOMElement $element, $metrics, $package = '')
    {
        $scope = '';

        if ($metrics->getFunction() instanceof ReflectionMethod) {
            $scope = $metrics->getFunction()->getDeclaringClass()->getName();
        }

        $startLine = $metrics->getFunction()->getStartLine();
        $endLine   = $metrics->getFunction()->getEndLine();
        $name      = $metrics->getFunction()->getName();

        $ccn = $metrics->getCCN();

        if (is_int(self::$THRESHOLD_FUNCTION_CCN) &&
            self::$THRESHOLD_FUNCTION_CCN > 0 &&
            $ccn >= self::$THRESHOLD_FUNCTION_CCN) {
            $this->addViolation(
              sprintf(
                "The cyclomatic complexity is %d.\n" .
                'Complexity is determined by the number of decision points in a ' .
                'function or method plus one for the function or method entry. ' .
                'The decision points are "if", "for", "foreach", "while", "case", ' .
                '"catch", "&amp;&amp;", "||", and "?:". Generally, 1-4 is low ' .
                'complexity, 5-7 indicates moderate complexity, 8-10 is high ' .
                'complexity, and 11+ is very high complexity.',
                $ccn
              ),
              $element,
              'CyclomaticComplexity',
              $startLine,
              $endLine,
              $package,
              $scope,
              $name
            );

            $this->added = TRUE;
        }

        $npath = $metrics->getNPath();

        if (is_int(self::$THRESHOLD_FUNCTION_NPATH) &&
            self::$THRESHOLD_FUNCTION_NPATH > 0 &&
            $npath >= self::$THRESHOLD_FUNCTION_NPATH) {
            $this->addViolation(
              sprintf(
                "The NPath complexity is %d.\n" .
                'The NPath complexity of a function or method is the number of ' .
                'acyclic execution paths through that method. A threshold of 200 ' .
                'is generally considered the point where measures should be taken ' .
                'to reduce complexity.',
                $npath
              ),
              $element,
              'NPathComplexity',
              $startLine,
              $endLine,
              $package,
              $scope,
              $name
            );

            $this->added = TRUE;
        }

        $coverage = $metrics->getCoverage();

        $violation = '';

        if (is_int(self::$THRESHOLD_COVERAGE_LOW_UPPER_BOUND) &&
            self::$THRESHOLD_COVERAGE_LOW_UPPER_BOUND > 0 &&
            $coverage <= self::$THRESHOLD_COVERAGE_LOW_UPPER_BOUND) {
            $violation = 'The code coverage is %01.2f which is considered low.';
        }

        else if (is_int(self::$THRESHOLD_COVERAGE_LOW_UPPER_BOUND) &&
                 self::$THRESHOLD_COVERAGE_LOW_UPPER_BOUND > 0 &&
                 is_int(self::$THRESHOLD_COVERAGE_HIGH_LOWER_BOUND) &&
                 self::$THRESHOLD_COVERAGE_HIGH_LOWER_BOUND > 0 &&
                 $coverage > self::$THRESHOLD_COVERAGE_LOW_UPPER_BOUND &&
                 $coverage < self::$THRESHOLD_COVERAGE_HIGH_LOWER_BOUND) {
            $violation = 'The code coverage is %01.2f which is considered medium.';
        }

        if (!empty($violation)) {
            $this->addViolation(
              sprintf(
                $violation,
                $coverage
              ),
              $element,
              'CodeCoverage',
              $startLine,
              $endLine,
              $package,
              $scope,
              $name
            );

            $this->added = TRUE;
        }

        $locExecutable = $metrics->getLocExecutable();

        if (is_int(self::$THRESHOLD_FUNCTION_ELOC) && self::$THRESHOLD_FUNCTION_ELOC > 0 && $locExecutable > self::$THRESHOLD_FUNCTION_ELOC) {
            $this->addViolation(
              sprintf(
                "Function or method has %d lines of executable code.\n" .
                'Violations of this rule usually indicate that the method is ' .
                'doing too much. Try to reduce the method size by creating ' .
                'helper methods and removing any copy/pasted code.',
                $locExecutable
              ),
              $element,
              'ExcessiveMethodLength',
              $startLine,
              $endLine,
              $package,
              $scope,
              $name
            );

            $this->added = TRUE;
        }

        $parameters = $metrics->getParameters();

        if (is_int(self::$THRESHOLD_FUNCTION_PARAMETERS) && self::$THRESHOLD_FUNCTION_PARAMETERS > 0 && $parameters > self::$THRESHOLD_FUNCTION_PARAMETERS) {
            $this->addViolation(
              sprintf(
                "Function or method has %d parameters.\n" .
                'Long parameter lists can indicate that a new object should be ' .
                'created to wrap the numerous parameters. Basically, try to '.
                'group the parameters together.',
                $parameters
              ),
              $element,
              'ExcessiveParameterList',
              $startLine,
              $endLine,
              $package,
              $scope,
              $name
            );

            $this->added = TRUE;
        }
    }
}
?>
