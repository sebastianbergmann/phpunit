<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\TestDox;

use function get_class;
use function in_array;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use PHPUnit\Framework\WarningTestCase;
use PHPUnit\Runner\BaseTestRunner;
use PHPUnit\TextUI\ResultPrinter as ResultPrinterInterface;
use PHPUnit\Util\Printer;
use Throwable;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
abstract class ResultPrinter extends Printer implements ResultPrinterInterface
{
    /**
     * @var NamePrettifier
     */
    protected $prettifier;

    /**
     * @var string
     */
    protected $testClass = '';

    /**
     * @var int
     */
    protected $testStatus;

    /**
     * @var array
     */
    protected $tests = [];

    /**
     * @var int
     */
    protected $successful = 0;

    /**
     * @var int
     */
    protected $warned = 0;

    /**
     * @var int
     */
    protected $failed = 0;

    /**
     * @var int
     */
    protected $risky = 0;

    /**
     * @var int
     */
    protected $skipped = 0;

    /**
     * @var int
     */
    protected $incomplete = 0;

    /**
     * @var null|string
     */
    protected $currentTestClassPrettified;

    /**
     * @var null|string
     */
    protected $currentTestMethodPrettified;

    /**
     * @var array
     */
    private $groups;

    /**
     * @var array
     */
    private $excludeGroups;

    /**
     * @param resource $out
     *
     * @throws \PHPUnit\Framework\Exception
     */
    public function __construct($out = null, array $groups = [], array $excludeGroups = [])
    {
        parent::__construct($out);

        $this->groups        = $groups;
        $this->excludeGroups = $excludeGroups;

        $this->prettifier = new NamePrettifier;
        $this->startRun();
    }

    /**
     * Flush buffer and close output.
     */
    public function flush(): void
    {
        $this->doEndClass();
        $this->endRun();

        parent::flush();
    }

    /**
     * An error occurred.
     */
    public function addError(Test $test, Throwable $t, float $time): void
    {
        if (!$this->isOfInterest($test)) {
            return;
        }

        $this->testStatus = BaseTestRunner::STATUS_ERROR;
        $this->failed++;
    }

    /**
     * A warning occurred.
     */
    public function addWarning(Test $test, Warning $e, float $time): void
    {
        if (!$this->isOfInterest($test)) {
            return;
        }

        $this->testStatus = BaseTestRunner::STATUS_WARNING;
        $this->warned++;
    }

    /**
     * A failure occurred.
     */
    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
    {
        if (!$this->isOfInterest($test)) {
            return;
        }

        $this->testStatus = BaseTestRunner::STATUS_FAILURE;
        $this->failed++;
    }

    /**
     * Incomplete test.
     */
    public function addIncompleteTest(Test $test, Throwable $t, float $time): void
    {
        if (!$this->isOfInterest($test)) {
            return;
        }

        $this->testStatus = BaseTestRunner::STATUS_INCOMPLETE;
        $this->incomplete++;
    }

    /**
     * Risky test.
     */
    public function addRiskyTest(Test $test, Throwable $t, float $time): void
    {
        if (!$this->isOfInterest($test)) {
            return;
        }

        $this->testStatus = BaseTestRunner::STATUS_RISKY;
        $this->risky++;
    }

    /**
     * Skipped test.
     */
    public function addSkippedTest(Test $test, Throwable $t, float $time): void
    {
        if (!$this->isOfInterest($test)) {
            return;
        }

        $this->testStatus = BaseTestRunner::STATUS_SKIPPED;
        $this->skipped++;
    }

    /**
     * A testsuite started.
     */
    public function startTestSuite(TestSuite $suite): void
    {
    }

    /**
     * A testsuite ended.
     */
    public function endTestSuite(TestSuite $suite): void
    {
    }

    /**
     * A test started.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function startTest(Test $test): void
    {
        if (!$this->isOfInterest($test)) {
            return;
        }

        $class = get_class($test);

        if ($this->testClass !== $class) {
            if ($this->testClass !== '') {
                $this->doEndClass();
            }

            $this->currentTestClassPrettified = $this->prettifier->prettifyTestClass($class);
            $this->testClass                  = $class;
            $this->tests                      = [];

            $this->startClass($class);
        }

        if ($test instanceof TestCase) {
            $this->currentTestMethodPrettified = $this->prettifier->prettifyTestCase($test);
        }

        $this->testStatus = BaseTestRunner::STATUS_PASSED;
    }

    /**
     * A test ended.
     */
    public function endTest(Test $test, float $time): void
    {
        if (!$this->isOfInterest($test)) {
            return;
        }

        $this->tests[] = [$this->currentTestMethodPrettified, $this->testStatus];

        $this->currentTestClassPrettified  = null;
        $this->currentTestMethodPrettified = null;
    }

    protected function doEndClass(): void
    {
        foreach ($this->tests as $test) {
            $this->onTest($test[0], $test[1] === BaseTestRunner::STATUS_PASSED);
        }

        $this->endClass($this->testClass);
    }

    /**
     * Handler for 'start run' event.
     */
    protected function startRun(): void
    {
    }

    /**
     * Handler for 'start class' event.
     */
    protected function startClass(string $name): void
    {
    }

    /**
     * Handler for 'on test' event.
     */
    protected function onTest(string $name, bool $success = true): void
    {
    }

    /**
     * Handler for 'end class' event.
     */
    protected function endClass(string $name): void
    {
    }

    /**
     * Handler for 'end run' event.
     */
    protected function endRun(): void
    {
    }

    private function isOfInterest(Test $test): bool
    {
        if (!$test instanceof TestCase) {
            return false;
        }

        if ($test instanceof WarningTestCase) {
            return false;
        }

        if (!empty($this->groups)) {
            foreach ($test->getGroups() as $group) {
                if (in_array($group, $this->groups, true)) {
                    return true;
                }
            }

            return false;
        }

        if (!empty($this->excludeGroups)) {
            foreach ($test->getGroups() as $group) {
                if (in_array($group, $this->excludeGroups, true)) {
                    return false;
                }
            }

            return true;
        }

        return true;
    }
}
