<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner;

use File_Iterator_Facade;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Test;
use ReflectionClass;
use ReflectionException;

/**
 * Base class for all test runners.
 */
abstract class BaseTestRunner
{
    const STATUS_PASSED     = 0;
    const STATUS_SKIPPED    = 1;
    const STATUS_INCOMPLETE = 2;
    const STATUS_FAILURE    = 3;
    const STATUS_ERROR      = 4;
    const STATUS_RISKY      = 5;
    const STATUS_WARNING    = 6;
    const SUITE_METHODNAME  = 'suite';

    /**
     * Returns the loader to be used.
     *
     * @return TestSuiteLoader
     */
    public function getLoader()
    {
        return new StandardTestSuiteLoader;
    }

    /**
     * Returns the Test corresponding to the given suite.
     * This is a template method, subclasses override
     * the runFailed() and clearStatus() methods.
     *
     * @param string $suiteClassName
     * @param string $suiteClassFile
     * @param mixed  $suffixes
     *
     * @return Test|null
     */
    public function getTest($suiteClassName, $suiteClassFile = '', $suffixes = '')
    {
        if (\is_dir($suiteClassName) &&
            !\is_file($suiteClassName . '.php') && empty($suiteClassFile)) {
            $facade = new File_Iterator_Facade;
            $files  = $facade->getFilesAsArray(
                $suiteClassName,
                $suffixes
            );

            $suite = new TestSuite($suiteClassName);
            $suite->addTestFiles($files);

            return $suite;
        }

        try {
            $testClass = $this->loadSuiteClass(
                $suiteClassName,
                $suiteClassFile
            );
        } catch (Exception $e) {
            $this->runFailed($e->getMessage());

            return;
        }

        try {
            $suiteMethod = $testClass->getMethod(self::SUITE_METHODNAME);

            if (!$suiteMethod->isStatic()) {
                $this->runFailed(
                    'suite() method must be static.'
                );

                return;
            }

            try {
                $test = $suiteMethod->invoke(null, $testClass->getName());
            } catch (ReflectionException $e) {
                $this->runFailed(
                    \sprintf(
                        "Failed to invoke suite() method.\n%s",
                        $e->getMessage()
                    )
                );

                return;
            }
        } catch (ReflectionException $e) {
            try {
                $test = new TestSuite($testClass);
            } catch (Exception $e) {
                $test = new TestSuite;
                $test->setName($suiteClassName);
            }
        }

        $this->clearStatus();

        return $test;
    }

    /**
     * Returns the loaded ReflectionClass for a suite name.
     *
     * @param string $suiteClassName
     * @param string $suiteClassFile
     *
     * @return ReflectionClass
     */
    protected function loadSuiteClass($suiteClassName, $suiteClassFile = '')
    {
        $loader = $this->getLoader();

        return $loader->load($suiteClassName, $suiteClassFile);
    }

    /**
     * Clears the status message.
     */
    protected function clearStatus()
    {
    }

    /**
     * Override to define how to handle a failed loading of
     * a test suite.
     *
     * @param string $message
     */
    abstract protected function runFailed($message);
}
