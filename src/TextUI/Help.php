<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI;

use const PHP_EOL;
use function count;
use function explode;
use function max;
use function preg_replace_callback;
use function str_pad;
use function str_repeat;
use function strlen;
use function wordwrap;
use PHPUnit\Util\Color;
use SebastianBergmann\Environment\Console;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Help
{
    private const LEFT_MARGIN = '  ';

    private const HELP_TEXT = [
        'Usage' => [
            ['text' => 'phpunit [options] UnitTest.php'],
            ['text' => 'phpunit [options] <directory>'],
        ],

        'Configuration' => [
            ['arg' => '--bootstrap <file>', 'desc' => 'A PHP script that is included before the tests run'],
            ['arg' => '-c|--configuration <file>', 'desc' => 'Read configuration from XML file'],
            ['arg' => '--no-configuration', 'desc' => 'Ignore default configuration file (phpunit.xml)'],
            ['arg' => '--no-extensions', 'desc' => 'Do not load PHPUnit extensions'],
            ['arg' => '--include-path <path(s)>', 'desc' => 'Prepend PHP\'s include_path with given path(s)'],
            ['arg' => '-d <key[=value]>', 'desc' => 'Sets a php.ini value'],
            ['arg' => '--cache-directory <dir>', 'desc' => 'Specify cache directory'],
            ['arg' => '--generate-configuration', 'desc' => 'Generate configuration file with suggested settings'],
            ['arg' => '--migrate-configuration', 'desc' => 'Migrate configuration file to current format'],
        ],

        'Selection' => [
            ['arg' => '--list-suites', 'desc' => 'List available test suites'],
            ['arg' => '--testsuite <name>', 'desc' => 'Only run tests from the specified test suite(s)'],
            ['arg' => '--exclude-testsuite <name>', 'desc' => 'Exclude tests from the specified test suite(s)'],
            ['arg' => '--list-groups', 'desc' => 'List available test groups'],
            ['arg' => '--group <name>', 'desc' => 'Only run tests from the specified group(s)'],
            ['arg' => '--exclude-group <name>', 'desc' => 'Exclude tests from the specified group(s)'],
            ['arg' => '--covers <name>', 'desc' => 'Only run tests annotated with "@covers <name>"'],
            ['arg' => '--uses <name>', 'desc' => 'Only run tests annotated with "@uses <name>"'],
            ['arg' => '--list-tests', 'desc' => 'List available tests'],
            ['arg' => '--list-tests-xml <file>', 'desc' => 'List available tests in XML format'],
            ['arg' => '--filter <pattern>', 'desc' => 'Filter which tests to run'],
            ['arg' => '--test-suffix <suffixes>', 'desc' => 'Only search for test in files with specified suffix(es). Default: Test.php,.phpt'],
        ],

        'Execution' => [
            ['arg' => '--process-isolation', 'desc' => 'Run each test in a separate PHP process'],
            ['arg'    => '--globals-backup', 'desc' => 'Backup and restore $GLOBALS for each test'],
            ['arg'    => '--static-backup', 'desc' => 'Backup and restore static properties for each test'],
            ['spacer' => ''],

            ['arg'    => '--strict-coverage', 'desc' => 'Be strict about code coverage attributes and annotations'],
            ['arg'    => '--strict-global-state', 'desc' => 'Be strict about changes to global state'],
            ['arg'    => '--disallow-test-output', 'desc' => 'Be strict about output during tests'],
            ['arg'    => '--enforce-time-limit', 'desc' => 'Enforce time limit based on test size'],
            ['arg'    => '--default-time-limit <sec>', 'desc' => 'Timeout in seconds for tests that have no declared size'],
            ['arg'    => '--dont-report-useless-tests', 'desc' => 'Do not report tests that do not test anything'],
            ['spacer' => ''],

            ['arg'    => '--stop-on-defect', 'desc' => 'Stop execution upon first not-passed test'],
            ['arg'    => '--stop-on-error', 'desc' => 'Stop execution upon first error'],
            ['arg'    => '--stop-on-failure', 'desc' => 'Stop execution upon first error or failure'],
            ['arg'    => '--stop-on-warning', 'desc' => 'Stop execution upon first warning'],
            ['arg'    => '--stop-on-risky', 'desc' => 'Stop execution upon first risky test'],
            ['arg'    => '--stop-on-skipped', 'desc' => 'Stop execution upon first skipped test'],
            ['arg'    => '--stop-on-incomplete', 'desc' => 'Stop execution upon first incomplete test'],
            ['spacer' => ''],

            ['arg'    => '--fail-on-incomplete', 'desc' => 'Treat incomplete tests as failures'],
            ['arg'    => '--fail-on-risky', 'desc' => 'Treat risky tests as failures'],
            ['arg'    => '--fail-on-skipped', 'desc' => 'Treat skipped tests as failures'],
            ['arg'    => '--fail-on-warning', 'desc' => 'Treat tests with warnings as failures'],
            ['spacer' => ''],

            ['arg'    => '--cache-result', 'desc' => 'Write test results to cache file'],
            ['arg'    => '--do-not-cache-result', 'desc' => 'Do not write test results to cache file'],
            ['spacer' => ''],

            ['arg' => '--order-by <order>', 'desc' => 'Run tests in order: default|defects|duration|no-depends|random|reverse|size'],
            ['arg' => '--random-order-seed <N>', 'desc' => 'Use a specific random seed <N> for random order'],
        ],

        'Reporting' => [
            ['arg' => '--colors <flag>', 'desc' => 'Use colors in output ("never", "auto" or "always")'],
            ['arg'    => '--columns <n>', 'desc' => 'Number of columns to use for progress output'],
            ['arg'    => '--columns max', 'desc' => 'Use maximum number of columns for progress output'],
            ['arg'    => '--stderr', 'desc' => 'Write to STDERR instead of STDOUT'],
            ['spacer' => ''],

            ['arg'    => '--no-progress', 'desc' => 'Disable output of test execution progress'],
            ['arg'    => '--no-results', 'desc' => 'Disable output of test results'],
            ['arg'    => '--no-output', 'desc' => 'Disable all output'],
            ['spacer' => ''],

            ['arg'    => '--display-incomplete', 'desc' => 'Display details for incomplete tests'],
            ['arg'    => '--display-skipped', 'desc' => 'Display details for skipped tests'],
            ['arg'    => '--display-deprecations', 'desc' => 'Display details for deprecations triggered by tests'],
            ['arg'    => '--display-errors', 'desc' => 'Display details for errors triggered by tests'],
            ['arg'    => '--display-notices', 'desc' => 'Display details for notices triggered by tests'],
            ['arg'    => '--display-warnings', 'desc' => 'Display details for warnings triggered by tests'],
            ['arg'    => '--reverse-list', 'desc' => 'Print defects in reverse order'],
            ['spacer' => ''],

            ['arg' => '--teamcity', 'desc' => 'Report test execution progress in TeamCity format'],
            ['arg' => '--testdox', 'desc' => 'Report test results in TestDox format'],
        ],

        'Logging' => [
            ['arg' => '--log-junit <file>', 'desc' => 'Log test execution in JUnit XML format to file'],
            ['arg' => '--log-teamcity <file>', 'desc' => 'Log test execution in TeamCity format to file'],
            ['arg' => '--testdox-html <file>', 'desc' => 'Write documentation in HTML format to file'],
            ['arg' => '--testdox-text <file>', 'desc' => 'Write documentation in Text format to file'],
            ['arg' => '--log-events-text <file>', 'desc' => 'Stream events as plain text to file'],
            ['arg' => '--log-events-verbose-text <file>', 'desc' => 'Stream events as plain text to file (with telemetry information)'],
            ['arg' => '--no-logging', 'desc' => 'Ignore logging configuration'],
        ],

        'Code Coverage' => [
            ['arg' => '--coverage-clover <file>', 'desc' => 'Generate code coverage report in Clover XML format'],
            ['arg' => '--coverage-cobertura <file>', 'desc' => 'Generate code coverage report in Cobertura XML format'],
            ['arg' => '--coverage-crap4j <file>', 'desc' => 'Generate code coverage report in Crap4J XML format'],
            ['arg' => '--coverage-html <dir>', 'desc' => 'Generate code coverage report in HTML format'],
            ['arg' => '--coverage-php <file>', 'desc' => 'Export PHP_CodeCoverage object to file'],
            ['arg' => '--coverage-text=<file>', 'desc' => 'Generate code coverage report in text format [default: standard output]'],
            ['arg' => '--coverage-xml <dir>', 'desc' => 'Generate code coverage report in PHPUnit XML format'],
            ['arg' => '--warm-coverage-cache', 'desc' => 'Warm static analysis cache'],
            ['arg' => '--coverage-filter <dir>', 'desc' => 'Include <dir> in code coverage analysis'],
            ['arg' => '--path-coverage', 'desc' => 'Perform path coverage analysis'],
            ['arg' => '--disable-coverage-ignore', 'desc' => 'Disable attributes and annotations for ignoring code coverage'],
            ['arg' => '--no-coverage', 'desc' => 'Ignore code coverage configuration'],
        ],

        'Miscellaneous' => [
            ['arg' => '-h|--help', 'desc' => 'Prints this usage information'],
            ['arg' => '--version', 'desc' => 'Prints the version and exits'],
            ['arg' => '--atleast-version <min>', 'desc' => 'Checks that version is greater than min and exits'],
            ['arg' => '--check-version', 'desc' => 'Check whether PHPUnit is the latest version'],
        ],

    ];
    private int $lengthOfLongestOptionName = 0;
    private readonly int $columnsAvailableForDescription;
    private ?bool $hasColor;

    public function __construct(?int $width = null, ?bool $withColor = null)
    {
        if ($width === null) {
            $width = (new Console)->getNumberOfColumns();
        }

        if ($withColor === null) {
            $this->hasColor = (new Console)->hasColorSupport();
        } else {
            $this->hasColor = $withColor;
        }

        foreach (self::HELP_TEXT as $options) {
            foreach ($options as $option) {
                if (isset($option['arg'])) {
                    $this->lengthOfLongestOptionName = max($this->lengthOfLongestOptionName, isset($option['arg']) ? strlen($option['arg']) : 0);
                }
            }
        }

        $this->columnsAvailableForDescription = $width - $this->lengthOfLongestOptionName - 4;
    }

    public function generate(): string
    {
        if ($this->hasColor) {
            return $this->writeWithColor();
        }

        return $this->writeWithoutColor();
    }

    private function writeWithoutColor(): string
    {
        $buffer = '';

        foreach (self::HELP_TEXT as $section => $options) {
            $buffer .= "{$section}:" . PHP_EOL;

            if ($section !== 'Usage') {
                $buffer .= PHP_EOL;
            }

            foreach ($options as $option) {
                if (isset($option['spacer'])) {
                    $buffer .= PHP_EOL;
                }

                if (isset($option['text'])) {
                    $buffer .= self::LEFT_MARGIN . $option['text'] . PHP_EOL;
                }

                if (isset($option['arg'])) {
                    $arg = str_pad($option['arg'], $this->lengthOfLongestOptionName);

                    $buffer .= self::LEFT_MARGIN . $arg . ' ' . $option['desc'] . PHP_EOL;
                }
            }

            $buffer .= PHP_EOL;
        }

        return $buffer;
    }

    private function writeWithColor(): string
    {
        $buffer = '';

        foreach (self::HELP_TEXT as $section => $options) {
            $buffer .= Color::colorize('fg-yellow', "{$section}:") . PHP_EOL;

            if ($section !== 'Usage') {
                $buffer .= PHP_EOL;
            }

            foreach ($options as $option) {
                if (isset($option['spacer'])) {
                    $buffer .= PHP_EOL;
                }

                if (isset($option['text'])) {
                    $buffer .= self::LEFT_MARGIN . $option['text'] . PHP_EOL;
                }

                if (isset($option['arg'])) {
                    $arg = Color::colorize('fg-green', str_pad($option['arg'], $this->lengthOfLongestOptionName));
                    $arg = preg_replace_callback(
                        '/(<[^>]+>)/',
                        static fn ($matches) => Color::colorize('fg-cyan', $matches[0]),
                        $arg
                    );

                    $desc = explode(PHP_EOL, wordwrap($option['desc'], $this->columnsAvailableForDescription, PHP_EOL));

                    $buffer .= self::LEFT_MARGIN . $arg . ' ' . $desc[0] . PHP_EOL;

                    for ($i = 1; $i < count($desc); $i++) {
                        $buffer .= str_repeat(' ', $this->lengthOfLongestOptionName + 3) . $desc[$i] . PHP_EOL;
                    }
                }
            }

            $buffer .= PHP_EOL;
        }

        return $buffer;
    }
}
