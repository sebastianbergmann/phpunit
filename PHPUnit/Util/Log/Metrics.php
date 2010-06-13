<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2010, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @copyright  2002-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
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
 * Generates an XML logfile with software metrics information.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.2.0
 */
class PHPUnit_Util_Log_Metrics extends PHPUnit_Util_Printer
{
    /**
     * @param  PHPUnit_Framework_TestResult $result
     */
    public function process(PHPUnit_Framework_TestResult $result)
    {
        $codeCoverage   = $result->getCodeCoverageInformation();
        $summary        = PHPUnit_Util_CodeCoverage::getSummary($codeCoverage);
        $files          = array_keys($summary);
        $projectMetrics = new PHPUnit_Util_Metrics_Project($files, $summary);

        $document = new DOMDocument('1.0', 'UTF-8');
        $document->formatOutput = TRUE;

        $metrics = $document->createElement('metrics');
        $metrics->setAttribute('files', count($projectMetrics->getFiles()));
        $metrics->setAttribute('functions', count($projectMetrics->getFunctions()));
        $metrics->setAttribute('cls', $projectMetrics->getCLS());
        $metrics->setAttribute('clsa', $projectMetrics->getCLSa());
        $metrics->setAttribute('clsc', $projectMetrics->getCLSc());
        $metrics->setAttribute('roots', $projectMetrics->getRoots());
        $metrics->setAttribute('leafs', $projectMetrics->getLeafs());
        $metrics->setAttribute('interfs', $projectMetrics->getInterfs());
        $metrics->setAttribute('maxdit', $projectMetrics->getMaxDit());

        $document->appendChild($metrics);

        foreach ($projectMetrics->getFiles() as $fileName => $fileMetrics) {
            $xmlFile = $metrics->appendChild(
              $document->createElement('file')
            );

            $xmlFile->setAttribute('name', $fileName);
            $xmlFile->setAttribute('classes', count($fileMetrics->getClasses()));
            $xmlFile->setAttribute('functions', count($fileMetrics->getFunctions()));
            $xmlFile->setAttribute('loc', $fileMetrics->getLoc());
            $xmlFile->setAttribute('cloc', $fileMetrics->getCloc());
            $xmlFile->setAttribute('ncloc', $fileMetrics->getNcloc());
            $xmlFile->setAttribute('locExecutable', $fileMetrics->getLocExecutable());
            $xmlFile->setAttribute('locExecuted', $fileMetrics->getLocExecuted());
            $xmlFile->setAttribute('coverage', sprintf('%F', $fileMetrics->getCoverage()));

            foreach ($fileMetrics->getClasses() as $className => $classMetrics) {
                if (!$classMetrics->getClass()->implementsInterface('PHPUnit_Framework_Test')) {
                    $xmlClass = $document->createElement('class');

                    $xmlClass->setAttribute('name', $classMetrics->getClass()->getName());
                    $xmlClass->setAttribute('loc', $classMetrics->getLoc());
                    $xmlClass->setAttribute('locExecutable', $classMetrics->getLocExecutable());
                    $xmlClass->setAttribute('locExecuted', $classMetrics->getLocExecuted());
                    $xmlClass->setAttribute('aif', sprintf('%F', $classMetrics->getAIF()));
                    $xmlClass->setAttribute('ahf', sprintf('%F', $classMetrics->getAHF()));
                    $xmlClass->setAttribute('ca', $classMetrics->getCa());
                    $xmlClass->setAttribute('ce', $classMetrics->getCe());
                    $xmlClass->setAttribute('csz', $classMetrics->getCSZ());
                    $xmlClass->setAttribute('cis', $classMetrics->getCIS());
                    $xmlClass->setAttribute('coverage', sprintf('%F', $classMetrics->getCoverage()));
                    $xmlClass->setAttribute('dit', $classMetrics->getDIT());
                    $xmlClass->setAttribute('i', sprintf('%F', $classMetrics->getI()));
                    $xmlClass->setAttribute('impl', $classMetrics->getIMPL());
                    $xmlClass->setAttribute('mif', sprintf('%F', $classMetrics->getMIF()));
                    $xmlClass->setAttribute('mhf', sprintf('%F', $classMetrics->getMHF()));
                    $xmlClass->setAttribute('noc', $classMetrics->getNOC());
                    $xmlClass->setAttribute('pf', sprintf('%F', $classMetrics->getPF()));
                    $xmlClass->setAttribute('vars', $classMetrics->getVARS());
                    $xmlClass->setAttribute('varsnp', $classMetrics->getVARSnp());
                    $xmlClass->setAttribute('varsi', $classMetrics->getVARSi());
                    $xmlClass->setAttribute('wmc', $classMetrics->getWMC());
                    $xmlClass->setAttribute('wmcnp', $classMetrics->getWMCnp());
                    $xmlClass->setAttribute('wmci', $classMetrics->getWMCi());

                    foreach ($classMetrics->getMethods() as $methodName => $methodMetrics) {
                        $xmlMethod = $xmlClass->appendChild(
                          $document->createElement('method')
                        );

                        $this->processFunctionOrMethod($methodMetrics, $xmlMethod);
                    }

                    $xmlFile->appendChild($xmlClass);
                }
            }

            foreach ($fileMetrics->getFunctions() as $functionName => $functionMetrics) {
                $xmlFunction = $xmlFile->appendChild(
                  $document->createElement('function')
                );

                $this->processFunctionOrMethod($functionMetrics, $xmlFunction);
            }
        }

        $this->write($document->saveXML());
        $this->flush();
    }

    /**
     * @param  PHPUnit_Util_Metrics_Function $metrics
     * @param  DOMElement                    $element
     */
    protected function processFunctionOrMethod($metrics, DOMElement $element)
    {
        $element->setAttribute('name', $metrics->getFunction()->getName());
        $element->setAttribute('loc', $metrics->getLoc());
        $element->setAttribute('locExecutable', $metrics->getLocExecutable());
        $element->setAttribute('locExecuted', $metrics->getLocExecuted());
        $element->setAttribute('coverage', sprintf('%F', $metrics->getCoverage()));
        $element->setAttribute('ccn', $metrics->getCCN());
        $element->setAttribute('crap', sprintf('%F', $metrics->getCrapIndex()));
        $element->setAttribute('npath', $metrics->getNPath());
        $element->setAttribute('parameters', $metrics->getParameters());
    }
}
?>