<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2001-2012, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @package    PHPUnit
 * @subpackage Util
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2012 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.2.0
 */

/**
 * Wrapper for the PHPUnit XML configuration file.
 *
 * Example XML configuration file:
 * <code>
 * <?xml version="1.0" encoding="utf-8" ?>
 *
 * <phpunit backupGlobals="true"
 *          backupStaticAttributes="false"
 *          bootstrap="/path/to/bootstrap.php"
 *          cacheTokens="true"
 *          colors="false"
 *          convertErrorsToExceptions="true"
 *          convertNoticesToExceptions="true"
 *          convertWarningsToExceptions="true"
 *          forceCoversAnnotation="false"
 *          mapTestClassNameToCoveredClassName="false"
 *          printerClass="PHPUnit_TextUI_ResultPrinter"
 *          processIsolation="false"
 *          stopOnError="false"
 *          stopOnFailure="false"
 *          stopOnIncomplete="false"
 *          stopOnSkipped="false"
 *          testSuiteLoaderClass="PHPUnit_Runner_StandardTestSuiteLoader"
 *          timeoutForSmallTests="1"
 *          timeoutForMediumTests="10"
 *          timeoutForLargeTests="60"
 *          strict="false"
 *          verbose="false">
 *   <testsuites>
 *     <testsuite name="My Test Suite">
 *       <directory suffix="Test.php" phpVersion="5.3.0" phpVersionOperator=">=">/path/to/files</directory>
 *       <file phpVersion="5.3.0" phpVersionOperator=">=">/path/to/MyTest.php</file>
 *       <exclude>/path/to/files/exclude</exclude>
 *     </testsuite>
 *   </testsuites>
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
 *     <whitelist processUncoveredFilesFromWhitelist="false">
 *       <directory suffix=".php">/path/to/files</directory>
 *       <file>/path/to/file</file>
 *       <exclude>
 *         <directory suffix=".php">/path/to/files</directory>
 *         <file>/path/to/file</file>
 *       </exclude>
 *     </whitelist>
 *   </filter>
 *
 *   <listeners>
 *     <listener class="MyListener" file="/optional/path/to/MyListener.php">
 *       <arguments>
 *         <array>
 *           <element key="0">
 *             <string>Sebastian</string>
 *           </element>
 *         </array>
 *         <integer>22</integer>
 *         <string>April</string>
 *         <double>19.78</double>
 *         <null/>
 *         <object class="stdClass"/>
 *         <file>MyRelativeFile.php</file>
 *         <directory>MyRelativeDir</directory>
 *       </arguments>
 *     </listener>
 *   </listeners>
 *
 *   <logging>
 *     <log type="coverage-html" target="/tmp/report" title="My Project"
            charset="UTF-8" yui="true" highlight="false"
 *          lowUpperBound="35" highLowerBound="70"/>
 *     <log type="coverage-clover" target="/tmp/clover.xml"/>
 *     <log type="json" target="/tmp/logfile.json"/>
 *     <log type="plain" target="/tmp/logfile.txt"/>
 *     <log type="tap" target="/tmp/logfile.tap"/>
 *     <log type="junit" target="/tmp/logfile.xml" logIncompleteSkipped="false"/>
 *     <log type="testdox-html" target="/tmp/testdox.html"/>
 *     <log type="testdox-text" target="/tmp/testdox.txt"/>
 *   </logging>
 *
 *   <php>
 *     <includePath>.</includePath>
 *     <ini name="foo" value="bar"/>
 *     <const name="foo" value="bar"/>
 *     <var name="foo" value="bar"/>
 *     <env name="foo" value="bar"/>
 *     <post name="foo" value="bar"/>
 *     <get name="foo" value="bar"/>
 *     <cookie name="foo" value="bar"/>
 *     <server name="foo" value="bar"/>
 *     <files name="foo" value="bar"/>
 *     <request name="foo" value="bar"/>
 *   </php>
 *
 *   <selenium>
 *     <browser name="Firefox on Linux"
 *              browser="*firefox /usr/lib/firefox/firefox-bin"
 *              host="my.linux.box"
 *              port="4444"
 *              timeout="30000"/>
 *   </selenium>
 * </phpunit>
 * </code>
 *
 * @package    PHPUnit
 * @subpackage Util
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2012 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.2.0
 */
class PHPUnit_Util_Configuration
{
    private static $instances = array();

    protected $document;
    protected $xpath;
    protected $filename;

    /**
     * Loads a PHPUnit configuration file.
     *
     * @param  string $filename
     */
    protected function __construct($filename)
    {
        $this->filename = $filename;
        $this->document = PHPUnit_Util_XML::loadFile($filename);
        $this->xpath    = new DOMXPath($this->document);
    }

    /**
     * @since  Method available since Release 3.4.0
     */
    private final function __clone()
    {
    }

    /**
     * Returns a PHPUnit configuration object.
     *
     * @param  string $filename
     * @return PHPUnit_Util_Configuration
     * @since  Method available since Release 3.4.0
     */
    public static function getInstance($filename)
    {
        $realpath = realpath($filename);

        if ($realpath === FALSE) {
            throw new PHPUnit_Framework_Exception(
              sprintf(
                'Could not read "%s".',
                $filename
              )
            );
        }

        if (!isset(self::$instances[$realpath])) {
            self::$instances[$realpath] = new PHPUnit_Util_Configuration($realpath);
        }

        return self::$instances[$realpath];
    }

    /**
     * Returns the realpath to the configuration file.
     *
     * @return string
     * @since  Method available since Release 3.6.0
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Returns the configuration for SUT filtering.
     *
     * @return array
     * @since  Method available since Release 3.2.1
     */
    public function getFilterConfiguration()
    {
        $processUncoveredFilesFromWhitelist = FALSE;

        $tmp = $this->xpath->query('filter/whitelist');

        if ($tmp->length == 1 &&
            $tmp->item(0)->hasAttribute('processUncoveredFilesFromWhitelist')) {
            $processUncoveredFilesFromWhitelist = $this->getBoolean(
              (string)$tmp->item(0)->getAttribute(
                'processUncoveredFilesFromWhitelist'
              ),
              FALSE
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
            'processUncoveredFilesFromWhitelist' => $processUncoveredFilesFromWhitelist,
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
     * Returns the configuration for listeners.
     *
     * @return array
     * @since  Method available since Release 3.4.0
     */
    public function getListenerConfiguration()
    {
        $result = array();

        foreach ($this->xpath->query('listeners/listener') as $listener) {
            $class     = (string)$listener->getAttribute('class');
            $file      = '';
            $arguments = array();

            if ($listener->hasAttribute('file')) {
                $file = $this->toAbsolutePath(
                  (string)$listener->getAttribute('file'), TRUE
                );
            }

            if ($listener->childNodes->item(1) instanceof DOMElement &&
                $listener->childNodes->item(1)->tagName == 'arguments') {
                foreach ($listener->childNodes->item(1)->childNodes as $argument) {
                    if ($argument instanceof DOMElement) {
                        if ($argument->tagName == 'file' ||
                            $argument->tagName == 'directory') {
                            $arguments[] = $this->toAbsolutePath((string)$argument->nodeValue);
                        } else {
                            $arguments[] = PHPUnit_Util_XML::xmlToVariable($argument);
                        }
                    }
                }
            }

            $result[] = array(
              'class'     => $class,
              'file'      => $file,
              'arguments' => $arguments
            );
        }

        return $result;
    }

    /**
     * Returns the logging configuration.
     *
     * @return array
     */
    public function getLoggingConfiguration()
    {
        $result = array();

        foreach ($this->xpath->query('logging/log') as $log) {
            $type = (string)$log->getAttribute('type');

            $target = $this->toAbsolutePath(
              (string)$log->getAttribute('target')
            );

            if ($type == 'coverage-html') {
                if ($log->hasAttribute('title')) {
                    $result['title'] = (string)$log->getAttribute('title');
                }

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
                      TRUE
                    );
                }

                if ($log->hasAttribute('highlight')) {
                    $result['highlight'] = $this->getBoolean(
                      (string)$log->getAttribute('highlight'),
                      FALSE
                    );
                }
            }

            else if ($type == 'junit') {
                if ($log->hasAttribute('logIncompleteSkipped')) {
                    $result['logIncompleteSkipped'] = $this->getBoolean(
                      (string)$log->getAttribute('logIncompleteSkipped'),
                      FALSE
                    );
                }
            }

            else if ($type == 'coverage-text') {
                if ($log->hasAttribute('showUncoveredFiles')) {
                    $result['coverageTextShowUncoveredFiles'] = $this->getBoolean(
                      (string)$log->getAttribute('showUncoveredFiles'),
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
     * @since  Method available since Release 3.2.1
     */
    public function getPHPConfiguration()
    {
        $result = array(
          'include_path' => array(),
          'ini'          => array(),
          'const'        => array(),
          'var'          => array(),
          'env'          => array(),
          'post'         => array(),
          'get'          => array(),
          'cookie'       => array(),
          'server'       => array(),
          'files'        => array(),
          'request'      => array()
        );

        foreach ($this->xpath->query('php/includePath') as $includePath) {
            $path = (string)$includePath->nodeValue;

            $result['include_path'][] = $this->toAbsolutePath($path);
        }

        foreach ($this->xpath->query('php/ini') as $ini) {
            $name  = (string)$ini->getAttribute('name');
            $value = (string)$ini->getAttribute('value');

            $result['ini'][$name] = $value;
        }

        foreach ($this->xpath->query('php/const') as $const) {
            $name  = (string)$const->getAttribute('name');
            $value = (string)$const->getAttribute('value');

            $result['const'][$name] = $this->getBoolean($value, $value);
        }

        foreach (array('var', 'env', 'post', 'get', 'cookie', 'server', 'files', 'request') as $array) {
            foreach ($this->xpath->query('php/' . $array) as $var) {
                $name  = (string)$var->getAttribute('name');
                $value = (string)$var->getAttribute('value');

                $result[$array][$name] = $this->getBoolean($value, $value);
            }
        }

        return $result;
    }

    /**
     * Handles the PHP configuration.
     *
     * @since  Method available since Release 3.2.20
     */
    public function handlePHPConfiguration()
    {
        $configuration = $this->getPHPConfiguration();

        if (! empty($configuration['include_path'])) {
            ini_set(
              'include_path',
              implode(PATH_SEPARATOR, $configuration['include_path']) .
              PATH_SEPARATOR .
              ini_get('include_path')
            );
        }

        foreach ($configuration['ini'] as $name => $value) {
            if (defined($value)) {
                $value = constant($value);
            }

            ini_set($name, $value);
        }

        foreach ($configuration['const'] as $name => $value) {
            if (!defined($name)) {
                define($name, $value);
            }
        }

        foreach (array('var', 'env', 'post', 'get', 'cookie', 'server', 'files', 'request') as $array) {
            if ($array == 'var') {
                $target = &$GLOBALS;
            } else {
                $target = &$GLOBALS['_' . strtoupper($array)];
            }

            foreach ($configuration[$array] as $name => $value) {
                $target[$name] = $value;
            }
        }

        foreach ($configuration['env'] as $name => $value) {
            putenv("$name=$value");
        }
    }

    /**
     * Returns the PHPUnit configuration.
     *
     * @return array
     * @since  Method available since Release 3.2.14
     */
    public function getPHPUnitConfiguration()
    {
        $result = array();
        $root   = $this->document->documentElement;

        if ($root->hasAttribute('cacheTokens')) {
            $result['cacheTokens'] = $this->getBoolean(
              (string)$root->getAttribute('cacheTokens'), TRUE
            );
        }

        if ($root->hasAttribute('colors')) {
            $result['colors'] = $this->getBoolean(
              (string)$root->getAttribute('colors'), FALSE
            );
        }

        if ($root->hasAttribute('backupGlobals')) {
            $result['backupGlobals'] = $this->getBoolean(
              (string)$root->getAttribute('backupGlobals'), TRUE
            );
        }

        if ($root->hasAttribute('backupStaticAttributes')) {
            $result['backupStaticAttributes'] = $this->getBoolean(
              (string)$root->getAttribute('backupStaticAttributes'), FALSE
            );
        }

        if ($root->hasAttribute('bootstrap')) {
            $result['bootstrap'] = $this->toAbsolutePath(
              (string)$root->getAttribute('bootstrap')
            );
        }

        if ($root->hasAttribute('convertErrorsToExceptions')) {
            $result['convertErrorsToExceptions'] = $this->getBoolean(
              (string)$root->getAttribute('convertErrorsToExceptions'), TRUE
            );
        }

        if ($root->hasAttribute('convertNoticesToExceptions')) {
            $result['convertNoticesToExceptions'] = $this->getBoolean(
              (string)$root->getAttribute('convertNoticesToExceptions'), TRUE
            );
        }

        if ($root->hasAttribute('convertWarningsToExceptions')) {
            $result['convertWarningsToExceptions'] = $this->getBoolean(
              (string)$root->getAttribute('convertWarningsToExceptions'), TRUE
            );
        }

        if ($root->hasAttribute('forceCoversAnnotation')) {
            $result['forceCoversAnnotation'] = $this->getBoolean(
              (string)$root->getAttribute('forceCoversAnnotation'), FALSE
            );
        }

        if ($root->hasAttribute('mapTestClassNameToCoveredClassName')) {
            $result['mapTestClassNameToCoveredClassName'] = $this->getBoolean(
              (string)$root->getAttribute('mapTestClassNameToCoveredClassName'),
              FALSE
            );
        }

        if ($root->hasAttribute('processIsolation')) {
            $result['processIsolation'] = $this->getBoolean(
              (string)$root->getAttribute('processIsolation'), FALSE
            );
        }

        if ($root->hasAttribute('stopOnError')) {
            $result['stopOnError'] = $this->getBoolean(
              (string)$root->getAttribute('stopOnError'), FALSE
            );
        }

        if ($root->hasAttribute('stopOnFailure')) {
            $result['stopOnFailure'] = $this->getBoolean(
              (string)$root->getAttribute('stopOnFailure'), FALSE
            );
        }

        if ($root->hasAttribute('stopOnIncomplete')) {
            $result['stopOnIncomplete'] = $this->getBoolean(
              (string)$root->getAttribute('stopOnIncomplete'), FALSE
            );
        }

        if ($root->hasAttribute('stopOnSkipped')) {
            $result['stopOnSkipped'] = $this->getBoolean(
              (string)$root->getAttribute('stopOnSkipped'), FALSE
            );
        }

        if ($root->hasAttribute('testSuiteLoaderClass')) {
            $result['testSuiteLoaderClass'] = (string)$root->getAttribute(
              'testSuiteLoaderClass'
            );
        }

        if ($root->hasAttribute('testSuiteLoaderFile')) {
            $result['testSuiteLoaderFile'] = (string)$root->getAttribute(
              'testSuiteLoaderFile'
            );
        }

        if ($root->hasAttribute('printerClass')) {
            $result['printerClass'] = (string)$root->getAttribute(
              'printerClass'
            );
        }

        if ($root->hasAttribute('printerFile')) {
            $result['printerFile'] = (string)$root->getAttribute(
              'printerFile'
            );
        }

        if ($root->hasAttribute('timeoutForSmallTests')) {
            $result['timeoutForSmallTests'] = $this->getInteger(
              (string)$root->getAttribute('timeoutForSmallTests'), 1
            );
        }

        if ($root->hasAttribute('timeoutForMediumTests')) {
            $result['timeoutForMediumTests'] = $this->getInteger(
              (string)$root->getAttribute('timeoutForMediumTests'), 10
            );
        }

        if ($root->hasAttribute('timeoutForLargeTests')) {
            $result['timeoutForLargeTests'] = $this->getInteger(
              (string)$root->getAttribute('timeoutForLargeTests'), 60
            );
        }

        if ($root->hasAttribute('strict')) {
            $result['strict'] = $this->getBoolean(
              (string)$root->getAttribute('strict'), FALSE
            );
        }

        if ($root->hasAttribute('verbose')) {
            $result['verbose'] = $this->getBoolean(
              (string)$root->getAttribute('verbose'), FALSE
            );
        }

        return $result;
    }

    /**
     * Returns the SeleniumTestCase browser configuration.
     *
     * @return array
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
                $port = $this->getInteger(
                  (string)$config->getAttribute('port'), 4444
                );
            } else {
                $port = 4444;
            }

            if ($config->hasAttribute('timeout')) {
                $timeout = $this->getInteger(
                  (string)$config->getAttribute('timeout'), 30000
                );
            } else {
                $timeout = 30000;
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
     * @since  Method available since Release 3.2.1
     */
    public function getTestSuiteConfiguration()
    {
        $testSuiteNodes = $this->xpath->query('testsuites/testsuite');

        if ($testSuiteNodes->length == 0) {
            $testSuiteNodes = $this->xpath->query('testsuite');
        }

        if ($testSuiteNodes->length == 1) {
            return $this->getTestSuite($testSuiteNodes->item(0));
        }

        if ($testSuiteNodes->length > 1) {
            $suite = new PHPUnit_Framework_TestSuite;

            foreach ($testSuiteNodes as $testSuiteNode) {
                $suite->addTestSuite(
                  $this->getTestSuite($testSuiteNode)
                );
            }

            return $suite;
        }
    }

    /**
     * @param  DOMElement $testSuiteNode
     * @return PHPUnit_Framework_TestSuite
     * @since  Method available since Release 3.4.0
     */
    protected function getTestSuite(DOMElement $testSuiteNode)
    {
        if ($testSuiteNode->hasAttribute('name')) {
            $suite = new PHPUnit_Framework_TestSuite(
              (string)$testSuiteNode->getAttribute('name')
            );
        } else {
            $suite = new PHPUnit_Framework_TestSuite;
        }

        $exclude = array();

        foreach ($testSuiteNode->getElementsByTagName('exclude') as $excludeNode) {
            $exclude[] = (string)$excludeNode->nodeValue;
        }

        $fileIteratorFacade = new File_Iterator_Facade;

        foreach ($testSuiteNode->getElementsByTagName('directory') as $directoryNode) {
            $directory = (string)$directoryNode->nodeValue;

            if (empty($directory)) {
                continue;
            }

            if ($directoryNode->hasAttribute('phpVersion')) {
                $phpVersion = (string)$directoryNode->getAttribute('phpVersion');
            } else {
                $phpVersion = PHP_VERSION;
            }

            if ($directoryNode->hasAttribute('phpVersionOperator')) {
                $phpVersionOperator = (string)$directoryNode->getAttribute('phpVersionOperator');
            } else {
                $phpVersionOperator = '>=';
            }

            if (!version_compare(PHP_VERSION, $phpVersion, $phpVersionOperator)) {
                continue;
            }

            if ($directoryNode->hasAttribute('prefix')) {
                $prefix = (string)$directoryNode->getAttribute('prefix');
            } else {
                $prefix = '';
            }

            if ($directoryNode->hasAttribute('suffix')) {
                $suffix = (string)$directoryNode->getAttribute('suffix');
            } else {
                $suffix = 'Test.php';
            }

            $files = $fileIteratorFacade->getFilesAsArray(
              $this->toAbsolutePath($directory),
              $suffix,
              $prefix,
              $exclude
            );
            $suite->addTestFiles($files);
        }

        foreach ($testSuiteNode->getElementsByTagName('file') as $fileNode) {
            $file = (string)$fileNode->nodeValue;

            if (empty($file)) {
                continue;
            }

            // Get the absolute path to the file
            $file = $fileIteratorFacade->getFilesAsArray($file);

            if (!isset($file[0])) {
                continue;
            }

            $file = $file[0];

            if ($fileNode->hasAttribute('phpVersion')) {
                $phpVersion = (string)$fileNode->getAttribute('phpVersion');
            } else {
                $phpVersion = PHP_VERSION;
            }

            if ($fileNode->hasAttribute('phpVersionOperator')) {
                $phpVersionOperator = (string)$fileNode->getAttribute('phpVersionOperator');
            } else {
                $phpVersionOperator = '>=';
            }

            if (!version_compare(PHP_VERSION, $phpVersion, $phpVersionOperator)) {
                continue;
            }

            $suite->addTestFile($file);
        }

        return $suite;
    }

    /**
     * @param  string  $value
     * @param  boolean $default
     * @return boolean
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
     * @param  string  $value
     * @param  boolean $default
     * @return boolean
     * @since  Method available since Release 3.6.0
     */
    protected function getInteger($value, $default)
    {
        if (is_numeric($value)) {
            return (int)$value;
        }

        return $default;
    }

    /**
     * @param  string $query
     * @return array
     * @since  Method available since Release 3.2.3
     */
    protected function readFilterDirectories($query)
    {
        $directories = array();

        foreach ($this->xpath->query($query) as $directory) {
            if ($directory->hasAttribute('prefix')) {
                $prefix = (string)$directory->getAttribute('prefix');
            } else {
                $prefix = '';
            }

            if ($directory->hasAttribute('suffix')) {
                $suffix = (string)$directory->getAttribute('suffix');
            } else {
                $suffix = '.php';
            }

            if ($directory->hasAttribute('group')) {
                $group = (string)$directory->getAttribute('group');
            } else {
                $group = 'DEFAULT';
            }

            $directories[] = array(
              'path'   => $this->toAbsolutePath((string)$directory->nodeValue),
              'prefix' => $prefix,
              'suffix' => $suffix,
              'group'  => $group
            );
        }

        return $directories;
    }

    /**
     * @param  string $query
     * @return array
     * @since  Method available since Release 3.2.3
     */
    protected function readFilterFiles($query)
    {
        $files = array();

        foreach ($this->xpath->query($query) as $file) {
            $files[] = $this->toAbsolutePath((string)$file->nodeValue);
        }

        return $files;
    }

    /**
     * @param  string  $path
     * @param  boolean $useIncludePath
     * @return string
     * @since  Method available since Release 3.5.0
     */
    protected function toAbsolutePath($path, $useIncludePath = FALSE)
    {
        // Check whether the path is already absolute.
        if ($path[0] === '/' || $path[0] === '\\' ||
            (strlen($path) > 3 && ctype_alpha($path[0]) &&
             $path[1] === ':' && ($path[2] === '\\' || $path[2] === '/'))) {
            return $path;
        }

        // Check whether a stream is used.
        if (strpos($path, '://') !== FALSE) {
            return $path;
        }

        $file = dirname($this->filename) . DIRECTORY_SEPARATOR . $path;

        if ($useIncludePath && !file_exists($file)) {
            $includePathFile = stream_resolve_include_path($path);

            if ($includePathFile) {
                $file = $includePathFile;
            }
        }

        return $file;
    }
}
