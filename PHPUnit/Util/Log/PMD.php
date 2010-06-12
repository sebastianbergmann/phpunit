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
require_once 'PHPUnit/Util/Log/PMD/Rule/Class.php';
require_once 'PHPUnit/Util/Log/PMD/Rule/File.php';
require_once 'PHPUnit/Util/Log/PMD/Rule/Function.php';
require_once 'PHPUnit/Util/Log/PMD/Rule/Project.php';
require_once 'PHPUnit/Util/Class.php';
require_once 'PHPUnit/Util/CodeCoverage.php';
require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Util/FilterIterator.php';
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
 * @copyright  2002-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.2.0
 */
class PHPUnit_Util_Log_PMD extends PHPUnit_Util_Printer
{
    protected $added;

    protected $rules = array(
      'project'  => array(),
      'file'     => array(),
      'class'    => array(),
      'function' => array()
    );

    /**
     * Constructor.
     *
     * @param  mixed $out
     * @param  array $configuration
     * @throws InvalidArgumentException
     */
    public function __construct($out = NULL, array $configuration = array())
    {
        parent::__construct($out);
        $this->loadClasses($configuration);
    }

    /**
     * @param  PHPUnit_Framework_TestResult $result
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

        foreach ($this->rules['project'] as $ruleName => $rule) {
            $result = $rule->apply($metrics);

            if ($result !== NULL) {
                $this->addViolation(
                  $result,
                  $pmd,
                  $rule
                );
            }
        }

        foreach ($metrics->getFiles() as $fileName => $fileMetrics) {
            $xmlFile = $document->createElement('file');
            $xmlFile->setAttribute('name', $fileName);

            $this->added = FALSE;

            foreach ($this->rules['file'] as $ruleName => $rule) {
                $result = $rule->apply($fileMetrics);

                if ($result !== NULL) {
                    $this->addViolation(
                      $result,
                      $xmlFile,
                      $rule
                    );

                    $this->added = TRUE;
                }
            }

            foreach ($fileMetrics->getClasses() as $className => $classMetrics) {
                if (!$classMetrics->getClass()->isInterface()) {
                    $classStartLine = $classMetrics->getClass()->getStartLine();
                    $classEndLine   = $classMetrics->getClass()->getEndLine();
                    $classPackage   = $classMetrics->getPackage();

                    foreach ($this->rules['class'] as $ruleName => $rule) {
                        $result = $rule->apply($classMetrics);

                        if ($result !== NULL) {
                            $this->addViolation(
                              $result,
                              $xmlFile,
                              $rule,
                              $classStartLine,
                              $classEndLine,
                              $classPackage,
                              $className
                            );

                            $this->added = TRUE;
                        }
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
     * @param  string                    $violation
     * @param  DOMElement                $element
     * @param  PHPUnit_Util_Log_PMD_Rule $rule
     * @param  integer                   $line
     * @param  integer                   $toLine
     * @param  string                    $package
     * @param  string                    $class
     * @param  string                    $method
     */
    protected function addViolation($violation, DOMElement $element, PHPUnit_Util_Log_PMD_Rule $rule, $line = '', $toLine = '', $package = '', $class = '', $method = '', $function = '')
    {
        $violationXml = $element->appendChild(
          $element->ownerDocument->createElement('violation', $violation)
        );

        $violationXml->setAttribute('rule', $rule->getName());
        $violationXml->setAttribute('priority', $rule->getPriority());

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

        foreach ($this->rules['function'] as $ruleName => $rule) {
            $result = $rule->apply($metrics);

            if ($result !== NULL) {
                $this->addViolation(
                  $result,
                  $element,
                  $rule,
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

    protected function loadClasses(array $configuration)
    {
        $basedir = dirname(__FILE__) . DIRECTORY_SEPARATOR .
                   'PMD' . DIRECTORY_SEPARATOR . 'Rule';

        $dirs = array(
          $basedir . DIRECTORY_SEPARATOR . 'Class',
          $basedir . DIRECTORY_SEPARATOR . 'File',
          $basedir . DIRECTORY_SEPARATOR . 'Function',
          $basedir . DIRECTORY_SEPARATOR . 'Project'
        );

        foreach ($dirs as $dir) {
            if (file_exists($dir)) {
                $iterator = new PHPUnit_Util_FilterIterator(
                  new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($dir)
                  ),
                  '.php'
                );

                foreach ($iterator as $file) {
                    include_once $file->getPathname();
                }
            }
        }

        $classes = get_declared_classes();

        foreach ($classes as $className) {
            $class = new ReflectionClass($className);

            if (!$class->isAbstract() && $class->isSubclassOf('PHPUnit_Util_Log_PMD_Rule')) {
                $rule = explode('_', $className);
                $rule = $rule[count($rule)-1];

                if (isset($configuration[$className])) {
                    $object = new $className(
                      $configuration[$className]['threshold'],
                      $configuration[$className]['priority']
                    );
                } else {
                    $object = new $className;
                }

                if ($class->isSubclassOf('PHPUnit_Util_Log_PMD_Rule_Project')) {
                    $this->rules['project'][$rule] = $object;
                }

                if ($class->isSubclassOf('PHPUnit_Util_Log_PMD_Rule_File')) {
                    $this->rules['file'][$rule] = $object;
                }

                else if ($class->isSubclassOf('PHPUnit_Util_Log_PMD_Rule_Class')) {
                    $this->rules['class'][$rule] = $object;
                }

                else if ($class->isSubclassOf('PHPUnit_Util_Log_PMD_Rule_Function')) {
                    $this->rules['function'][$rule] = $object;
                }
            }
        }
    }
}
?>