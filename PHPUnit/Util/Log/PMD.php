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
    /**
     * @param  PHPUnit_Framework_TestResult $result
     * @access public
     */
    public function process(PHPUnit_Framework_TestResult $result)
    {
        $codeCoverage = $result->getCodeCoverageInformation(FALSE, TRUE);
        $summary      = PHPUnit_Util_CodeCoverage::getSummary($codeCoverage);
        $files        = array_keys($summary);
        $metrics      = new PHPUnit_Util_Metrics_Project($files, $summary);

        $document = new DOMDocument('1.0', 'UTF-8');
        $document->formatOutput = TRUE;

        $pmd = $document->createElement('pmd');
        $pmd->setAttribute('version', 'PHPUnit ' . PHPUnit_Runner_Version::id());
        $document->appendChild($pmd);

        foreach ($metrics->getFiles() as $fileName => $fileMetrics) {
            $xmlFile = $pmd->appendChild(
              $document->createElement('file')
            );

            $xmlFile->setAttribute('name', $fileName);

            foreach ($fileMetrics->getClasses() as $className => $classMetrics) {
                $dit = $classMetrics->getDIT();

                if ($dit > 6) {
                    $this->addViolation(
                      'Depth of Inheritance Tree (DIT) should not exceed 6.',
                      $xmlFile,
                      'DepthOfInheritanceTree',
                      $classMetrics->getClass()->getStartLine(),
                      '',
                      $className
                    );
                }

                $ahf = $classMetrics->getAHF();

                if (!($ahf >= 8 && $ahf <= 25)) {
                    $this->addViolation(
                      'Attribute Hiding Factor (AHF) should be between 8% and 25%.',
                      $xmlFile,
                      'AttributeHidingFactor',
                      $classMetrics->getClass()->getStartLine(),
                      '',
                      $className
                    );
                }

                $aif = $classMetrics->getAIF();

                if (!($aif >= 0 && $aif <= 48)) {
                    $this->addViolation(
                      'Attribute Inheritance Factor (AIF) should be between 0% and 48%.',
                      $xmlFile,
                      'AttributeInheritanceFactor',
                      $classMetrics->getClass()->getStartLine(),
                      '',
                      $className
                    );
                }

                $mhf = $classMetrics->getMHF();

                if (!($mhf >= 8 && $mhf <= 25)) {
                    $this->addViolation(
                      'Method Hiding Factor (MHF) should be between 8% and 25%.',
                      $xmlFile,
                      'MethodHidingFactor',
                      $classMetrics->getClass()->getStartLine(),
                      '',
                      $className
                    );
                }

                $mif = $classMetrics->getMIF();

                if (!($mif >= 20 && $mif <= 80)) {
                    $this->addViolation(
                      'Method Inheritance Factor (MIF) should be between 20% and 80%.',
                      $xmlFile,
                      'MethodInheritanceFactor',
                      $classMetrics->getClass()->getStartLine(),
                      '',
                      $className
                    );
                }

                foreach ($classMetrics->getMethods() as $methodName => $methodMetrics) {
                    $ccn = $methodMetrics->getCCN();

                    $violation = '';

                    if ($ccn >= 50) {
                        $violation = 'A cyclomatic complexity of over 50 indicates unmaintainable code.';
                    }

                    else if ($ccn >= 20) {
                        $violation = 'A cyclomatic complexity of over 20 indicates hardly maintainable code.';
                    }

                    if (!empty($violation)) {
                        $this->addViolation(
                          $violation,
                          $xmlFile,
                          'CyclomaticComplexity',
                          $methodMetrics->getMethod()->getStartLine(),
                          '',
                          $className,
                          $methodName
                        );
                    }
                }
            }

            foreach ($fileMetrics->getFunctions() as $functionName => $functionMetrics) {
                $ccn = $functionMetrics->getCCN();

                $violation = '';

                if ($ccn >= 50) {
                    $violation = 'A cyclomatic complexity of over 50 indicates unmaintainable code.';
                }

                else if ($ccn >= 20) {
                    $violation = 'A cyclomatic complexity of over 20 indicates hardly maintainable code.';
                }

                if (!empty($violation)) {
                    $this->addViolation(
                      $violation,
                      $xmlFile,
                      'CyclomaticComplexity',
                      $functionMetrics->getFunction()->getStartLine(),
                      '',
                      '',
                      '',
                      $functionName
                    );
                }
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
     * @param  string     $package
     * @param  string     $class
     * @param  string     $method
     * @access public
     */
    protected function addViolation($violation, DOMElement $element, $rule, $line = '', $package = '', $class = '', $method = '', $function = '')
    {
        $violationXml = $element->appendChild(
          $element->ownerDocument->createElement('violation', $violation)
        );

        $violationXml->setAttribute('rule', $rule);

        if (!empty($line)) {
            $violationXml->setAttribute('line', $line);
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
}
?>
