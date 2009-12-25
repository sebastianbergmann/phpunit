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

require_once 'PHPUnit/Runner/Version.php';
require_once 'PHPUnit/Util/Metrics/Project.php';
require_once 'PHPUnit/Util/CodeCoverage.php';
require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Util/Printer.php';
require_once 'PHPUnit/Util/XML.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Generates an XML logfile with code duplication information.
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
class PHPUnit_Util_Log_CPD extends PHPUnit_Util_Printer
{
    /**
     * @param  PHPUnit_Framework_TestResult $result
     */
    public function process(PHPUnit_Framework_TestResult $result, $minLines = 5, $minMatches = 70)
    {
        $codeCoverage = $result->getCodeCoverageInformation();
        $summary      = PHPUnit_Util_CodeCoverage::getSummary($codeCoverage);
        $files        = array_keys($summary);
        $metrics      = new PHPUnit_Util_Metrics_Project($files, $summary, TRUE, $minLines, $minMatches);

        $document = new DOMDocument('1.0', 'UTF-8');
        $document->formatOutput = TRUE;

        $cpd = $document->createElement('pmd-cpd');
        $cpd->setAttribute('version', 'PHPUnit ' . PHPUnit_Runner_Version::id());
        $document->appendChild($cpd);

        foreach ($metrics->getDuplicates() as $duplicate) {
            $xmlDuplication = $cpd->appendChild(
              $document->createElement('duplication')
            );

            $xmlDuplication->setAttribute('lines', $duplicate['numLines']);
            $xmlDuplication->setAttribute('tokens', $duplicate['numTokens']);

            $xmlFile = $xmlDuplication->appendChild(
              $document->createElement('file')
            );

            $xmlFile->setAttribute('path', $duplicate['fileA']->getPath());
            $xmlFile->setAttribute('line', $duplicate['firstLineA']);

            $xmlFile = $xmlDuplication->appendChild(
              $document->createElement('file')
            );

            $xmlFile->setAttribute('path', $duplicate['fileB']->getPath());
            $xmlFile->setAttribute('line', $duplicate['firstLineB']);

            $xmlDuplication->appendChild(
              $document->createElement(
                'codefragment',
                PHPUnit_Util_XML::prepareString(
                  join(
                    '',
                    array_slice(
                      $duplicate['fileA']->getLines(),
                      $duplicate['firstLineA'] - 1,
                      $duplicate['numLines']
                    )
                  )
                )
              )
            );
        }

        $this->write($document->saveXML());
        $this->flush();
    }
}
?>
