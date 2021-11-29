<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Logging\TestDox;

use function in_array;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ErrorTestCase;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestStatus\TestStatus;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use PHPUnit\Framework\WarningTestCase;
use PHPUnit\TextUI\ResultPrinter as ResultPrinterInterface;
use PHPUnit\Util\Printer;
use Throwable;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
abstract class ResultPrinter extends Printer implements ResultPrinterInterface
{
    protected NamePrettifier $prettifier;

    /**
     * @psalm-var class-string
     */
    protected ?string $testClass                   = null;
    protected ?TestStatus $testStatus              = null;
    protected array $tests                         = [];
    protected int $warned                          = 0;
    protected int $failed                          = 0;
    protected int $risky                           = 0;
    protected int $skipped                         = 0;
    protected int $incomplete                      = 0;
    protected ?string $currentTestClassPrettified  = null;
    protected ?string $currentTestMethodPrettified = null;
    private array $groups;
    private array $excludeGroups;

    /**
     * @throws \PHPUnit\Framework\Exception
     */
    public function __construct(string $out, array $groups = [], array $excludeGroups = [])
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

        $this->testStatus = TestStatus::error();
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

        $this->testStatus = TestStatus::warning();
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

        $this->testStatus = TestStatus::failure();
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

        $this->testStatus = TestStatus::incomplete();
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

        $this->testStatus = TestStatus::risky();
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

        $this->testStatus = TestStatus::skipped();
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
     */
    public function startTest(Test $test): void
    {
        if (!$this->isOfInterest($test)) {
            return;
        }

        $class = $test::class;

        if ($this->testClass !== $class) {
            if ($this->testClass !== null) {
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

        $this->testStatus = TestStatus::success();
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
            $this->onTest($test[0], $test[1]->isSuccess());
        }

        $this->endClass((string) $this->testClass);
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

        if ($test instanceof ErrorTestCase || $test instanceof WarningTestCase) {
            return false;
        }

        if (!empty($this->groups)) {
            foreach ($test->groups() as $group) {
                if (in_array($group, $this->groups, true)) {
                    return true;
                }
            }

            return false;
        }

        if (!empty($this->excludeGroups)) {
            foreach ($test->groups() as $group) {
                if (in_array($group, $this->excludeGroups, true)) {
                    return false;
                }
            }

            return true;
        }

        return true;
    }
}
