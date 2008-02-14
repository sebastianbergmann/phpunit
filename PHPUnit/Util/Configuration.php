<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2008, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @copyright  2002-2008 Sebastian Bergmann <sb@sebastian-bergmann.de>
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
 * <phpunit stopOnFailure="false">
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
 *       <exclude>
 *         <directory suffix=".php">/path/to/files</directory>
 *         <file>/path/to/file</file>
 *       </exclude>
 *     </blacklist>
 *     <whitelist addUncoveredFilesFromWhitelist="true">
 *       <directory suffix=".php">/path/to/files</directory>
 *       <file>/path/to/file</file>
 *       <exclude>
 *         <directory suffix=".php">/path/to/files</directory>
 *         <file>/path/to/file</file>
 *       </exclude>
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
 *     <log type="story-html" target="/tmp/story.html"/>
 *     <log type="story-text" target="/tmp/story.txt"/>
 *     <log type="testdox-html" target="/tmp/testdox.html"/>
 *     <log type="testdox-text" target="/tmp/testdox.txt"/>
 * 
 *     <pmd>
 *       <rule class="PHPUnit_Util_Log_PMD_Rule_Project_CRAP"
 *             threshold="5,30" priority="1"/>
 *       <rule class="PHPUnit_Util_Log_PMD_Rule_Class_DepthOfInheritanceTree"
 *             threshold="6" priority="1"/>
 *       <rule class="PHPUnit_Util_Log_PMD_Rule_Class_EfferentCoupling"
 *             threshold="20" priority="1"/>
 *       <rule class="PHPUnit_Util_Log_PMD_Rule_Class_ExcessiveClassLength"
 *             threshold="1000" priority="1"/>
 *       <rule class="PHPUnit_Util_Log_PMD_Rule_Class_ExcessivePublicCount"
 *             threshold="45" priority="1"/>
 *       <rule class="PHPUnit_Util_Log_PMD_Rule_Class_TooManyFields"
 *             threshold="15" priority="1"/>
 *       <rule class="PHPUnit_Util_Log_PMD_Rule_Function_CodeCoverage"
 *             threshold="35,70" priority="1"/>
 *       <rule class="PHPUnit_Util_Log_PMD_Rule_Function_CRAP"
 *             threshold="30" priority="1"/>
 *       <rule class="PHPUnit_Util_Log_PMD_Rule_Function_CyclomaticComplexity"
 *             threshold="20" priority="1"/>
 *       <rule class="PHPUnit_Util_Log_PMD_Rule_Function_ExcessiveMethodLength"
 *             threshold="100" priority="1"/>
 *       <rule class="PHPUnit_Util_Log_PMD_Rule_Function_ExcessiveParameterList"
 *             threshold="10" priority="1"/>
 *       <rule class="PHPUnit_Util_Log_PMD_Rule_Function_NPathComplexity"
 *             threshold="200" priority="1"/>
 *     </pmd>
 *   </logging>
 *
 *   <php>
 *     <ini name="foo" value="bar"/>
 *     <var name="foo" value="bar"/>
 *   </php>
 *
 *   <selenium>
 *     <browser name="" browser="" host="" port="" timeout="">
 *   </selenium>
 * </phpunit>
 * </code>
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2008 Sebastian Bergmann <sb@sebastian-bergmann.de>
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
        $addUncoveredFilesFromWhitelist = TRUE;

        $tmp = $this->xpath->query('filter/whitelist');

        if ($tmp->length == 1 &&
            $tmp->item(0)->hasAttribute('addUncoveredFilesFromWhitelist')) {
            $addUncoveredFilesFromWhitelist = $this->getBoolean(
              (string)$tmp->item(0)->getAttribute('addUncoveredFilesFromWhitelist'),
              TRUE
            );
        }

        return array(
          'blacklist' => array(
            'include' => array(
              'directory' => $this->readFilterDirectories(
                'filter/blacklist/directory'
              ),
              'file' => $this->readFilterFiles(
                'filter/blacklist/file'
              )
            ),
            'exclude' => array(
              'directory' => $this->readFilterDirectories(
                'filter/blacklist/exclude/directory'
               ),
              'file' => $this->readFilterFiles(
                'filter/blacklist/exclude/file'
              )
            )
          ),
          'whitelist' => array(
            'addUncoveredFilesFromWhitelist' => $addUncoveredFilesFromWhitelist,
            'include' => array(
              'directory' => $this->readFilterDirectories(
                'filter/whitelist/directory'
              ),
              'file' => $this->readFilterFiles(
                'filter/whitelist/file'
              )
            ),
            'exclude' => array(
              'directory' => $this->readFilterDirectories(
                'filter/whitelist/exclude/directory'
              ),
              'file' => $this->readFilterFiles(
                'filter/whitelist/exclude/file'
              )
            )
          )
        );
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
                    $result['yui'] = $this->getBoolean(
                      (string)$log->getAttribute('yui'),
                      FALSE
                    );
                }

                if ($log->hasAttribute('highlight')) {
                    $result['highlight'] = $this->getBoolean(
                      (string)$log->getAttribute('highlight'),
                      FALSE
                    );
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
                    $result['logIncompleteSkipped'] = $this->getBoolean(
                      (string)$log->getAttribute('logIncompleteSkipped'),
                      FALSE
                    );
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

            if (strtolower($value) == 'false') {
                $value = FALSE;
            }

            else if (strtolower($value) == 'true') {
                $value = TRUE;
            }

            $result['var'][$name] = $value;
        }

        return $result;
    }

    /**
     * Returns the PHPUnit configuration.
     *
     * @return array
     * @access public
     * @since  Method available since Release 3.2.14
     */
    public function getPHPUnitConfiguration()
    {
        $result = array();

        if ($this->document->documentElement->hasAttribute('stopOnFailure')) {
            $result['stopOnFailure'] = $this->getBoolean(
              (string)$this->document->documentElement('stopOnFailure'),
              FALSE
            );
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

            $priority = (int)$rule->getAttribute('priority');

            $result[$class] = array(
              'threshold' => $threshold,
              'priority'  => $priority
            );
        }

        return $result;
    }

    /**
     * Returns the SeleniumTestCase browser configuration.
     *
     * @return array
     * @access public
     * @since  Method available since Release 3.2.9
     */
    public function getSeleniumBrowserConfiguration()
    {
        $result = array();

        foreach ($this->xpath->query('selenium/browser') as $config) {
            $name    = (string)$config->getAttribute('name');
            $browser = (string)$config->getAttribute('browser');

            if ($config->hasAttribute('host')) {
                $host = (string)$config->getAttribute('host');
            } else {
                $host = 'localhost';
            }

            if ($config->hasAttribute('port')) {
                $host = (int)$config->getAttribute('port');
            } else {
                $host = 4444;
            }

            if ($config->hasAttribute('timeout')) {
                $host = (int)$config->getAttribute('timeout');
            } else {
                $host = 30000;
            }

            $result[] = array(
              'name'    => $name,
              'browser' => $browser,
              'host'    => $host,
              'port'    => $port,
              'timeout' => $timeout
            );
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

    /**
     * @param  string  $value
     * @param  boolean $default
     * @return boolean
     * @access protected
     * @since  Method available since Release 3.2.3
     */
    protected function getBoolean($value, $default)
    {
        if (strtolower($value) == 'false') {
            return FALSE;
        }

        else if (strtolower($value) == 'true') {
            return TRUE;
        }

        return $default;
    }

    /**
     * @param  string $query
     * @return array
     * @access protected
     * @since  Method available since Release 3.2.3
     */
    protected function readFilterDirectories($query)
    {
        $directories = array();

        foreach ($this->xpath->query($query) as $directory) {
            if ($directory->hasAttribute('suffix')) {
                $suffix = (string)$directory->getAttribute('suffix');
            } else {
                $suffix = '.php';
            }

            $directories[] = array(
              'path'   => (string)$directory->nodeValue,
              'suffix' => $suffix
            );
        }

        return $directories;
    }

    /**
     * @param  string $query
     * @return array
     * @access protected
     * @since  Method available since Release 3.2.3
     */
    protected function readFilterFiles($query)
    {
        $files = array();

        foreach ($this->xpath->query($query) as $file) {
            $files[] = (string)$file->nodeValue;
        }

        return $files;
    }
}
?>
