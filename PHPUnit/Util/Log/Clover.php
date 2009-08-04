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
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.3.0
 */

require_once 'PHPUnit/Runner/Version.php';
require_once 'PHPUnit/Util/Class.php';
require_once 'PHPUnit/Util/CodeCoverage.php';
require_once 'PHPUnit/Util/File.php';
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
 * @copyright  2002-2009 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.1.4
 */
class PHPUnit_Util_Log_Clover extends PHPUnit_Util_Printer
{
    /**
     * @param  PHPUnit_Framework_TestResult $result
     * @todo   Count conditionals.
     */
    public function process(PHPUnit_Framework_TestResult $result)
    {
        $time = time();

        $document = new DOMDocument('1.0', 'UTF-8');
        $document->formatOutput = TRUE;

        $coverage = $document->createElement('coverage');
        $coverage->setAttribute('generated', $time);
        $coverage->setAttribute('phpunit', PHPUnit_Runner_Version::id());
        $document->appendChild($coverage);

        $project = $document->createElement('project');
        $project->setAttribute('name', $result->topTestSuite()->getName());
        $project->setAttribute('timestamp', $time);
        $coverage->appendChild($project);

        $codeCoverageInformation    = $result->getCodeCoverageInformation();
        $files                      = PHPUnit_Util_CodeCoverage::getSummary($codeCoverageInformation);
        $packages                   = array();

        $projectStatistics = array(
          'files'               => 0,
          'loc'                 => 0,
          'ncloc'               => 0,
          'classes'             => 0,
          'methods'             => 0,
          'coveredMethods'      => 0,
          'conditionals'        => 0,
          'coveredConditionals' => 0,
          'statements'          => 0,
          'coveredStatements'   => 0
        );

        foreach ($files as $filename => $data) {
            $namespace = 'global';

            if (file_exists($filename)) {
                $fileStatistics = array(
                  'classes'             => 0,
                  'methods'             => 0,
                  'coveredMethods'      => 0,
                  'conditionals'        => 0,
                  'coveredConditionals' => 0,
                  'statements'          => 0,
                  'coveredStatements'   => 0
                );

                $file = $document->createElement('file');
                $file->setAttribute('name', $filename);

                $lines = array();

                foreach (PHPUnit_Util_File::getClassesInFile($filename) as $className => $_class) {
                    $classStatistics = array(
                      'methods'             => 0,
                      'coveredMethods'      => 0,
                      'conditionals'        => 0,
                      'coveredConditionals' => 0,
                      'statements'          => 0,
                      'coveredStatements'   => 0
                    );

                    foreach ($_class['methods'] as $methodName => $method) {
                        $classStatistics['methods']++;

                        $methodCount = 0;

                        for ($i = $method['startLine']; $i <= $method['endLine']; $i++) {
                            $add   = TRUE;
                            $count = 0;

                            if (isset($files[$filename][$i])) {
                                if ($files[$filename][$i] != -2) {
                                    $classStatistics['statements']++;
                                }

                                if (is_array($files[$filename][$i])) {
                                    $classStatistics['coveredStatements']++;
                                    $count = count($files[$filename][$i]);
                                }

                                else if ($files[$filename][$i] == -2) {
                                    $add = FALSE;
                                }
                            } else {
                                $add = FALSE;
                            }

                            $methodCount = max($methodCount, $count);

                            if ($add) {
                                $lines[$i] = array(
                                  'count' => $count,
                                  'type'  => 'stmt'
                                );
                            }
                        }

                        if ($methodCount > 0) {
                            $classStatistics['coveredMethods']++;
                        }

                        $lines[$method['startLine']] = array(
                          'count' => $methodCount,
                          'type'  => 'method',
                          'name'  => $methodName
                        );
                    }

                    $packageInformation = PHPUnit_Util_Class::getPackageInformation(
                      $className, $_class['docComment']
                    );

                    if (!empty($packageInformation['namespace'])) {
                        $namespace = $packageInformation['namespace'];
                    }

                    $class = $document->createElement('class');
                    $class->setAttribute('name', $className);
                    $class->setAttribute('namespace', $namespace);

                    if (!empty($packageInformation['fullPackage'])) {
                        $class->setAttribute('fullPackage', $packageInformation['fullPackage']);
                    }

                    if (!empty($packageInformation['category'])) {
                        $class->setAttribute('category', $packageInformation['category']);
                    }

                    if (!empty($packageInformation['package'])) {
                        $class->setAttribute('package', $packageInformation['package']);
                    }

                    if (!empty($packageInformation['subpackage'])) {
                        $class->setAttribute('subpackage', $packageInformation['subpackage']);
                    }

                    $file->appendChild($class);

                    $metrics = $document->createElement('metrics');
                    $metrics->setAttribute('methods', $classStatistics['methods']);
                    $metrics->setAttribute('coveredmethods', $classStatistics['coveredMethods']);
                    //$metrics->setAttribute('conditionals', $classStatistics['conditionals']);
                    //$metrics->setAttribute('coveredconditionals', $classStatistics['coveredConditionals']);
                    $metrics->setAttribute('statements', $classStatistics['statements']);
                    $metrics->setAttribute('coveredstatements', $classStatistics['coveredStatements']);
                    $metrics->setAttribute('elements', $classStatistics['conditionals'] + $classStatistics['statements'] + $classStatistics['methods']);
                    $metrics->setAttribute('coveredelements', $classStatistics['coveredConditionals'] + $classStatistics['coveredStatements'] + $classStatistics['coveredMethods']);
                    $class->appendChild($metrics);

                    $fileStatistics['methods']             += $classStatistics['methods'];
                    $fileStatistics['coveredMethods']      += $classStatistics['coveredMethods'];
                    $fileStatistics['conditionals']        += $classStatistics['conditionals'];
                    $fileStatistics['coveredConditionals'] += $classStatistics['coveredConditionals'];
                    $fileStatistics['statements']          += $classStatistics['statements'];
                    $fileStatistics['coveredStatements']   += $classStatistics['coveredStatements'];
                    $fileStatistics['classes']++;
                }

                ksort($lines);

                foreach ($lines as $_line => $_data) {
                    $line = $document->createElement('line');
                    $line->setAttribute('num', $_line);
                    $line->setAttribute('type', $_data['type']);

                    if (isset($_data['name'])) {
                        $line->setAttribute('name', $_data['name']);
                    }

                    $line->setAttribute('count', $_data['count']);

                    $file->appendChild($line);
                }

                $count = PHPUnit_Util_File::countLines($filename);

                $metrics = $document->createElement('metrics');
                $metrics->setAttribute('loc', $count['loc']);
                $metrics->setAttribute('ncloc', $count['ncloc']);
                $metrics->setAttribute('classes', $fileStatistics['classes']);
                $metrics->setAttribute('methods', $fileStatistics['methods']);
                $metrics->setAttribute('coveredmethods', $fileStatistics['coveredMethods']);
                //$metrics->setAttribute('conditionals', $fileStatistics['conditionals']);
                //$metrics->setAttribute('coveredconditionals', $fileStatistics['coveredConditionals']);
                $metrics->setAttribute('statements', $fileStatistics['statements']);
                $metrics->setAttribute('coveredstatements', $fileStatistics['coveredStatements']);
                $metrics->setAttribute('elements', $fileStatistics['conditionals'] + $fileStatistics['statements'] + $fileStatistics['methods']);
                $metrics->setAttribute('coveredelements', $fileStatistics['coveredConditionals'] + $fileStatistics['coveredStatements'] + $fileStatistics['coveredMethods']);

                $file->appendChild($metrics);

                if ($namespace == 'global') {
                    $project->appendChild($file);
                } else {
                    if (!isset($packages[$namespace])) {
                        $packages[$namespace] = $document->createElement('package');
                        $packages[$namespace]->setAttribute('name', $namespace);
                        $project->appendChild($packages[$namespace]);
                    }

                    $packages[$namespace]->appendChild($file);
                }

                $projectStatistics['loc']                 += $count['loc'];
                $projectStatistics['ncloc']               += $count['ncloc'];
                $projectStatistics['classes']             += $fileStatistics['classes'];
                $projectStatistics['methods']             += $fileStatistics['methods'];
                $projectStatistics['coveredMethods']      += $fileStatistics['coveredMethods'];
                $projectStatistics['conditionals']        += $fileStatistics['conditionals'];
                $projectStatistics['coveredConditionals'] += $fileStatistics['coveredConditionals'];
                $projectStatistics['statements']          += $fileStatistics['statements'];
                $projectStatistics['coveredStatements']   += $fileStatistics['coveredStatements'];
                $projectStatistics['files']++;
            }
        }

        $metrics = $document->createElement('metrics');
        $metrics->setAttribute('files', $projectStatistics['files']);
        $metrics->setAttribute('loc', $projectStatistics['loc']);
        $metrics->setAttribute('ncloc', $projectStatistics['ncloc']);
        $metrics->setAttribute('classes', $projectStatistics['classes']);
        $metrics->setAttribute('methods', $projectStatistics['methods']);
        $metrics->setAttribute('coveredmethods', $projectStatistics['coveredMethods']);
        //$metrics->setAttribute('conditionals', $projectStatistics['conditionals']);
        //$metrics->setAttribute('coveredconditionals', $projectStatistics['coveredConditionals']);
        $metrics->setAttribute('statements', $projectStatistics['statements']);
        $metrics->setAttribute('coveredstatements', $projectStatistics['coveredStatements']);
        $metrics->setAttribute('elements', $projectStatistics['conditionals'] + $projectStatistics['statements'] + $projectStatistics['methods']);
        $metrics->setAttribute('coveredelements', $projectStatistics['coveredConditionals'] + $projectStatistics['coveredStatements'] + $projectStatistics['coveredMethods']);
        $project->appendChild($metrics);

        $this->write($document->saveXML());
        $this->flush();
    }
}
?>
