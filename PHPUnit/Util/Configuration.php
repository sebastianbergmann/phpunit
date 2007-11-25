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
require_once 'PHPUnit/Runner/IncludePathTestCollector.php';
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
 *   <testsuite name="My Test Suite">
 *     <directory suffix="Test.php">/path/to/files</directory>
 *     <file>/path/to/MyTest.php</file>
 *   </testsuite>
 *
 *   <groups>
 *     <include>
 *       <group>name</group>
 *     </include>
 *     <exclude>
 *       <group>name</group>
 *     </exclude>
 *   </groups>
 *
 *   <filter>
 *     <blacklist>
 *       <directory suffix=".php">/path/to/files</directory>
 *       <file>/path/to/file</file>
 *     </blacklist>
 *     <whitelist>
 *       <directory suffix=".php">/path/to/files</directory>
 *       <file>/path/to/file</file>
 *     </whitelist>
 *   </filter>
 *
 *   <logging>
 *     <log type="coverage-html" target="/tmp/report" charset="UTF-8"
 *          yui="true" highlight="false"
 *          lowUpperBound="35" highLowerBound="70"/>
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
 *
 *   <php>
 *     <ini name="foo" value="bar"/>
 *     <var name="foo" value="bar"/>
 *   </php>
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
    protected $xpath;

    /**
     * Loads a PHPUnit configuration file.
     *
     * @param  string $filename
     * @access public
     */
    public function __construct($filename)
    {
        $this->document = PHPUnit_Util_XML::load($filename);
        $this->xpath    = new DOMXPath($this->document);
    }

    /**
     * Returns the configuration for SUT filtering.
     *
     * @return array
     * @access public
     * @since  Method available since Release 3.2.1
     */
    public function getFilterConfiguration()
    {
        $filters = array(
          'blacklist' => array('directory' => array(), 'file' => array()),
          'whitelist' => array('directory' => array(), 'file' => array())
        );

        foreach ($this->xpath->query('filter/blacklist/directory') as $directory) {
            if ($directory->hasAttribute('suffix')) {
                $suffix = (string)$directory->getAttribute('suffix');
            } else {
                $suffix = '.php';
            }

            $filters['blacklist']['directory'][] = array(
              'path'   => (string)$directory->nodeValue,
              'suffix' => $suffix
            );
        }

        foreach ($this->xpath->query('filter/blacklist/file') as $file) {
            $filters['blacklist']['file'][] = (string)$file->nodeValue;
        }

        foreach ($this->xpath->query('filter/whitelist/directory') as $directory) {
            if ($directory->hasAttribute('suffix')) {
                $suffix = (string)$directory->getAttribute('suffix');
            } else {
                $suffix = '.php';
            }

            $filters['whitelist']['directory'][] = array(
              'path'   => (string)$directory->nodeValue,
              'suffix' => $suffix
            );
        }

        foreach ($this->xpath->query('filter/whitelist/file') as $file) {
            $filters['whitelist']['file'][] = (string)$file->nodeValue;
        }

        return $filters;
    }

    /**
     * Returns the configuration for groups.
     *
     * @return array
     * @access public
     * @since  Method available since Release 3.2.1
     */
    public function getGroupConfiguration()
    {
        $groups = array(
          'include' => array(),
          'exclude' => array()
        );

        foreach ($this->xpath->query('groups/include/group') as $group) {
            $groups['include'][] = (string)$group->nodeValue;
        }

        foreach ($this->xpath->query('groups/exclude/group') as $group) {
            $groups['exclude'][] = (string)$group->nodeValue;
        }

        return $groups;
    }

    /**
     * Returns the logging configuration.
     *
     * @return array
     * @access public
     */
    public function getLoggingConfiguration()
    {
        $result = array();

        foreach ($this->xpath->query('logging/log') as $log) {
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

                if ($log->hasAttribute('yui')) {
                    if ((string)$log->getAttribute('yui') == 'true') {
                        $result['yui'] = TRUE;
                    } else {
                        $result['yui'] = FALSE;
                    }
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
     * Returns the PHP configuration.
     *
     * @return array
     * @access public
     * @since  Method available since Release 3.2.1
     */
    public function getPHPConfiguration()
    {
        $result = array(
          'ini' => array(),
          'var' => array()
        );

        foreach ($this->xpath->query('php/ini') as $ini) {
            $name  = (string)$ini->getAttribute('name');
            $value = (string)$ini->getAttribute('value');

            $result['ini'][$name] = $value;
        }

        foreach ($this->xpath->query('php/var') as $var) {
            $name  = (string)$var->getAttribute('name');
            $value = (string)$var->getAttribute('value');

            $result['var'][$name] = $value;
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
        $result = array();

        foreach ($this->xpath->query('logging/pmd/rule') as $rule) {
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

    /**
     * Returns the test suite configuration.
     *
     * @return PHPUnit_Framework_TestSuite
     * @access public
     * @since  Method available since Release 3.2.1
     */
    public function getTestSuiteConfiguration()
    {
        $testSuiteNode = $this->xpath->query('testsuite');

        if ($testSuiteNode->length > 0) {
            $testSuiteNode = $testSuiteNode->item(0);

            if ($testSuiteNode->hasAttribute('name')) {
                $suite = new PHPUnit_Framework_TestSuite(
                  (string)$testSuiteNode->getAttribute('name')
                );
            } else {
                $suite = new PHPUnit_Framework_TestSuite;
            }

            foreach ($this->xpath->query('testsuite/directory') as $directoryNode) {
                if ($directoryNode->hasAttribute('suffix')) {
                    $suffix = (string)$directoryNode->getAttribute('suffix');
                } else {
                    $suffix = 'Test.php';
                }

                $testCollector = new PHPUnit_Runner_IncludePathTestCollector(
                  array((string)$directoryNode->nodeValue),
                  $suffix
                );

                $suite->addTestFiles($testCollector->collectTests());
            }

            foreach ($this->xpath->query('testsuite/file') as $fileNode) {
                $suite->addTestFile((string)$fileNode->nodeValue);
            }

            return $suite;
        }
    }
}
?>
