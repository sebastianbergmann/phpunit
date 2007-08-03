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
 * @since      File available since Release 3.1.4
 */

require_once 'PHPUnit/Runner/Version.php';
require_once 'PHPUnit/Util/Class.php';
require_once 'PHPUnit/Util/CodeCoverage.php';
require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Util/Printer.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Generates an XML logfile with code coverage information using the
 * Clover format "documented" at
 * http://svn.atlassian.com/svn/public/contrib/bamboo/bamboo-coverage-plugin/trunk/src/test/resources/test-clover-report.xml
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.1.4
 */
class PHPUnit_Util_Log_CodeCoverage_XML extends PHPUnit_Util_Printer
{
    /**
     * @param  PHPUnit_Framework_TestResult $result
     * @access public
     * @todo   Count conditionals.
     */
    public function process(PHPUnit_Framework_TestResult $result)
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $document->formatOutput = TRUE;

        $coverage = $document->createElement('coverage');
        $coverage->setAttribute('generated', time());
        $coverage->setAttribute('phpunit', PHPUnit_Runner_Version::id());
        $document->appendChild($coverage);

        $project = $document->createElement('project');
        $project->setAttribute('name', $result->topTestSuite()->getName());
        $project->setAttribute('timestamp', time());
        $coverage->appendChild($project);

        $codeCoverageInformation = $result->getCodeCoverageInformation();
        $files                   = PHPUnit_Util_CodeCoverage::getSummary($codeCoverageInformation);

        $projectFiles = 0;
        $projectLoc = 0;
        $projectNcloc = 0;
        $projectClasses = 0;
        $projectMethods = 0;
        $projectCoveredMethods = 0;
        $projectConditionals = 0;
        $projectCoveredConditionals = 0;
        $projectStatements = 0;
        $projectCoveredStatements = 0;

        foreach ($files as $filename => $data) {
            $projectFiles++;

            $fileClasses = 0;
            $fileConditionals = 0;
            $fileCoveredConditionals = 0;
            $fileStatements = 0;
            $fileCoveredStatements = 0;
            $fileMethods = 0;
            $fileCoveredMethods = 0;

            $file = $document->createElement('file');
            $file->setAttribute('name', $filename);

            $classes = PHPUnit_Util_Class::getClassesInFile($filename);
            $lines   = array();

            foreach ($classes as $class) {
                $methods    = $class->getMethods();
                $numMethods = 0;
                $fileClasses++;
                $projectClasses++;

                $classConditionals = 0;
                $classCoveredConditionals = 0;
                $classStatements = 0;
                $classCoveredStatements = 0;
                $classCoveredMethods = 0;

                foreach ($methods as $method) {
                    if ($method->getDeclaringClass()->getName() == $class->getName()) {
                        $startLine = $method->getStartLine();
                        $numMethods++;
                        $fileMethods++;
                        $projectMethods++;

                        if ($startLine) {
                            $endLine = $method->getEndLine();
                            $tests   = array();

                            for ($i = $startLine; $i <= $endLine; $i++) {
                                if (isset($files[$filename][$i])) {
                                    if (is_array($files[$filename][$i])) {
                                        foreach ($files[$filename][$i] as $_test) {
                                            $add = TRUE;

                                            foreach ($tests as $test) {
                                                if ($test === $_test) {
                                                    $add = FALSE;
                                                    break;
                                                }
                                            }

                                            if ($add) {
                                                $tests[] = $_test;
                                            }
                                        }

                                        $classCoveredStatements++;
                                    }

                                    $classStatements++;
                                }
                            }

                            $count = count($tests);

                            $lines[$startLine] = array(
                              'count' => $count,
                              'type' => 'method'
                            );

                            if ($count > 0) {
                                $classCoveredMethods++;
                                $fileCoveredMethods++;
                                $projectCoveredMethods++;
                            }
                        }
                    }
                }

                $classXML = $document->createElement('class');
                $classXML->setAttribute('name', $class->getName());
                $file->appendChild($classXML);

                $classMetricsXML = $document->createElement('metrics');
                $classMetricsXML->setAttribute('methods', $numMethods);
                $classMetricsXML->setAttribute('coveredmethods', $classCoveredMethods);
                //$classMetricsXML->setAttribute('conditionals', $classConditionals);
                //$classMetricsXML->setAttribute('coveredconditionals', $classCoveredConditionals);
                $classMetricsXML->setAttribute('statements', $classStatements);
                $classMetricsXML->setAttribute('coveredstatements', $classCoveredStatements);
                $classMetricsXML->setAttribute('elements', $classConditionals + $classStatements + $numMethods);
                $classMetricsXML->setAttribute('coveredelements', $classCoveredConditionals + $classCoveredStatements + $classCoveredMethods);
                $classXML->appendChild($classMetricsXML);

                $fileStatements += $classStatements;
                $fileCoveredStatements += $classCoveredStatements;
            }

            foreach ($data as $_line => $_data) {
                if (is_array($_data)) {
                    $count = count($_data);
                }

                else if ($_data == -1) {
                    $count = 0;
                }

                else if ($_data == -2) {
                    continue;
                }

                $lines[$_line] = array(
                  'count' => $count,
                  'type' => 'stmt'
                );
            }

            ksort($lines);

            foreach ($lines as $_line => $_data) {
                $line = $document->createElement('line');
                $line->setAttribute('num', $_line);
                $line->setAttribute('type', $_data['type']);
                $line->setAttribute('count', $_data['count']);

                $file->appendChild($line);
            }

            list($loc, $ncloc) = $this->countLines($filename);

            $projectLoc += $loc;
            $projectNcloc += $ncloc;

            $fileMetricsXML = $document->createElement('metrics');
            $fileMetricsXML->setAttribute('loc', $loc);
            $fileMetricsXML->setAttribute('ncloc', $ncloc);
            $fileMetricsXML->setAttribute('classes', $fileClasses);
            $fileMetricsXML->setAttribute('methods', $fileMethods);
            $fileMetricsXML->setAttribute('coveredmethods', $fileCoveredMethods);
            //$fileMetricsXML->setAttribute('conditionals', $fileConditionals);
            //$fileMetricsXML->setAttribute('coveredconditionals', $fileCoveredConditionals);
            $fileMetricsXML->setAttribute('statements', $fileStatements);
            $fileMetricsXML->setAttribute('coveredstatements', $fileCoveredStatements);
            $fileMetricsXML->setAttribute('elements', $fileConditionals + $fileStatements + $fileMethods);
            $fileMetricsXML->setAttribute('coveredelements', $fileCoveredConditionals + $fileCoveredStatements + $fileCoveredMethods);
            $file->appendChild($fileMetricsXML);

            $project->appendChild($file);
            $projectStatements += $fileStatements;
            $projectCoveredStatements += $fileCoveredStatements;
        }

        $projectMetricsXML = $document->createElement('metrics');
        $projectMetricsXML->setAttribute('files', $projectFiles);
        $projectMetricsXML->setAttribute('loc', $projectLoc);
        $projectMetricsXML->setAttribute('ncloc', $projectNcloc);
        $projectMetricsXML->setAttribute('classes', $projectClasses);
        $projectMetricsXML->setAttribute('methods', $projectMethods);
        $projectMetricsXML->setAttribute('coveredmethods', $projectCoveredMethods);
        //$projectMetricsXML->setAttribute('conditionals', $projectConditionals);
        //$projectMetricsXML->setAttribute('coveredconditionals', $projectCoveredConditionals);
        $projectMetricsXML->setAttribute('statements', $projectStatements);
        $projectMetricsXML->setAttribute('coveredstatements', $projectCoveredStatements);
        $projectMetricsXML->setAttribute('elements', $projectConditionals + $projectStatements + $projectMethods);
        $projectMetricsXML->setAttribute('coveredelements', $projectCoveredConditionals + $projectCoveredStatements + $projectCoveredMethods);
        $project->appendChild($projectMetricsXML);

        $this->write($document->saveXML());
        $this->flush();
    }

    /**
     * @param  string $filename
     * @return array
     * @access protected
     */
    protected function countLines($filename)
    {
        $lines  = file($filename);        
        $tokens = token_get_all(file_get_contents($filename));
        $loc    = count($lines);
        $cloc   = 0;

        foreach ($tokens as $i => $token) {
            if (is_string($token)) {
                continue;
            }

            list ($token, $value) = $token;

            if ($token == T_COMMENT || $token == T_DOC_COMMENT) {
                $cloc += count(explode("\n", $value));
            }
        }

        return array($loc, $loc - $cloc);
    }
}
?>
