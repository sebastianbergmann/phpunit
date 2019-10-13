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

use PHPUnit\Util\Color;
use SebastianBergmann\Environment\Console;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Help
{
    private const LEFT_MARGIN = '  ';

    private const HELP_TEXT = [
        'Usage'                 => [
            ['text' => 'phpunit [options] UnitTest [UnitTest.php]'],
            ['text' => 'phpunit [options] <directory>'],
        ],
        'Code Coverage Options' => [
            ['arg' => '--coverage-clover <file>', 'desc' => 'Generate code coverage report in Clover XML format'],
            ['arg'  => '--coverage-crap4j <file>', 'desc' => 'Generate code coverage report in Crap4J XML format'],
            ['arg'  => '--coverage-html <dir>', 'desc' => 'Generate code coverage report in HTML format'],
            ['arg'  => '--coverage-php <file>', 'desc' => 'Export PHP_CodeCoverage object to file'],
            ['arg'  => '--coverage-text=<file>', 'desc' => 'Generate code coverage report in text format [default: standard output]'],
            ['arg'  => '--coverage-xml <dir>', 'desc' => 'Generate code coverage report in PHPUnit XML format'],
            ['arg'  => '--whitelist <dir>', 'desc' => 'Whitelist <dir> for code coverage analysis'],
            ['arg'  => '--disable-coverage-ignore', 'desc' => 'Disable annotations for ignoring code coverage'],
            ['arg'  => '--no-coverage', 'desc' => 'Ignore code coverage configuration'],
            ['arg'  => '--dump-xdebug-filter <file>', 'desc' => 'Generate script to set Xdebug code coverage filter'],
        ],

        'Logging Options' => [
            ['arg' => '--log-junit <file>', 'desc' => 'Log test execution in JUnit XML format to file'],
            ['arg' => '--log-teamcity <file>', 'desc' => 'Log test execution in TeamCity format to file'],
            ['arg' => '--testdox-html <file>', 'desc' => 'Write agile documentation in HTML format to file'],
            ['arg' => '--testdox-text <file>', 'desc' => 'Write agile documentation in Text format to file'],
            ['arg' => '--testdox-xml <file>', 'desc' => 'Write agile documentation in XML format to file'],
            ['arg' => '--reverse-list', 'desc' => 'Print defects in reverse order'],
        ],

        'Test Selection Options' => [
            ['arg' => '--filter <pattern>', 'desc' => 'Filter which tests to run'],
            ['arg'  => '--testsuite <name>', 'desc' => 'Filter which testsuite to run'],
            ['arg'  => '--group <name>', 'desc' => 'Only runs tests from the specified group(s)'],
            ['arg'  => '--exclude-group <name>', 'desc' => 'Exclude tests from the specified group(s)'],
            ['arg'  => '--list-groups', 'desc' => 'List available test groups'],
            ['arg'  => '--list-suites', 'desc' => 'List available test suites'],
            ['arg'  => '--list-tests', 'desc' => 'List available tests'],
            ['arg'  => '--list-tests-xml <file>', 'desc' => 'List available tests in XML format'],
            ['arg'  => '--test-suffix <suffixes>', 'desc' => 'Only search for test in files with specified suffix(es). Default: Test.php,.phpt'],
        ],

        'Test Execution Options' => [
            ['arg' => '--dont-report-useless-tests', 'desc' => 'Do not report tests that do not test anything'],
            ['arg'    => '--strict-coverage', 'desc' => 'Be strict about @covers annotation usage'],
            ['arg'    => '--strict-global-state', 'desc' => 'Be strict about changes to global state'],
            ['arg'    => '--disallow-test-output', 'desc' => 'Be strict about output during tests'],
            ['arg'    => '--disallow-resource-usage', 'desc' => 'Be strict about resource usage during small tests'],
            ['arg'    => '--enforce-time-limit', 'desc' => 'Enforce time limit based on test size'],
            ['arg'    => '--default-time-limit=<sec>', 'desc' => 'Timeout in seconds for tests without @small, @medium or @large'],
            ['arg'    => '--disallow-todo-tests', 'desc' => 'Disallow @todo-annotated tests'],
            ['spacer' => ''],

            ['arg'    => '--process-isolation', 'desc' => 'Run each test in a separate PHP process'],
            ['arg'    => '--globals-backup', 'desc' => 'Backup and restore $GLOBALS for each test'],
            ['arg'    => '--static-backup', 'desc' => 'Backup and restore static attributes for each test'],
            ['spacer' => ''],

            ['arg'    => '--colors=<flag>', 'desc' => 'Use colors in output ("never", "auto" or "always")'],
            ['arg'    => '--columns <n>', 'desc' => 'Number of columns to use for progress output'],
            ['arg'    => '--columns max', 'desc' => 'Use maximum number of columns for progress output'],
            ['arg'    => '--stderr', 'desc' => 'Write to STDERR instead of STDOUT'],
            ['arg'    => '--stop-on-defect', 'desc' => 'Stop execution upon first not-passed test'],
            ['arg'    => '--stop-on-error', 'desc' => 'Stop execution upon first error'],
            ['arg'    => '--stop-on-failure', 'desc' => 'Stop execution upon first error or failure'],
            ['arg'    => '--stop-on-warning', 'desc' => 'Stop execution upon first warning'],
            ['arg'    => '--stop-on-risky', 'desc' => 'Stop execution upon first risky test'],
            ['arg'    => '--stop-on-skipped', 'desc' => 'Stop execution upon first skipped test'],
            ['arg'    => '--stop-on-incomplete', 'desc' => 'Stop execution upon first incomplete test'],
            ['arg'    => '--fail-on-warning', 'desc' => 'Treat tests with warnings as failures'],
            ['arg'    => '--fail-on-risky', 'desc' => 'Treat risky tests as failures'],
            ['arg'    => '-v|--verbose', 'desc' => 'Output more verbose information'],
            ['arg'    => '--debug', 'desc' => 'Display debugging information'],
            ['spacer' => ''],

            ['arg'    => '--loader <loader>', 'desc' => 'TestSuiteLoader implementation to use'],
            ['arg'    => '--repeat <times>', 'desc' => 'Runs the test(s) repeatedly'],
            ['arg'    => '--teamcity', 'desc' => 'Report test execution progress in TeamCity format'],
            ['arg'    => '--testdox', 'desc' => 'Report test execution progress in TestDox format'],
            ['arg'    => '--testdox-group', 'desc' => 'Only include tests from the specified group(s)'],
            ['arg'    => '--testdox-exclude-group', 'desc' => 'Exclude tests from the specified group(s)'],
            ['arg'    => '--no-interaction', 'desc' => 'Disable TestDox progress animation'],
            ['arg'    => '--printer <printer>', 'desc' => 'TestListener implementation to use'],
            ['spacer' => ''],

            ['arg'  => '--order-by=<order>', 'desc' => 'Run tests in order: default|defects|duration|no-depends|random|reverse|size'],
            ['arg'  => '--random-order-seed=<N>', 'desc' => 'Use a specific random seed <N> for random order'],
            ['arg'  => '--cache-result', 'desc' => 'Write test results to cache file'],
            ['arg'  => '--do-not-cache-result', 'desc' => 'Do not write test results to cache file'],
        ],

        'Configuration Options' => [
            ['arg' => '--prepend <file>', 'desc' => 'A PHP script that is included as early as possible'],
            ['arg' => '--bootstrap <file>', 'desc' => 'A PHP script that is included before the tests run'],
            ['arg' => '-c|--configuration <file>', 'desc' => 'Read configuration from XML file'],
            ['arg' => '--no-configuration', 'desc' => 'Ignore default configuration file (phpunit.xml)'],
            ['arg' => '--no-logging', 'desc' => 'Ignore logging configuration'],
            ['arg' => '--no-extensions', 'desc' => 'Do not load PHPUnit extensions'],
            ['arg' => '--include-path <path(s)>', 'desc' => 'Prepend PHP\'s include_path with given path(s)'],
            ['arg' => '-d <key[=value]>', 'desc' => 'Sets a php.ini value'],
            ['arg' => '--generate-configuration', 'desc' => 'Generate configuration file with suggested settings'],
            ['arg' => '--cache-result-file=<file>', 'desc' => 'Specify result cache path and filename'],
        ],

        'Miscellaneous Options' => [
            ['arg' => '-h|--help', 'desc' => 'Prints this usage information'],
            ['arg' => '--version', 'desc' => 'Prints the version and exits'],
            ['arg' => '--atleast-version <min>', 'desc' => 'Checks that version is greater than min and exits'],
            ['arg' => '--check-version', 'desc' => 'Check whether PHPUnit is the latest version'],
        ],

    ];

    /**
     * @var int Number of columns required to write the longest option name to the console
     */
    private $maxArgLength = 0;

    /**
     * @var int Number of columns left for the description field after padding and option
     */
    private $maxDescLength;

    /**
     * @var bool Use color highlights for sections, options and parameters
     */
    private $hasColor = false;

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

        foreach (self::HELP_TEXT as $section => $options) {
            foreach ($options as $option) {
                if (isset($option['arg'])) {
                    $this->maxArgLength = \max($this->maxArgLength, isset($option['arg']) ? \strlen($option['arg']) : 0);
                }
            }
        }

        $this->maxDescLength = $width - $this->maxArgLength - 4;
    }

    /**
     * Write the help file to the CLI, adapting width and colors to the console
     */
    public function writeToConsole(): void
    {
        if ($this->hasColor) {
            $this->writeWithColor();
        } else {
            $this->writePlaintext();
        }
    }

    private function writePlaintext(): void
    {
        foreach (self::HELP_TEXT as $section => $options) {
            print "$section:" . \PHP_EOL;

            if ($section !== 'Usage') {
                print \PHP_EOL;
            }

            foreach ($options as $option) {
                if (isset($option['spacer'])) {
                    print \PHP_EOL;
                }

                if (isset($option['text'])) {
                    print self::LEFT_MARGIN . $option['text'] . \PHP_EOL;
                }

                if (isset($option['arg'])) {
                    $arg = \str_pad($option['arg'], $this->maxArgLength);
                    print self::LEFT_MARGIN . $arg . ' ' . $option['desc'] . \PHP_EOL;
                }
            }

            print \PHP_EOL;
        }
    }

    private function writeWithColor(): void
    {
        foreach (self::HELP_TEXT as $section => $options) {
            print Color::colorize('fg-yellow', "$section:") . \PHP_EOL;

            foreach ($options as $option) {
                if (isset($option['spacer'])) {
                    print \PHP_EOL;
                }

                if (isset($option['text'])) {
                    print self::LEFT_MARGIN . $option['text'] . \PHP_EOL;
                }

                if (isset($option['arg'])) {
                    $arg = Color::colorize('fg-green', \str_pad($option['arg'], $this->maxArgLength));
                    $arg = \preg_replace_callback(
                        '/(<[^>]+>)/',
                        static function ($matches) {
                            return Color::colorize('fg-cyan', $matches[0]);
                        },
                        $arg
                    );
                    $desc = \explode(\PHP_EOL, \wordwrap($option['desc'], $this->maxDescLength, \PHP_EOL));

                    print self::LEFT_MARGIN . $arg . ' ' . $desc[0] . \PHP_EOL;

                    for ($i = 1; $i < \count($desc); $i++) {
                        print \str_repeat(' ', $this->maxArgLength + 3) . $desc[$i] . \PHP_EOL;
                    }
                }
            }

            print \PHP_EOL;
        }
    }
}
