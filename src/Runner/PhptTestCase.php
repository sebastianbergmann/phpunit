<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner;

use const DEBUG_BACKTRACE_IGNORE_ARGS;
use const DIRECTORY_SEPARATOR;
use function array_merge;
use function basename;
use function debug_backtrace;
use function defined;
use function dirname;
use function explode;
use function extension_loaded;
use function file;
use function file_get_contents;
use function file_put_contents;
use function is_array;
use function is_file;
use function is_readable;
use function is_string;
use function ltrim;
use function phpversion;
use function preg_match;
use function preg_replace;
use function preg_split;
use function realpath;
use function rtrim;
use function sprintf;
use function str_replace;
use function strncasecmp;
use function strpos;
use function substr;
use function trim;
use function unlink;
use function unserialize;
use function var_export;
use function version_compare;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExecutionOrderDependency;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\IncompleteTestError;
use PHPUnit\Framework\PHPTAssertionFailedError;
use PHPUnit\Framework\Reorderable;
use PHPUnit\Framework\SelfDescribing;
use PHPUnit\Framework\SkippedTestError;
use PHPUnit\Framework\SyntheticSkippedError;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestResult;
use PHPUnit\Util\PHP\AbstractPhpProcess;
use SebastianBergmann\CodeCoverage\RawCodeCoverageData;
use SebastianBergmann\Template\Template;
use SebastianBergmann\Timer\Timer;
use Throwable;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class PhptTestCase implements Reorderable, SelfDescribing, Test
{
    /**
     * @var string
     */
    private $filename;

    /**
     * @var AbstractPhpProcess
     */
    private $phpUtil;

    /**
     * @var string
     */
    private $output = '';

    /**
     * Constructs a test case with the given filename.
     *
     * @throws Exception
     */
    public function __construct(string $filename, AbstractPhpProcess $phpUtil = null)
    {
        if (!is_file($filename)) {
            throw new Exception(
                sprintf(
                    'File "%s" does not exist.',
                    $filename
                )
            );
        }

        $this->filename = $filename;
        $this->phpUtil  = $phpUtil ?: AbstractPhpProcess::factory();
    }

    /**
     * Counts the number of test cases executed by run(TestResult result).
     */
    public function count(): int
    {
        return 1;
    }

    /**
     * Runs a test and collects its result in a TestResult instance.
     *
     * @throws Exception
     * @throws \SebastianBergmann\CodeCoverage\InvalidArgumentException
     * @throws \SebastianBergmann\CodeCoverage\UnintentionallyCoveredCodeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function run(TestResult $result = null): TestResult
    {
        if ($result === null) {
            $result = new TestResult;
        }

        try {
            $sections = $this->parse();
        } catch (Exception $e) {
            $result->startTest($this);
            $result->addFailure($this, new SkippedTestError($e->getMessage()), 0);
            $result->endTest($this, 0);

            return $result;
        }

        $code     = $this->render($sections['FILE']);
        $xfail    = false;
        $settings = $this->parseIniSection($this->settings($result->getCollectCodeCoverageInformation()));

        $result->startTest($this);

        if (isset($sections['INI'])) {
            $settings = $this->parseIniSection($sections['INI'], $settings);
        }

        if (isset($sections['ENV'])) {
            $env = $this->parseEnvSection($sections['ENV']);
            $this->phpUtil->setEnv($env);
        }

        $this->phpUtil->setUseStderrRedirection(true);

        if ($result->enforcesTimeLimit()) {
            $this->phpUtil->setTimeout($result->getTimeoutForLargeTests());
        }

        $skip = $this->runSkip($sections, $result, $settings);

        if ($skip) {
            return $result;
        }

        if (isset($sections['XFAIL'])) {
            $xfail = trim($sections['XFAIL']);
        }

        if (isset($sections['STDIN'])) {
            $this->phpUtil->setStdin($sections['STDIN']);
        }

        if (isset($sections['ARGS'])) {
            $this->phpUtil->setArgs($sections['ARGS']);
        }

        if ($result->getCollectCodeCoverageInformation()) {
            $codeCoverageCacheDirectory = null;
            $pathCoverage               = false;

            $codeCoverage = $result->getCodeCoverage();

            if ($codeCoverage) {
                if ($codeCoverage->cachesStaticAnalysis()) {
                    $codeCoverageCacheDirectory = $codeCoverage->cacheDirectory();
                }

                $pathCoverage = $codeCoverage->collectsBranchAndPathCoverage();
            }

            $this->renderForCoverage($code, $pathCoverage, $codeCoverageCacheDirectory);
        }

        $timer = new Timer;
        $timer->start();

        $jobResult    = $this->phpUtil->runJob($code, $this->stringifyIni($settings));
        $time         = $timer->stop()->asSeconds();
        $this->output = $jobResult['stdout'] ?? '';

        if (isset($codeCoverage) && ($coverage = $this->cleanupForCoverage())) {
            $codeCoverage->append($coverage, $this, true, [], []);
        }

        try {
            $this->assertPhptExpectation($sections, $this->output);
        } catch (AssertionFailedError $e) {
            $failure = $e;

            if ($xfail !== false) {
                $failure = new IncompleteTestError($xfail, 0, $e);
            } elseif ($e instanceof ExpectationFailedException) {
                $comparisonFailure = $e->getComparisonFailure();

                if ($comparisonFailure) {
                    $diff = $comparisonFailure->getDiff();
                } else {
                    $diff = $e->getMessage();
                }

                $hint    = $this->getLocationHintFromDiff($diff, $sections);
                $trace   = array_merge($hint, debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
                $failure = new PHPTAssertionFailedError(
                    $e->getMessage(),
                    0,
                    $trace[0]['file'],
                    $trace[0]['line'],
                    $trace,
                    $comparisonFailure ? $diff : ''
                );
            }

            $result->addFailure($this, $failure, $time);
        } catch (Throwable $t) {
            $result->addError($this, $t, $time);
        }

        if ($xfail !== false && $result->allCompletelyImplemented()) {
            $result->addFailure($this, new IncompleteTestError('XFAIL section but test passes'), $time);
        }

        $this->runClean($sections, $result->getCollectCodeCoverageInformation());

        $result->endTest($this, $time);

        return $result;
    }

    /**
     * Returns the name of the test case.
     */
    public function getName(): string
    {
        return $this->toString();
    }

    /**
     * Returns a string representation of the test case.
     */
    public function toString(): string
    {
        return $this->filename;
    }

    public function usesDataProvider(): bool
    {
        return false;
    }

    public function getNumAssertions(): int
    {
        return 1;
    }

    public function getActualOutput(): string
    {
        return $this->output;
    }

    public function hasOutput(): bool
    {
        return !empty($this->output);
    }

    public function sortId(): string
    {
        return $this->filename;
    }

    /**
     * @return list<ExecutionOrderDependency>
     */
    public function provides(): array
    {
        return [];
    }

    /**
     * @return list<ExecutionOrderDependency>
     */
    public function requires(): array
    {
        return [];
    }

    /**
     * Parse --INI-- section key value pairs and return as array.
     *
     * @param array|string $content
     */
    private function parseIniSection($content, array $ini = []): array
    {
        if (is_string($content)) {
            $content = explode("\n", trim($content));
        }

        foreach ($content as $setting) {
            if (strpos($setting, '=') === false) {
                continue;
            }

            $setting = explode('=', $setting, 2);
            $name    = trim($setting[0]);
            $value   = trim($setting[1]);

            if ($name === 'extension' || $name === 'zend_extension') {
                if (!isset($ini[$name])) {
                    $ini[$name] = [];
                }

                $ini[$name][] = $value;

                continue;
            }

            $ini[$name] = $value;
        }

        return $ini;
    }

    private function parseEnvSection(string $content): array
    {
        $env = [];

        foreach (explode("\n", trim($content)) as $e) {
            $e = explode('=', trim($e), 2);

            if (!empty($e[0]) && isset($e[1])) {
                $env[$e[0]] = $e[1];
            }
        }

        return $env;
    }

    /**
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     */
    private function assertPhptExpectation(array $sections, string $output): void
    {
        $assertions = [
            'EXPECT'      => 'assertEquals',
            'EXPECTF'     => 'assertStringMatchesFormat',
            'EXPECTREGEX' => 'assertMatchesRegularExpression',
        ];

        $actual = preg_replace('/\r\n/', "\n", trim($output));

        foreach ($assertions as $sectionName => $sectionAssertion) {
            if (isset($sections[$sectionName])) {
                $sectionContent = preg_replace('/\r\n/', "\n", trim($sections[$sectionName]));
                $expected       = $sectionName === 'EXPECTREGEX' ? "/{$sectionContent}/" : $sectionContent;

                if ($expected === '') {
                    throw new Exception('No PHPT expectation found');
                }

                Assert::$sectionAssertion($expected, $actual);

                return;
            }
        }

        throw new Exception('No PHPT assertion found');
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    private function runSkip(array &$sections, TestResult $result, array $settings): bool
    {
        if (!isset($sections['SKIPIF'])) {
            return false;
        }

        $skipif    = $this->render($sections['SKIPIF']);
        $jobResult = $this->phpUtil->runJob($skipif, $this->stringifyIni($settings));

        if (!strncasecmp('skip', ltrim($jobResult['stdout']), 4)) {
            $message = '';

            if (preg_match('/^\s*skip\s*(.+)\s*/i', $jobResult['stdout'], $skipMatch)) {
                $message = substr($skipMatch[1], 2);
            }

            $hint  = $this->getLocationHint($message, $sections, 'SKIPIF');
            $trace = array_merge($hint, debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
            $result->addFailure(
                $this,
                new SyntheticSkippedError($message, 0, $trace[0]['file'], $trace[0]['line'], $trace),
                0
            );
            $result->endTest($this, 0);

            return true;
        }

        return false;
    }

    private function runClean(array &$sections, bool $collectCoverage): void
    {
        $this->phpUtil->setStdin('');
        $this->phpUtil->setArgs('');

        if (isset($sections['CLEAN'])) {
            $cleanCode = $this->render($sections['CLEAN']);

            $this->phpUtil->runJob($cleanCode, $this->settings($collectCoverage));
        }
    }

    /**
     * @throws Exception
     */
    private function parse(): array
    {
        $sections = [];
        $section  = '';

        $unsupportedSections = [
            'CGI',
            'COOKIE',
            'DEFLATE_POST',
            'EXPECTHEADERS',
            'EXTENSIONS',
            'GET',
            'GZIP_POST',
            'HEADERS',
            'PHPDBG',
            'POST',
            'POST_RAW',
            'PUT',
            'REDIRECTTEST',
            'REQUEST',
        ];

        $lineNr = 0;

        foreach (file($this->filename) as $line) {
            $lineNr++;

            if (preg_match('/^--([_A-Z]+)--/', $line, $result)) {
                $section                        = $result[1];
                $sections[$section]             = '';
                $sections[$section . '_offset'] = $lineNr;

                continue;
            }

            if (empty($section)) {
                throw new Exception('Invalid PHPT file: empty section header');
            }

            $sections[$section] .= $line;
        }

        if (isset($sections['FILEEOF'])) {
            $sections['FILE'] = rtrim($sections['FILEEOF'], "\r\n");
            unset($sections['FILEEOF']);
        }

        $this->parseExternal($sections);

        if (!$this->validate($sections)) {
            throw new Exception('Invalid PHPT file');
        }

        foreach ($unsupportedSections as $section) {
            if (isset($sections[$section])) {
                throw new Exception(
                    "PHPUnit does not support PHPT {$section} sections"
                );
            }
        }

        return $sections;
    }

    /**
     * @throws Exception
     */
    private function parseExternal(array &$sections): void
    {
        $allowSections = [
            'FILE',
            'EXPECT',
            'EXPECTF',
            'EXPECTREGEX',
        ];
        $testDirectory = dirname($this->filename) . DIRECTORY_SEPARATOR;

        foreach ($allowSections as $section) {
            if (isset($sections[$section . '_EXTERNAL'])) {
                $externalFilename = trim($sections[$section . '_EXTERNAL']);

                if (!is_file($testDirectory . $externalFilename) ||
                    !is_readable($testDirectory . $externalFilename)) {
                    throw new Exception(
                        sprintf(
                            'Could not load --%s-- %s for PHPT file',
                            $section . '_EXTERNAL',
                            $testDirectory . $externalFilename
                        )
                    );
                }

                $sections[$section] = file_get_contents($testDirectory . $externalFilename);
            }
        }
    }

    private function validate(array &$sections): bool
    {
        $requiredSections = [
            'FILE',
            [
                'EXPECT',
                'EXPECTF',
                'EXPECTREGEX',
            ],
        ];

        foreach ($requiredSections as $section) {
            if (is_array($section)) {
                $foundSection = false;

                foreach ($section as $anySection) {
                    if (isset($sections[$anySection])) {
                        $foundSection = true;

                        break;
                    }
                }

                if (!$foundSection) {
                    return false;
                }

                continue;
            }

            if (!isset($sections[$section])) {
                return false;
            }
        }

        return true;
    }

    private function render(string $code): string
    {
        return str_replace(
            [
                '__DIR__',
                '__FILE__',
            ],
            [
                "'" . dirname($this->filename) . "'",
                "'" . $this->filename . "'",
            ],
            $code
        );
    }

    private function getCoverageFiles(): array
    {
        $baseDir  = dirname(realpath($this->filename)) . DIRECTORY_SEPARATOR;
        $basename = basename($this->filename, 'phpt');

        return [
            'coverage' => $baseDir . $basename . 'coverage',
            'job'      => $baseDir . $basename . 'php',
        ];
    }

    private function renderForCoverage(string &$job, bool $pathCoverage, ?string $codeCoverageCacheDirectory): void
    {
        $files = $this->getCoverageFiles();

        $template = new Template(
            __DIR__ . '/../Util/PHP/Template/PhptTestCase.tpl'
        );

        $composerAutoload = '\'\'';

        if (defined('PHPUNIT_COMPOSER_INSTALL') && !defined('PHPUNIT_TESTSUITE')) {
            $composerAutoload = var_export(PHPUNIT_COMPOSER_INSTALL, true);
        }

        $phar = '\'\'';

        if (defined('__PHPUNIT_PHAR__')) {
            $phar = var_export(__PHPUNIT_PHAR__, true);
        }

        $globals = '';

        if (!empty($GLOBALS['__PHPUNIT_BOOTSTRAP'])) {
            $globals = '$GLOBALS[\'__PHPUNIT_BOOTSTRAP\'] = ' . var_export(
                $GLOBALS['__PHPUNIT_BOOTSTRAP'],
                true
            ) . ";\n";
        }

        if ($codeCoverageCacheDirectory === null) {
            $codeCoverageCacheDirectory = 'null';
        } else {
            $codeCoverageCacheDirectory = "'" . $codeCoverageCacheDirectory . "'";
        }

        $template->setVar(
            [
                'composerAutoload'           => $composerAutoload,
                'phar'                       => $phar,
                'globals'                    => $globals,
                'job'                        => $files['job'],
                'coverageFile'               => $files['coverage'],
                'driverMethod'               => $pathCoverage ? 'forLineAndPathCoverage' : 'forLineCoverage',
                'codeCoverageCacheDirectory' => $codeCoverageCacheDirectory,
            ]
        );

        file_put_contents($files['job'], $job);

        $job = $template->render();
    }

    private function cleanupForCoverage(): RawCodeCoverageData
    {
        $coverage = RawCodeCoverageData::fromXdebugWithoutPathCoverage([]);
        $files    = $this->getCoverageFiles();

        if (is_file($files['coverage'])) {
            $buffer = @file_get_contents($files['coverage']);

            if ($buffer !== false) {
                $coverage = @unserialize($buffer);

                if ($coverage === false) {
                    $coverage = RawCodeCoverageData::fromXdebugWithoutPathCoverage([]);
                }
            }
        }

        foreach ($files as $file) {
            @unlink($file);
        }

        return $coverage;
    }

    private function stringifyIni(array $ini): array
    {
        $settings = [];

        foreach ($ini as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $val) {
                    $settings[] = $key . '=' . $val;
                }

                continue;
            }

            $settings[] = $key . '=' . $value;
        }

        return $settings;
    }

    private function getLocationHintFromDiff(string $message, array $sections): array
    {
        $needle       = '';
        $previousLine = '';
        $block        = 'message';

        foreach (preg_split('/\r\n|\r|\n/', $message) as $line) {
            $line = trim($line);

            if ($block === 'message' && $line === '--- Expected') {
                $block = 'expected';
            }

            if ($block === 'expected' && $line === '@@ @@') {
                $block = 'diff';
            }

            if ($block === 'diff') {
                if (strpos($line, '+') === 0) {
                    $needle = $this->getCleanDiffLine($previousLine);

                    break;
                }

                if (strpos($line, '-') === 0) {
                    $needle = $this->getCleanDiffLine($line);

                    break;
                }
            }

            if (!empty($line)) {
                $previousLine = $line;
            }
        }

        return $this->getLocationHint($needle, $sections);
    }

    private function getCleanDiffLine(string $line): string
    {
        if (preg_match('/^[\-+]([\'\"]?)(.*)\1$/', $line, $matches)) {
            $line = $matches[2];
        }

        return $line;
    }

    private function getLocationHint(string $needle, array $sections, ?string $sectionName = null): array
    {
        $needle = trim($needle);

        if (empty($needle)) {
            return [[
                'file' => realpath($this->filename),
                'line' => 1,
            ]];
        }

        if ($sectionName) {
            $search = [$sectionName];
        } else {
            $search = [
                // 'FILE',
                'EXPECT',
                'EXPECTF',
                'EXPECTREGEX',
            ];
        }

        $sectionOffset = null;

        foreach ($search as $section) {
            if (!isset($sections[$section])) {
                continue;
            }

            if (isset($sections[$section . '_EXTERNAL'])) {
                $externalFile = trim($sections[$section . '_EXTERNAL']);

                return [
                    [
                        'file' => realpath(dirname($this->filename) . DIRECTORY_SEPARATOR . $externalFile),
                        'line' => 1,
                    ],
                    [
                        'file' => realpath($this->filename),
                        'line' => ($sections[$section . '_EXTERNAL_offset'] ?? 0) + 1,
                    ],
                ];
            }

            $sectionOffset = $sections[$section . '_offset'] ?? 0;
            $offset        = $sectionOffset + 1;

            foreach (preg_split('/\r\n|\r|\n/', $sections[$section]) as $line) {
                if (strpos($line, $needle) !== false) {
                    return [[
                        'file' => realpath($this->filename),
                        'line' => $offset,
                    ]];
                }
                $offset++;
            }
        }

        if ($sectionName) {
            // String not found in specified section, show user the start of the named section
            return [[
                'file' => realpath($this->filename),
                'line' => $sectionOffset,
            ]];
        }

        // No section specified, show user start of code
        return [[
            'file' => realpath($this->filename),
            'line' => 1,
        ]];
    }

    /**
     * @psalm-return list<string>
     */
    private function settings(bool $collectCoverage): array
    {
        $settings = [
            'allow_url_fopen=1',
            'auto_append_file=',
            'auto_prepend_file=',
            'disable_functions=',
            'display_errors=1',
            'docref_ext=.html',
            'docref_root=',
            'error_append_string=',
            'error_prepend_string=',
            'error_reporting=-1',
            'html_errors=0',
            'log_errors=0',
            'open_basedir=',
            'output_buffering=Off',
            'output_handler=',
            'report_memleaks=0',
            'report_zend_debug=0',
        ];

        if (extension_loaded('pcov')) {
            if ($collectCoverage) {
                $settings[] = 'pcov.enabled=1';
            } else {
                $settings[] = 'pcov.enabled=0';
            }
        }

        if (extension_loaded('xdebug')) {
            if (version_compare(phpversion('xdebug'), '3', '>=')) {
                if ($collectCoverage) {
                    $settings[] = 'xdebug.mode=coverage';
                } else {
                    $settings[] = 'xdebug.mode=off';
                }
            } else {
                $settings[] = 'xdebug.default_enable=0';

                if ($collectCoverage) {
                    $settings[] = 'xdebug.coverage_enable=1';
                }
            }
        }

        return $settings;
    }
}
