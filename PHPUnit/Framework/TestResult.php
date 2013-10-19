<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2001-2013, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @subpackage Framework
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 2.0.0
 */

/**
 * A TestResult collects the results of executing a test case.
 *
 * @package    PHPUnit
 * @subpackage Framework
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.0.0
 */
class PHPUnit_Framework_TestResult implements Countable
{
    /**
     * @var boolean
     */
    protected static $xdebugLoaded = NULL;

    /**
     * @var boolean
     */
    protected static $useXdebug = NULL;

    /**
     * @var array
     */
    protected $passed = array();

    /**
     * @var array
     */
    protected $errors = array();

    /**
     * @var array
     */
    protected $deprecatedFeatures = array();

    /**
     * @var array
     */
    protected $failures = array();

    /**
     * @var array
     */
    protected $notImplemented = array();

    /**
     * @var array
     */
    protected $skipped = array();

    /**
     * @var array
     */
    protected $listeners = array();

    /**
     * @var integer
     */
    protected $runTests = 0;

    /**
     * @var float
     */
    protected $time = 0;

    /**
     * @var PHPUnit_Framework_TestSuite
     */
    protected $topTestSuite = NULL;

    /**
     * Check parameter types on function/method calls using function traces from Xdebug
     *
     * @var boolean
     */
    protected $checkParamTypes = FALSE;

    /**
     * Parameter type verification depth
     *
     * @var int
     */
    protected $checkParamTypeDepth = 2;

    /**
     * Parameter type verification - ignore null values as parameters
     *
     * @var boolean
     */
    protected $checkParamTypeIgnoreNull = FALSE;

    /**
     * Code Coverage information.
     *
     * @var PHP_CodeCoverage
     */
    protected $codeCoverage;

    /**
     * @var boolean
     */
    protected $convertErrorsToExceptions = TRUE;

    /**
     * @var boolean
     */
    protected $stop = FALSE;

    /**
     * @var boolean
     */
    protected $stopOnError = FALSE;

    /**
     * @var boolean
     */
    protected $stopOnFailure = FALSE;

    /**
     * @var boolean
     */
    protected $strictMode = FALSE;

    /**
     * @var boolean
     */
    protected $beStrictAboutTestsThatDoNotTestAnything = FALSE;

    /**
     * @var boolean
     */
    protected $beStrictAboutOutputDuringTests = FALSE;

    /**
     * @var boolean
     */
    protected $beStrictAboutTestSize = FALSE;

    /**
     * @var boolean
     */
    protected $stopOnIncomplete = FALSE;

    /**
     * @var boolean
     */
    protected $stopOnSkipped = FALSE;

    /**
     * @var boolean
     */
    protected $lastTestFailed = FALSE;

    /**
     * @var integer
     */
    protected $timeoutForSmallTests = 1;

    /**
     * @var integer
     */
    protected $timeoutForMediumTests = 10;

    /**
     * @var integer
     */
    protected $timeoutForLargeTests = 60;

    /**
     * Registers a TestListener.
     *
     * @param  PHPUnit_Framework_TestListener
     */
    public function addListener(PHPUnit_Framework_TestListener $listener)
    {
        $this->listeners[] = $listener;
    }

    /**
     * Unregisters a TestListener.
     *
     * @param  PHPUnit_Framework_TestListener $listener
     */
    public function removeListener(PHPUnit_Framework_TestListener $listener)
    {
        foreach ($this->listeners as $key => $_listener) {
            if ($listener === $_listener) {
                unset($this->listeners[$key]);
            }
        }
    }

    /**
     * Flushes all flushable TestListeners.
     *
     * @since  Method available since Release 3.0.0
     */
    public function flushListeners()
    {
        foreach ($this->listeners as $listener) {
            if ($listener instanceof PHPUnit_Util_Printer) {
                $listener->flush();
            }
        }
    }

    /**
     * Adds an error to the list of errors.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        if ($e instanceof PHPUnit_Framework_IncompleteTest) {
            $this->notImplemented[] = new PHPUnit_Framework_TestFailure(
              $test, $e
            );

            $notifyMethod = 'addIncompleteTest';

            if ($this->stopOnIncomplete) {
                $this->stop();
            }
        }

        else if ($e instanceof PHPUnit_Framework_SkippedTest) {
            $this->skipped[] = new PHPUnit_Framework_TestFailure($test, $e);
            $notifyMethod    = 'addSkippedTest';

            if ($this->stopOnSkipped) {
                $this->stop();
            }
        }

        else {
            $this->errors[] = new PHPUnit_Framework_TestFailure($test, $e);
            $notifyMethod   = 'addError';

            if ($this->stopOnError || $this->stopOnFailure) {
                $this->stop();
            }
        }

        foreach ($this->listeners as $listener) {
            $listener->$notifyMethod($test, $e, $time);
        }

        $this->lastTestFailed = TRUE;
        $this->time          += $time;
    }

    /**
     * Adds a failure to the list of failures.
     * The passed in exception caused the failure.
     *
     * @param  PHPUnit_Framework_Test                 $test
     * @param  PHPUnit_Framework_AssertionFailedError $e
     * @param  float                                  $time
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        if ($e instanceof PHPUnit_Framework_IncompleteTest) {
            $this->notImplemented[] = new PHPUnit_Framework_TestFailure(
              $test, $e
            );

            $notifyMethod = 'addIncompleteTest';

            if ($this->stopOnIncomplete) {
                $this->stop();
            }
        }

        else if ($e instanceof PHPUnit_Framework_SkippedTest) {
            $this->skipped[] = new PHPUnit_Framework_TestFailure($test, $e);
            $notifyMethod    = 'addSkippedTest';

            if ($this->stopOnSkipped) {
                $this->stop();
            }
        }

        else {
            $this->failures[] = new PHPUnit_Framework_TestFailure($test, $e);
            $notifyMethod     = 'addFailure';

            if ($this->stopOnFailure) {
                $this->stop();
            }
        }

        foreach ($this->listeners as $listener) {
            $listener->$notifyMethod($test, $e, $time);
        }

        $this->lastTestFailed = TRUE;
        $this->time          += $time;
    }

    /**
     * Adds a deprecated feature notice to the list of deprecated features used during run
     *
     * @param PHPUnit_Util_DeprecatedFeature $deprecatedFeature
     */
    public function addDeprecatedFeature(PHPUnit_Util_DeprecatedFeature $deprecatedFeature)
    {
        $this->deprecatedFeatures[] = $deprecatedFeature;
    }

    /**
     * Informs the result that a testsuite will be started.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @since  Method available since Release 2.2.0
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        if ($this->topTestSuite === NULL) {
            $this->topTestSuite = $suite;
        }

        foreach ($this->listeners as $listener) {
            $listener->startTestSuite($suite);
        }
    }

    /**
     * Informs the result that a testsuite was completed.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @since  Method available since Release 2.2.0
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        foreach ($this->listeners as $listener) {
            $listener->endTestSuite($suite);
        }
    }

    /**
     * Informs the result that a test will be started.
     *
     * @param  PHPUnit_Framework_Test $test
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
        $this->lastTestFailed = FALSE;
        $this->runTests      += count($test);

        foreach ($this->listeners as $listener) {
            $listener->startTest($test);
        }
    }

    /**
     * Informs the result that a test was completed.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  float                  $time
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        foreach ($this->listeners as $listener) {
            $listener->endTest($test, $time);
        }

        if (!$this->lastTestFailed && $test instanceof PHPUnit_Framework_TestCase) {
            $class  = get_class($test);
            $key    =  $class . '::' . $test->getName();

            $this->passed[$key] = array(
              'result' => $test->getResult(),
              'size'   => PHPUnit_Util_Test::getSize(
                            $class, $test->getName(FALSE)
                          )
            );

            $this->time += $time;
        }
    }

    /**
     * Returns TRUE if no incomplete test occurred.
     *
     * @return boolean
     */
    public function allCompletelyImplemented()
    {
        return $this->notImplementedCount() == 0;
    }

    /**
     * Gets the number of incomplete tests.
     *
     * @return integer
     */
    public function notImplementedCount()
    {
        return count($this->notImplemented);
    }

    /**
     * Returns an Enumeration for the incomplete tests.
     *
     * @return array
     */
    public function notImplemented()
    {
        return $this->notImplemented;
    }

    /**
     * Returns TRUE if no test has been skipped.
     *
     * @return boolean
     * @since  Method available since Release 3.0.0
     */
    public function noneSkipped()
    {
        return $this->skippedCount() == 0;
    }

    /**
     * Gets the number of skipped tests.
     *
     * @return integer
     * @since  Method available since Release 3.0.0
     */
    public function skippedCount()
    {
        return count($this->skipped);
    }

    /**
     * Returns an Enumeration for the skipped tests.
     *
     * @return array
     * @since  Method available since Release 3.0.0
     */
    public function skipped()
    {
        return $this->skipped;
    }

    /**
     * Gets the number of detected errors.
     *
     * @return integer
     */
    public function errorCount()
    {
        return count($this->errors);
    }

    /**
     * Returns an Enumeration for the errors.
     *
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * Returns an Enumeration for the deprecated features used.
     *
     * @return array
     * @since  Method available since Release 3.5.7
     */
    public function deprecatedFeatures()
    {
        return $this->deprecatedFeatures;
    }

    /**
     * Returns an Enumeration for the deprecated features used.
     *
     * @return array
     * @since  Method available since Release 3.5.7
     */
    public function deprecatedFeaturesCount()
    {
        return count($this->deprecatedFeatures);
    }

    /**
     * Gets the number of detected failures.
     *
     * @return integer
     */
    public function failureCount()
    {
        return count($this->failures);
    }

    /**
     * Returns an Enumeration for the failures.
     *
     * @return array
     */
    public function failures()
    {
        return $this->failures;
    }

    /**
     * Returns the names of the tests that have passed.
     *
     * @return array
     * @since  Method available since Release 3.4.0
     */
    public function passed()
    {
        return $this->passed;
    }

    /**
     * Returns the (top) test suite.
     *
     * @return PHPUnit_Framework_TestSuite
     * @since  Method available since Release 3.0.0
     */
    public function topTestSuite()
    {
        return $this->topTestSuite;
    }

    /**
     * Enables or disables parameter type checking and sets depth
     *
     * @param  boolean $flag
     * @param  int $depth
     * @param  boolean $ignoreNull
     * @throws InvalidArgumentException
     * @since  Method not released yet
     */
    public function checkParamTypes($flag, $depth, $ignoreNull)
    {
        if (is_bool($flag)) {
            $this->checkParamTypes = $flag;
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }
        if (!is_null($depth)) {
            if (is_numeric($depth)) {
                $this->checkParamTypeDepth = $depth;
            } else {
                throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'integer');
            }
        }
        $this->checkParamTypeIgnoreNull = $ignoreNull;
    }

    /**
     * Compares 2 types, including classes
     * First type is the actual type, retrieved from the Xdebug trace
     * Second type is the type (or types) specified in the docblock of the function/method
     *
     * @param string $paramType Can be 'class ClassName' or just the type itself
     * @param string $docblockType The type to compare to
     * @return boolean True if matched, false if not
     */
    private function compareTypes($callType, $docblockType)
    {
        if (trim($callType) == '???') {
            return true;
        }

        /**
         * Main loop, comparing for all types specified in docblock
         * Multiple types must be separated by a pipe (|)
         */
        $docblockTypes = explode('|', $docblockType);
        foreach ($docblockTypes as $docblockType) {
            if ($docblockType == 'mixed') {
                return true;
            }

            preg_match_all('/\w+/', $callType, $callTypes); // Split up by words to get class names (if any)

            if ($callTypes[0][0] == 'class') { // If it's a class, we will get its parent classes and interface, since they are allowed types as well
                $className = $callTypes[0][1];
                $implements = class_implements($className);
                $parents = class_parents($className);
                $callTypes = array_merge(array($className), $implements, $parents);
            } else {
                $callTypes = array($callTypes[0][0]);
            }

            $foundMatch = false;
            foreach ($callTypes as $callType) {
                switch ($callType) {
                    case 'null':
                        if ($this->checkParamTypeIgnoreNull === true) {
                            return true;
                        }
                        break;
                    case $docblockType:
                        return true;
                        break;
                    case 'float':
                        if ($docblockType == 'double' || $docblockType == 'number') {
                            return true;
                        }
                        break;
                    case 'int': // This may seem weird, but we consider that since there's no data loss when passing an int as a float, it's ok
                    case 'long': // Long doesn't exist in PHP, but it seems Xdebug passes it as long in the trace file, which is why it's here
                        if ($docblockType == 'int' || $docblockType == 'integer' || $docblockType == 'long' || $docblockType == 'number' || $docblockType == 'float') {
                            return true;
                        }
                        break;
                    case 'bool':
                        if ($docblockType == 'boolean') {
                            return true;
                        }
                        break;
                }
            }
        }
        return false;
    }

    /**
     * Process the function calls from the Xdebug trace file
     *
     * @param PHPUnit_Framework_Test $test
     * @param string                 $traceFile
     */
    public function processFunctionCalls(PHPUnit_Framework_Test $test, $traceFile)
    {
        $isPHPUnitCode = false; // We won't test code that's part of the PHPUnit suite
        $returnStack = array();

        if (!file_exists($traceFile) || ($handle = fopen($traceFile, 'r')) === false) {
            return false;
        }

        while ($dataLine = fgetcsv($handle, null, "\t")) {
            if (isset($dataLine[5]) && is_numeric($dataLine[0]) && $dataLine[5] == get_class($test) . '->' . $test->getName()) {
                $minimumLevel = $dataLine[0] + 1; // The stack level we start from. We will continue until $minimumLevel + $this->checkParamTypeDepth()
                break;
            }
        }

        while ($dataLine = fgetcsv($handle, null, "\t")) {
            if ($dataLine[0] < $minimumLevel) { // No need to process things that are below the required stack level at the end of a trace
                break;
            }

            if ($dataLine[0] == $minimumLevel) { // At this level, code is either a call to a testable function or a PHPUnit_Framework_Assert (which we won't test)
                if (
                (isset($dataLine[5]) && preg_match('/^PHPUnit_Framework_Assert.*/', $dataLine[5]) == 0) ||
                (!isset($dataLine[5]))
                ) {
                    $isPHPUnitCode = false;
                } else {
                    $isPHPUnitCode = true;
                    continue;
                }
            }

            if ($isPHPUnitCode === true) { // If true, we're deeper than $minimumLevel and within PHPUnit_Framework_Assert
                continue;
            }

            if ($dataLine[0] < $minimumLevel + $this->checkParamTypeDepth) {
                if ($dataLine[2] == 0) { // It's a function/method call
                    preg_match(
                    '/(?P<classOrFunction>\w+){0,1}(?:\:\:|->){0,1}(?P<method>\w+){0,1}/',
                    $dataLine[5],
                    $functionCall
                    );

                    $docBlock = false; // getDocComment will return false if no docblock is present
                    if (!isset($functionCall['method']) && function_exists($functionCall['classOrFunction'])) { // It's a function
                        $calledName = $functionCall['classOrFunction'];
                        $func = new ReflectionFunction($functionCall['classOrFunction']);
                        $docBlock = $func->getDocComment();
                    } elseif (isset($functionCall['method']) && method_exists($functionCall['classOrFunction'], $functionCall['method'])) { // It's a method
                        $calledName = $functionCall['classOrFunction'] . '->' . $functionCall['method'];
                        $method = new ReflectionMethod($functionCall['classOrFunction'], $functionCall['method']);
                        $docBlock = $method->getDocComment();
                    } // else it's an internal function

                    if ($docBlock !== false) {
                        $foundReturn = false;

                        preg_match_all('/\s*\*\s*@(?P<tag>phpunit-no-type-check)/', $docBlock, $noTypeCheck, PREG_SET_ORDER); // Check if @phpunit-no-type-check was in docblock
                        if (count($noTypeCheck) == 0) {

                            // Main docblock parameter and return type loop
                            preg_match_all('/\s*\*\s*@(?P<tag>param|return)\s+(?P<type>\S+)\s+(?P<paramName>\$?\w+)?/', $docBlock, $docBlockVars, PREG_SET_ORDER);
                            for ($cntDocBlockTag = 0; $cntDocBlockTag < count($docBlockVars); $cntDocBlockTag++) {
                                if ($docBlockVars[$cntDocBlockTag]['tag'] == "param") {
                                    if (isset($dataLine[11 + $cntDocBlockTag])) { // Was the call made with this parameter ?
                                        $foundMatch = $this->compareTypes($dataLine[11 + $cntDocBlockTag], $docBlockVars[$cntDocBlockTag]['type']);

                                        if ($foundMatch === false) {
                                            $this->addFailure($test, new PHPUnit_Framework_AssertionFailedError('Invalid type calling ' . $calledName . ' : parameter ' . ($cntDocBlockTag + 1)  . ' (' . $docBlockVars[$cntDocBlockTag]['paramName'] . ') should be of type ' . $docBlockVars[$cntDocBlockTag][2] . ' but got ' . $dataLine[11 + $cntDocBlockTag] . ' instead in ' . $dataLine[8]), 1);
                                        }
                                    }

                                } else { // Put the expected return type on the stack for later use
                                    $returnStack[] = array(
                                        'calledName'    => $calledName,
                                        'calledFrom'    => $dataLine[8] . ':' . $dataLine[9],
                                        'type'          => $docBlockVars[$cntDocBlockTag]['type'],
                                        'noTypeCheck'   => 0
                                    );
                                    $foundReturn = true;
                                }
                            }
                        }

                        if ($foundReturn === false) { // Put this function/method call on the stack
                            $returnStack[] = array(
                                'calledName'    => $calledName,
                                'calledFrom'    => $dataLine[8] . ':' . $dataLine[9],
                                'type'          => '',
                                'noTypeCheck'   => count($noTypeCheck)
                            );
                        }
                    } else {
                        $returnStack[] = array(
                            'calledName'    => $calledName,
                            'calledFrom'    => $dataLine[8] . ':' . $dataLine[9],
                            'type'          => '',
                            'noTypeCheck'   => 1
                        );
                    }

                } else { // It's a return
                    if (isset($dataLine[5])) { // If this isn't set, we have a version of Xdebug that hasn't been patched for Xdebug bug #416
                        $returnPop = array_pop($returnStack);

                        if ($returnPop['noTypeCheck'] == 0 && $returnPop['type'] != '') {
                            preg_match('/(?P<type>\w+)\s?(?P<class>\w+)?/', $dataLine[5], $returnTypes);
                            if (preg_match('/^\'.*/', $dataLine[5]) > 0) { // Strings are not extracted with the above regular expression, but ALWAYS start with a quote in Xdebug trace output
                                $returnType = 'string';
                            } elseif ($returnTypes['type'] == 'NULL') {
                                $returnType = 'null';
                            } elseif ($returnTypes['type'] == 'class') {
                                $returnType = 'class ' . $returnTypes['class'];
                            } elseif ($returnTypes['type'] == 'TRUE' || $returnTypes['type'] == 'FALSE') {
                                $returnType = 'bool';
                            } elseif ($returnTypes['type'] == 'array') {
                                $returnType = 'array';
                            } elseif (is_numeric($returnTypes['type'])) {
                                $returnType = 'int';
                            } else {
                                $returnType = 'unknown';
                            }
                            if (!$this->compareTypes($returnType, $returnPop['type'])) {
                                $this->addFailure($test, new PHPUnit_Framework_AssertionFailedError('Invalid type returned from ' . $returnPop['calledName'] . ' : return should be of type ' . $returnPop['type'] . ' but got ' . $returnType . ' instead (called from ' . $returnPop['calledFrom'] . ')'), 1);
                            }
                        }
                    }
                }
            }
        }
        fclose($handle);
    }

    /**
     * Returns whether code coverage information should be collected.
     *
     * @return boolean If code coverage should be collected
     * @since  Method available since Release 3.2.0
     */
    public function getCollectCodeCoverageInformation()
    {
        return $this->codeCoverage !== NULL;
    }

    /**
     * Returns the strict mode configuration option
     *
     * @return boolean
     */
    public function isStrict()
    {
        return $this->strictMode;
    }

    /**
     * Runs a TestCase.
     *
     * @param  PHPUnit_Framework_Test $test
     */
    public function run(PHPUnit_Framework_Test $test)
    {
        PHPUnit_Framework_Assert::resetCount();

        $error      = FALSE;
        $failure    = FALSE;
        $incomplete = FALSE;
        $skipped    = FALSE;

        $this->startTest($test);

        $errorHandlerSet = FALSE;

        if ($this->convertErrorsToExceptions) {
            $oldErrorHandler = set_error_handler(
              array('PHPUnit_Util_ErrorHandler', 'handleError'),
              E_ALL | E_STRICT
            );

            if ($oldErrorHandler === NULL) {
                $errorHandlerSet = TRUE;
            } else {
                restore_error_handler();
            }
        }

        if (self::$xdebugLoaded === NULL) {
            self::$xdebugLoaded = extension_loaded('xdebug');
            self::$useXdebug    = self::$xdebugLoaded;
        }

        $useXdebug = self::$useXdebug &&
                     $this->codeCoverage !== NULL &&
                     !$test instanceof PHPUnit_Extensions_SeleniumTestCase &&
                     !$test instanceof PHPUnit_Framework_Warning;

        if ($useXdebug) {
            // We need to blacklist test source files when no whitelist is used.
            if (!$this->codeCoverage->filter()->hasWhitelist()) {
                $classes = $this->getHierarchy(get_class($test), TRUE);

                foreach ($classes as $class) {
                    $this->codeCoverage->filter()->addFileToBlacklist(
                      $class->getFileName()
                    );
                }
            }

            $this->codeCoverage->start($test);
        }

        PHP_Timer::start();

        if ($this->checkParamTypes && extension_loaded('xdebug')) {
            ini_set('xdebug.auto_trace', 1);
            ini_set('xdebug.trace_format', 1);
            ini_set('xdebug.collect_return', 1);
            ini_set('xdebug.collect_params', 1);
            ini_set('xdebug.trace_options', 0);
            if (defined('PHPUNIT_TMPDIR')) {
                $tmpDir = PHPUNIT_TMPDIR;
            } else {
                $tmpDir = sys_get_temp_dir();
            }
            $traceFile = tempnam($tmpDir, 'PHPUnit_ParamTypeCheck_');
            $test->setTracefileName($traceFile);
        }

        try {
            if (!$test instanceof PHPUnit_Framework_Warning &&
                $this->beStrictAboutTestSize &&
                extension_loaded('pcntl') && class_exists('PHP_Invoker')) {
                switch ($test->getSize()) {
                    case PHPUnit_Util_Test::SMALL: {
                        $_timeout = $this->timeoutForSmallTests;
                    }
                    break;

                    case PHPUnit_Util_Test::MEDIUM: {
                        $_timeout = $this->timeoutForMediumTests;
                    }
                    break;

                    case PHPUnit_Util_Test::LARGE: {
                        $_timeout = $this->timeoutForLargeTests;
                    }
                    break;
                }

                $invoker = new PHP_Invoker;
                $invoker->invoke(array($test, 'runBare'), array(), $_timeout);
            } else {
                $test->runBare();
            }
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            $failure = TRUE;

            if ($e instanceof PHPUnit_Framework_IncompleteTestError) {
                $incomplete = TRUE;
            }

            else if ($e instanceof PHPUnit_Framework_SkippedTestError) {
                $skipped = TRUE;
            }
        }

        catch (Exception $e) {
            $error = TRUE;
        }

        $time = PHP_Timer::stop();

        if ($this->checkParamTypes && extension_loaded('xdebug')) {
            $this->processFunctionCalls(
                $test, $traceFile . '.xt'
            );
            if (file_exists($traceFile)) {
                unlink($traceFile);
            }
            if (file_exists($traceFile . '.xt')) {
                unlink($traceFile . '.xt');
            }
        }

        $test->addToAssertionCount(PHPUnit_Framework_Assert::getCount());

        if ($this->beStrictAboutTestsThatDoNotTestAnything &&
            $test->getNumAssertions() == 0) {
            $incomplete = TRUE;
        }

        if ($useXdebug) {
            $append           = !$incomplete && !$skipped;
            $linesToBeCovered = array();
            $linesToBeUsed    = array();

            if ($append && $test instanceof PHPUnit_Framework_TestCase) {
                $linesToBeCovered = PHPUnit_Util_Test::getLinesToBeCovered(
                  get_class($test), $test->getName()
                );

                $linesToBeUsed = PHPUnit_Util_Test::getLinesToBeUsed(
                  get_class($test), $test->getName()
                );
            }

            try {
                $this->codeCoverage->stop(
                  $append, $linesToBeCovered, $linesToBeUsed
                );
            }

            catch (PHP_CodeCoverage_Exception_UnintentionallyCoveredCode $cce) {
                $this->addFailure(
                  $test,
                  new PHPUnit_Framework_UnintentionallyCoveredCodeError(
                    'This test executed code that is not listed as code to be covered or used'
                  ),
                  $time
                );
            }

            catch (PHPUnit_Framework_InvalidCoversTargetException $cce) {
                $this->addFailure(
                  $test,
                  new PHPUnit_Framework_InvalidCoversTargetError(
                    $cce->getMessage()
                  ),
                  $time
                );
            }

            catch (PHP_CodeCoverage_Exception $cce) {
                $error = TRUE;

                if (!isset($e)) {
                    $e = $cce;
                }
            }
        }

        if ($errorHandlerSet === TRUE) {
            restore_error_handler();
        }

        if ($error === TRUE) {
            $this->addError($test, $e, $time);
        }

        else if ($failure === TRUE) {
            $this->addFailure($test, $e, $time);
        }

        else if ($this->beStrictAboutTestsThatDoNotTestAnything &&
                 $test->getNumAssertions() == 0) {
            $this->addFailure(
              $test,
              new PHPUnit_Framework_IncompleteTestError(
                'This test did not perform any assertions'
              ),
              $time
            );
        }

        else if ($this->beStrictAboutOutputDuringTests && $test->hasOutput()) {
            $this->addFailure(
              $test,
              new PHPUnit_Framework_OutputError(
                sprintf(
                  'This test printed output: %s',
                  $test->getActualOutput()
                )
              ),
              $time
            );
        }

        $this->endTest($test, $time);
    }

    /**
     * Gets the number of run tests.
     *
     * @return integer
     */
    public function count()
    {
        return $this->runTests;
    }

    /**
     * Checks whether the test run should stop.
     *
     * @return boolean
     */
    public function shouldStop()
    {
        return $this->stop;
    }

    /**
     * Marks that the test run should stop.
     *
     */
    public function stop()
    {
        $this->stop = TRUE;
    }

    /**
     * Returns the PHP_CodeCoverage object.
     *
     * @return PHP_CodeCoverage
     * @since  Method available since Release 3.5.0
     */
    public function getCodeCoverage()
    {
        return $this->codeCoverage;
    }

    /**
     * Returns the PHP_CodeCoverage object.
     *
     * @return PHP_CodeCoverage
     * @since  Method available since Release 3.6.0
     */
    public function setCodeCoverage(PHP_CodeCoverage $codeCoverage)
    {
        $this->codeCoverage = $codeCoverage;
    }

    /**
     * Enables or disables the error-to-exception conversion.
     *
     * @param  boolean $flag
     * @throws PHPUnit_Framework_Exception
     * @since  Method available since Release 3.2.14
     */
    public function convertErrorsToExceptions($flag)
    {
        if (is_bool($flag)) {
            $this->convertErrorsToExceptions = $flag;
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }
    }

    /**
     * Returns the error-to-exception conversion setting.
     *
     * @return boolean
     * @since  Method available since Release 3.4.0
     */
    public function getConvertErrorsToExceptions()
    {
        return $this->convertErrorsToExceptions;
    }

    /**
     * Enables or disables the stopping when an error occurs.
     *
     * @param  boolean $flag
     * @throws PHPUnit_Framework_Exception
     * @since  Method available since Release 3.5.0
     */
    public function stopOnError($flag)
    {
        if (is_bool($flag)) {
            $this->stopOnError = $flag;
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }
    }

    /**
     * Enables or disables the stopping when a failure occurs.
     *
     * @param  boolean $flag
     * @throws PHPUnit_Framework_Exception
     * @since  Method available since Release 3.1.0
     */
    public function stopOnFailure($flag)
    {
        if (is_bool($flag)) {
            $this->stopOnFailure = $flag;
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }
    }

    /**
     * Enables or disables the strict mode.
     *
     * When active
     *   * Tests that do not assert anything will be marked as incomplete.
     *   * Tests that are incomplete or skipped yield no code coverage.
     *
     * @param  boolean $flag
     * @throws PHPUnit_Framework_Exception
     * @since  Method available since Release 3.5.2
     */
    public function strictMode($flag)
    {
        if (is_bool($flag)) {
            $this->strictMode = $flag;

            $this->beStrictAboutTestsThatDoNotTestAnything($flag);
            $this->beStrictAboutOutputDuringTests($flag);
            $this->beStrictAboutTestSize($flag);
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }
    }

    /**
     * @param  boolean $flag
     * @throws PHPUnit_Framework_Exception
     * @since  Method available since Release 3.8.0
     */
    public function beStrictAboutTestsThatDoNotTestAnything($flag)
    {
        if (is_bool($flag)) {
            $this->beStrictAboutTestsThatDoNotTestAnything = $flag;
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }
    }

    /**
     * @param  boolean $flag
     * @throws PHPUnit_Framework_Exception
     * @since  Method available since Release 3.8.0
     */
    public function beStrictAboutOutputDuringTests($flag)
    {
        if (is_bool($flag)) {
            $this->beStrictAboutOutputDuringTests = $flag;
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }
    }

    /**
     * @param  boolean $flag
     * @throws PHPUnit_Framework_Exception
     * @since  Method available since Release 3.8.0
     */
    public function beStrictAboutTestSize($flag)
    {
        if (is_bool($flag)) {
            $this->beStrictAboutTestSize = $flag;
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }
    }

    /**
     * Enables or disables the stopping for incomplete tests.
     *
     * @param  boolean $flag
     * @throws PHPUnit_Framework_Exception
     * @since  Method available since Release 3.5.0
     */
    public function stopOnIncomplete($flag)
    {
        if (is_bool($flag)) {
            $this->stopOnIncomplete = $flag;
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }
    }

    /**
     * Enables or disables the stopping for skipped tests.
     *
     * @param  boolean $flag
     * @throws PHPUnit_Framework_Exception
     * @since  Method available since Release 3.1.0
     */
    public function stopOnSkipped($flag)
    {
        if (is_bool($flag)) {
            $this->stopOnSkipped = $flag;
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }
    }

    /**
     * Returns the time spent running the tests.
     *
     * @return float
     */
    public function time()
    {
        return $this->time;
    }

    /**
     * Returns whether the entire test was successful or not.
     *
     * @return boolean
     */
    public function wasSuccessful()
    {
        return empty($this->errors) && empty($this->failures);
    }

    /**
     * Sets the timeout for small tests.
     *
     * @param  integer $timeout
     * @throws PHPUnit_Framework_Exception
     * @since  Method available since Release 3.6.0
     */
    public function setTimeoutForSmallTests($timeout)
    {
        if (is_integer($timeout)) {
            $this->timeoutForSmallTests = $timeout;
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'integer');
        }
    }

    /**
     * Sets the timeout for medium tests.
     *
     * @param  integer $timeout
     * @throws PHPUnit_Framework_Exception
     * @since  Method available since Release 3.6.0
     */
    public function setTimeoutForMediumTests($timeout)
    {
        if (is_integer($timeout)) {
            $this->timeoutForMediumTests = $timeout;
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'integer');
        }
    }

    /**
     * Sets the timeout for large tests.
     *
     * @param  integer $timeout
     * @throws PHPUnit_Framework_Exception
     * @since  Method available since Release 3.6.0
     */
    public function setTimeoutForLargeTests($timeout)
    {
        if (is_integer($timeout)) {
            $this->timeoutForLargeTests = $timeout;
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'integer');
        }
    }

    /**
     * Returns the class hierarchy for a given class.
     *
     * @param  string  $className
     * @param  boolean $asReflectionObjects
     * @return array
     */
    protected function getHierarchy($className, $asReflectionObjects = FALSE)
    {
        if ($asReflectionObjects) {
            $classes = array(new ReflectionClass($className));
        } else {
            $classes = array($className);
        }

        $done = FALSE;

        while (!$done) {
            if ($asReflectionObjects) {
                $class = new ReflectionClass(
                  $classes[count($classes)-1]->getName()
                );
            } else {
                $class = new ReflectionClass($classes[count($classes)-1]);
            }

            $parent = $class->getParentClass();

            if ($parent !== FALSE) {
                if ($asReflectionObjects) {
                    $classes[] = $parent;
                } else {
                    $classes[] = $parent->getName();
                }
            } else {
                $done = TRUE;
            }
        }

        return $classes;
    }
}
