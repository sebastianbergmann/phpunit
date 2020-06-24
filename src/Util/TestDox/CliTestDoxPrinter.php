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

use const PHP_EOL;
use function array_map;
use function ceil;
use function count;
use function explode;
use function get_class;
use function implode;
use function preg_match;
use function sprintf;
use function strlen;
use function strpos;
use function trim;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestResult;
use PHPUnit\Runner\BaseTestRunner;
use PHPUnit\Runner\PhptTestCase;
use PHPUnit\Util\Color;
use SebastianBergmann\Timer\ResourceUsageFormatter;
use SebastianBergmann\Timer\Timer;
use Throwable;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
class CliTestDoxPrinter extends TestDoxPrinter
{
    /**
     * The default Testdox left margin for messages is a vertical line.
     */
    private const PREFIX_SIMPLE = [
        'default' => '│',
        'start'   => '│',
        'message' => '│',
        'diff'    => '│',
        'trace'   => '│',
        'last'    => '│',
    ];

    /**
     * Colored Testdox use box-drawing for a more textured map of the message.
     */
    private const PREFIX_DECORATED = [
        'default' => '│',
        'start'   => '┐',
        'message' => '├',
        'diff'    => '┊',
        'trace'   => '╵',
        'last'    => '┴',
    ];

    private const SPINNER_ICONS = [
        " \e[36m◐\e[0m running tests",
        " \e[36m◓\e[0m running tests",
        " \e[36m◑\e[0m running tests",
        " \e[36m◒\e[0m running tests",
    ];

    private const STATUS_STYLES = [
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
            'symbol'  => '↩',
            'color'   => 'fg-cyan',
            'message' => 'fg-cyan',
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
            'symbol'  => '⚠',
            'color'   => 'fg-yellow',
            'message' => 'fg-yellow',
        ],
        BaseTestRunner::STATUS_UNKNOWN => [
            'symbol'  => '?',
            'color'   => 'fg-blue',
            'message' => 'fg-white,bg-blue',
        ],
    ];

    /**
     * @var int[]
     */
    private $nonSuccessfulTestResults = [];

    /**
     * @var Timer
     */
    private $timer;

    /**
     * @param null|resource|string $out
     * @param int|string           $numberOfColumns
     *
     * @throws \PHPUnit\Framework\Exception
     */
    public function __construct($out = null, bool $verbose = false, string $colors = self::COLOR_DEFAULT, bool $debug = false, $numberOfColumns = 80, bool $reverse = false)
    {
        parent::__construct($out, $verbose, $colors, $debug, $numberOfColumns, $reverse);

        $this->timer = new Timer;

        $this->timer->start();
    }

    public function printResult(TestResult $result): void
    {
        $this->printHeader($result);

        $this->printNonSuccessfulTestsSummary($result->count());

        $this->printFooter($result);
    }

    protected function printHeader(TestResult $result): void
    {
        $this->write("\n" . (new ResourceUsageFormatter)->resourceUsage($this->timer->stop()) . "\n\n");
    }

    protected function formatClassName(Test $test): string
    {
        if ($test instanceof TestCase) {
            return $this->prettifier->prettifyTestClass(get_class($test));
        }

        return get_class($test);
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    protected function registerTestResult(Test $test, ?Throwable $t, int $status, float $time, bool $verbose): void
    {
        if ($status !== BaseTestRunner::STATUS_PASSED) {
            $this->nonSuccessfulTestResults[] = $this->testIndex;
        }

        parent::registerTestResult($test, $t, $status, $time, $verbose);
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
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
            $this->write(PHP_EOL);
        }

        // suite header
        if ($prevResult['className'] !== $result['className']) {
            $this->write($this->colorizeTextBox('underlined', $result['className']) . PHP_EOL);
        }

        // test result line
        if ($this->colors && $result['className'] === PhptTestCase::class) {
            $testName = Color::colorizePath($result['testName'], $prevResult['testName'], true);
        } else {
            $testName = $result['testMethod'];
        }

        $style = self::STATUS_STYLES[$result['status']];
        $line  = sprintf(
            ' %s %s%s' . PHP_EOL,
            $this->colorizeTextBox($style['color'], $style['symbol']),
            $testName,
            $this->verbose ? ' ' . $this->formatRuntime($result['time'], $style['color']) : ''
        );

        $this->write($line);

        // additional information when verbose
        $this->write($result['message']);
    }

    protected function formatThrowable(Throwable $t, ?int $status = null): string
    {
        return trim(\PHPUnit\Framework\TestFailure::exceptionToString($t));
    }

    protected function colorizeMessageAndDiff(string $style, string $buffer): array
    {
        $lines      = $buffer ? array_map('\rtrim', explode(PHP_EOL, $buffer)) : [];
        $message    = [];
        $diff       = [];
        $insideDiff = false;

        foreach ($lines as $line) {
            if ($line === '--- Expected') {
                $insideDiff = true;
            }

            if (!$insideDiff) {
                $message[] = $line;
            } else {
                if (strpos($line, '-') === 0) {
                    $line = Color::colorize('fg-red', Color::visualizeWhitespace($line, true));
                } elseif (strpos($line, '+') === 0) {
                    $line = Color::colorize('fg-green', Color::visualizeWhitespace($line, true));
                } elseif ($line === '@@ @@') {
                    $line = Color::colorize('fg-cyan', $line);
                }
                $diff[] = $line;
            }
        }
        $diff = implode(PHP_EOL, $diff);

        if (!empty($message)) {
            $message = $this->colorizeTextBox($style, implode(PHP_EOL, $message));
        }

        return [$message, $diff];
    }

    protected function formatStacktrace(Throwable $t): string
    {
        $trace = \PHPUnit\Util\Filter::getFilteredStacktrace($t);

        if (!$this->colors) {
            return $trace;
        }

        $lines    = [];
        $prevPath = '';

        foreach (explode(PHP_EOL, $trace) as $line) {
            if (preg_match('/^(.*):(\d+)$/', $line, $matches)) {
                $lines[] = Color::colorizePath($matches[1], $prevPath) .
                    Color::dim(':') .
                    Color::colorize('fg-blue', $matches[2]) .
                    "\n";
                $prevPath = $matches[1];
            } else {
                $lines[]  = $line;
                $prevPath = '';
            }
        }

        return implode('', $lines);
    }

    protected function formatTestResultMessage(Throwable $t, array $result, ?string $prefix = null): string
    {
        $message = $this->formatThrowable($t, $result['status']);
        $diff    = '';

        if (!($this->verbose || $result['verbose'])) {
            return '';
        }

        if ($message && $this->colors) {
            $style            = self::STATUS_STYLES[$result['status']]['message'] ?? '';
            [$message, $diff] = $this->colorizeMessageAndDiff($style, $message);
        }

        if ($prefix === null || !$this->colors) {
            $prefix = self::PREFIX_SIMPLE;
        }

        if ($this->colors) {
            $color  = self::STATUS_STYLES[$result['status']]['color'] ?? '';
            $prefix = array_map(static function ($p) use ($color) {
                return Color::colorize($color, $p);
            }, self::PREFIX_DECORATED);
        }

        $trace = $this->formatStacktrace($t);
        $out   = $this->prefixLines($prefix['start'], PHP_EOL) . PHP_EOL;

        if ($message) {
            $out .= $this->prefixLines($prefix['message'], $message . PHP_EOL) . PHP_EOL;
        }

        if ($diff) {
            $out .= $this->prefixLines($prefix['diff'], $diff . PHP_EOL) . PHP_EOL;
        }

        if ($trace) {
            if ($message || $diff) {
                $out .= $this->prefixLines($prefix['default'], PHP_EOL) . PHP_EOL;
            }
            $out .= $this->prefixLines($prefix['trace'], $trace . PHP_EOL) . PHP_EOL;
        }
        $out .= $this->prefixLines($prefix['last'], PHP_EOL) . PHP_EOL;

        return $out;
    }

    protected function drawSpinner(): void
    {
        if ($this->colors) {
            $id = $this->spinState % count(self::SPINNER_ICONS);
            $this->write(self::SPINNER_ICONS[$id]);
        }
    }

    protected function undrawSpinner(): void
    {
        if ($this->colors) {
            $id = $this->spinState % count(self::SPINNER_ICONS);
            $this->write("\e[1K\e[" . strlen(self::SPINNER_ICONS[$id]) . 'D');
        }
    }

    private function formatRuntime(float $time, string $color = ''): string
    {
        if (!$this->colors) {
            return sprintf('[%.2f ms]', $time * 1000);
        }

        if ($time > 1) {
            $color = 'fg-magenta';
        }

        return Color::colorize($color, ' ' . (int) ceil($time * 1000) . ' ' . Color::dim('ms'));
    }

    private function printNonSuccessfulTestsSummary(int $numberOfExecutedTests): void
    {
        if (empty($this->nonSuccessfulTestResults)) {
            return;
        }

        if ((count($this->nonSuccessfulTestResults) / $numberOfExecutedTests) >= 0.7) {
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
