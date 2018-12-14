<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\TestDox;

use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestResult;
use PHPUnit\Runner\BaseTestRunner;
use PHPUnit\Runner\PhptTestCase;
use SebastianBergmann\Timer\Timer;

/**
 * This printer is for CLI output only. For the classes that output to file, html and xml,
 * please refer to the PHPUnit\Util\TestDox namespace
 */
class CliTestDoxPrinter extends TestDoxPrinter
{
    /**
     * @var int[]
     */
    private $nonSuccessfulTestResults = [];

    private $statusStyles = [
        BaseTestRunner::STATUS_PASSED => [
            'symbol' => '✔',
            'color'  => 'fg-green',
        ],
        BaseTestRunner::STATUS_ERROR => [
            'symbol'  => '✘',
            'color'   => 'fg-yellow',
            'message' => 'bg-yellow,fg-black',
        ],
        BaseTestRunner::STATUS_FAILURE => [
            'symbol'  => '✘',
            'color'   => 'fg-red',
            'message' => 'bg-red,fg-white',
        ],
        BaseTestRunner::STATUS_SKIPPED => [
            'symbol'  => '→',
            'color'   => 'fg-yellow',
            'message' => 'fg-yellow',
        ],
        BaseTestRunner::STATUS_RISKY => [
            'symbol'  => '☢',
            'color'   => 'fg-yellow',
            'message' => 'fg-yellow',
        ],
        BaseTestRunner::STATUS_INCOMPLETE => [
            'symbol'  => '∅',
            'color'   => 'fg-yellow',
            'message' => 'fg-yellow',
        ],
        BaseTestRunner::STATUS_WARNING => [
            'symbol'  => '✘',
            'color'   => 'fg-yellow',
            'message' => 'fg-yellow',
        ],
        BaseTestRunner::STATUS_UNKNOWN => [
            'symbol'  => '?',
            'color'   => 'fg-blue',
            'message' => 'fg-white,bg-blue',
        ],
    ];

    public function printResult(TestResult $result): void
    {
        $this->printHeader();

        $this->printNonSuccessfulTestsSummary($result->count());

        $this->printFooter($result);
    }

    protected function printHeader(): void
    {
        $this->write("\n" . Timer::resourceUsage() . "\n\n");
    }

    protected function formatClassName(Test $test): string
    {
        if ($test instanceof TestCase) {
            return $this->prettifier->prettifyTestClass(\get_class($test));
        }

        return \get_class($test);
    }

    protected function registerTestResult(Test $test, ?\Throwable $t, int $status, float $time, bool $verbose): void
    {
        if ($status !== BaseTestRunner::STATUS_PASSED) {
            $this->nonSuccessfulTestResults[] = $this->testIndex;
        }

        parent::registerTestResult($test, $t, $status, $time, $verbose);
    }

    protected function formatTestName(Test $test): string
    {
        if ($test instanceof TestCase) {
            return $this->prettifier->prettifyTestCase($test);
        }

        return parent::formatTestName($test);
    }

    protected function writeTestResult(array $prevResult, array $result): void
    {
        // spacer line for new suite headers and after verbose messages
        if ($prevResult['testName'] !== '' &&
            (!empty($prevResult['message']) || $prevResult['className'] !== $result['className'])) {
            $this->write("\n");
        }

        // suite header
        if ($prevResult['className'] !== $result['className']) {
            $this->write($this->formatWithColor('underlined', $result['className']) . "\n");
        }

        // test result line
        if ($this->colors && $result['className'] == PhptTestCase::class) {
            $testName = Color::colorizePath($result['testName'], $prevResult['testName'], true);
        } else {
            $testName = $result['testMethod'];
        }
        $style = $this->statusStyles[$result['status']];
        $line  = \sprintf(
            " %s %s%s\n",
            $this->formatWithColor($style['color'], $style['symbol']),
            $testName,
            $this->verbose ? ' ' . $this->formatRuntime($result['time'], $style['color']) : ''
            );
        $this->write($line);

        // additional information when verbose
        $this->write($result['message']);
    }

    protected function formatThrowable(\Throwable $t, ?int $status = null): string
    {
        $message = \trim(\PHPUnit\Framework\TestFailure::exceptionToString($t));
        $message = \implode(\PHP_EOL, \array_map('\trim', \explode(\PHP_EOL, $message)));

        if ($message) {
            if ($this->colors) {
                $style   = $this->statusStyles[$status]['message'] ?? '';
                $message = $this->formatWithColor($style, $message);
            }

            $message .= "\n\n" . $this->formatStacktrace($t);
        } else {
            $message = $this->formatStacktrace($t);
        }

        return $message;
    }

    protected function formatStacktrace(\Throwable $t): string
    {
        $trace = \PHPUnit\Util\Filter::getFilteredStacktrace($t);

        if (!$this->colors) {
            return $trace;
        }

        $lines    = [];
        $prevPath = '';

        foreach (\explode("\n", $trace) as $line) {
            if (\preg_match('/^(.*):(\d+)$/', $line, $matches)) {
                $lines[] =  Color::colorizePath($matches[1], $prevPath) .
                            Color::dim(':') .
                            Color::colorize('fg-blue', $matches[2]) .
                            "\n";
                $prevPath = $matches[1];
            } else {
                $lines[]  = $line;
                $prevPath = '';
            }
        }

        return \implode('', $lines);
    }

    protected function formatTestResultMessage(string $message, int $status, bool $verbose, ?string $prefix = null): string
    {
        if ($message === '') {
            return '';
        }

        if (!($this->verbose || $verbose)) {
            return '';
        }

        if ($prefix === null) {
            $prefix = '│';
        }

        if ($this->colors) {
            $prefix = Color::colorize($this->statusStyles[$status]['color'] ?? '', $prefix);
        }

        return parent::formatTestResultMessage($message, $status, $verbose, $prefix);
    }

    private function formatRuntime(float $time, string $color = ''): string
    {
        if (!$this->colors) {
            return \sprintf('[%.2f ms]', $time * 1000);
        }

        if ($time > 1) {
            $color = 'fg-magenta';
        }

        return Color::colorize($color, \sprintf(' %.2f ms', $time * 1000));
    }

    private function printNonSuccessfulTestsSummary(int $numberOfExecutedTests): void
    {
        if (empty($this->nonSuccessfulTestResults)) {
            return;
        }

        if ((\count($this->nonSuccessfulTestResults) / $numberOfExecutedTests) >= 0.7) {
            return;
        }

        $this->write("Summary of non-successful tests:\n\n");

        $prevResult = $this->getEmptyTestResult();

        foreach ($this->nonSuccessfulTestResults as $testIndex) {
            $result = $this->testResults[$testIndex];
            $this->writeTestResult($prevResult, $result);
            $prevResult = $result;
        }
    }
}
