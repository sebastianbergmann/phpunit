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

require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Util/XML.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Wrapper for the PHPUnit XML configuration file.
 *
 * Example XML configuration file:
 * <code>
 * <?xml version="1.0" encoding="utf-8" ?>
 *
 * <phpunit>
 *   <logging>
 *     <log type="coverage-html" target="/tmp/report" charset="UTF-8"
 *          highlight="false" lowUpperBound="35" highLowerBound="70"/>
 *     <log type="coverage-xml" target="/tmp/coverage.xml"/>
 *     <log type="graphviz" target="/tmp/logfile.dot"/>
 *     <log type="json" target="/tmp/logfile.json"/>
 *     <log type="metrics-xml" target="/tmp/metrics.xml"/>
 *     <log type="plain" target="/tmp/logfile.txt"/>
 *     <log type="pmd-xml" target="/tmp/pmd.xml" cpdMinLines="5" cpdMinMatches="70"/>
 *     <log type="tap" target="/tmp/logfile.tap"/>
 *     <log type="test-xml" target="/tmp/logfile.xml" logIncompleteSkipped="false"/>
 *     <log type="testdox-html" target="/tmp/testdox.html"/>
 *     <log type="testdox-text" target="/tmp/testdox.txt"/>
 * 
 *     <pmd>
 *       <rule class="PHPUnit_Util_Log_PMD_Rule_Project_CRAP"
 *             threshold="5,30"/>
 *       <rule class="PHPUnit_Util_Log_PMD_Rule_Class_DepthOfInheritanceTree"
 *             threshold="6"/>
 *       <rule class="PHPUnit_Util_Log_PMD_Rule_Class_EfferentCoupling"
 *             threshold="20"/>
 *       <rule class="PHPUnit_Util_Log_PMD_Rule_Class_ExcessiveClassLength"
 *             threshold="1000"/>
 *       <rule class="PHPUnit_Util_Log_PMD_Rule_Class_ExcessivePublicCount"
 *             threshold="45"/>
 *       <rule class="PHPUnit_Util_Log_PMD_Rule_Class_TooManyFields"
 *             threshold="15"/>
 *       <rule class="PHPUnit_Util_Log_PMD_Rule_Function_CodeCoverage"
 *             threshold="35,70"/>
 *       <rule class="PHPUnit_Util_Log_PMD_Rule_Function_CRAP"
 *             threshold="30"/>
 *       <rule class="PHPUnit_Util_Log_PMD_Rule_Function_CyclomaticComplexity"
 *             threshold="20"/>
 *       <rule class="PHPUnit_Util_Log_PMD_Rule_Function_ExcessiveMethodLength"
 *             threshold="100"/>
 *       <rule class="PHPUnit_Util_Log_PMD_Rule_Function_ExcessiveParameterList"
 *             threshold="10"/>
 *       <rule class="PHPUnit_Util_Log_PMD_Rule_Function_NPathComplexity"
 *             threshold="200"/>
 *     </pmd>
 *   </logging>
 * </phpunit>
 * </code>
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
class PHPUnit_Util_Configuration
{
    protected $document;

    /**
     * Loads a PHPUnit configuration file.
     *
     * @param  string $filename
     * @access public
     */
    public function __construct($filename)
    {
        $this->document = PHPUnit_Util_XML::load($filename);
    }

    /**
     * Returns the logging configuration.
     *
     * @return array
     * @access public
     */
    public function getLoggingConfiguration()
    {
        $xpath  = new DOMXPath($this->document);
        $logs   = $xpath->query('logging/log');
        $result = array();

        foreach ($logs as $log) {
            $type   = (string)$log->getAttribute('type');
            $target = (string)$log->getAttribute('target');

            if ($type == 'coverage-html') {
                if ($log->hasAttribute('charset')) {
                    $result['charset'] = (string)$log->getAttribute('charset');
                }

                if ($log->hasAttribute('lowUpperBound')) {
                    $result['lowUpperBound'] = (string)$log->getAttribute('lowUpperBound');
                }

                if ($log->hasAttribute('highLowerBound')) {
                    $result['highLowerBound'] = (string)$log->getAttribute('highLowerBound');
                }

                if ($log->hasAttribute('highlight')) {
                    if ((string)$log->getAttribute('highlight') == 'true') {
                        $result['highlight'] = TRUE;
                    } else {
                        $result['highlight'] = FALSE;
                    }
                }
            }

            else if ($type == 'pmd-xml') {
                if ($log->hasAttribute('cpdMinLines')) {
                    $result['cpdMinLines'] = (string)$log->getAttribute('cpdMinLines');
                }

                if ($log->hasAttribute('cpdMinMatches')) {
                    $result['cpdMinMatches'] = (string)$log->getAttribute('cpdMinMatches');
                }
            }

            else if ($type == 'test-xml') {
                if ($log->hasAttribute('logIncompleteSkipped')) {
                    if ((string)$log->getAttribute('logIncompleteSkipped') == 'true') {
                        $result['logIncompleteSkipped'] = TRUE;
                    } else {
                        $result['logIncompleteSkipped'] = FALSE;
                    }
                }
            }

            $result[$type] = $target;
        }

        return $result;
    }

    /**
     * Returns the configuration for PMD rules.
     *
     * @return array
     * @access public
     */
    public function getPMDConfiguration()
    {
        $xpath  = new DOMXPath($this->document);
        $rules  = $xpath->query('logging/pmd/rule');
        $result = array();

        foreach ($rules as $rule) {
            $class     = (string)$rule->getAttribute('class');
            $threshold = (string)$rule->getAttribute('threshold');
            $threshold = explode(',', $threshold);

            if (count($threshold) == 1) {
                $threshold = $threshold[0];
            }

            $result[$class] = $threshold;
        }

        return $result;
    }
}
?>
