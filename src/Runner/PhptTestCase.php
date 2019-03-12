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

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\IncompleteTestError;
use PHPUnit\Framework\PHPTAssertionFailedError;
use PHPUnit\Framework\SelfDescribing;
use PHPUnit\Framework\SkippedTestError;
use PHPUnit\Framework\SyntheticSkippedError;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestResult;
use PHPUnit\Util\PHP\AbstractPhpProcess;
use SebastianBergmann\Timer\Timer;
use Text_Template;
use Throwable;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class PhptTestCase implements Test, SelfDescribing
{
    /**
     * @var string[]
     */
    private const SETTINGS = [
        'allow_url_fopen=1',
        'auto_append_file=',
        'auto_prepend_file=',
        'disable_functions=',
        'display_errors=1',
        'docref_root=',
        'docref_ext=.html',
        'error_append_string=',
        'error_prepend_string=',
        'error_reporting=-1',
        'html_errors=0',
        'log_errors=0',
        'magic_quotes_runtime=0',
        'output_handler=',
        'open_basedir=',
        'output_buffering=Off',
        'report_memleaks=0',
        'report_zend_debug=0',
        'safe_mode=0',
        'xdebug.default_enable=0',
    ];

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
        if (!\is_file($filename)) {
            throw new Exception(
                \sprintf(
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
     * @throws \ReflectionException
     * @throws \SebastianBergmann\CodeCoverage\CoveredCodeNotExecutedException
     * @throws \SebastianBergmann\CodeCoverage\InvalidArgumentException
     * @throws \SebastianBergmann\CodeCoverage\MissingCoversAnnotationException
     * @throws \SebastianBergmann\CodeCoverage\RuntimeException
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
        $settings = $this->parseIniSection(self::SETTINGS);

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
            $xfail = \trim($sections['XFAIL']);
        }

        if (isset($sections['STDIN'])) {
            $this->phpUtil->setStdin($sections['STDIN']);
        }

        if (isset($sections['ARGS'])) {
            $this->phpUtil->setArgs($sections['ARGS']);
        }

        if ($result->getCollectCodeCoverageInformation()) {
            $this->renderForCoverage($code);
        }

        Timer::start();

        $jobResult    = $this->phpUtil->runJob($code, $this->stringifyIni($settings));
        $time         = Timer::stop();
        $this->output = $jobResult['stdout'] ?? '';

        if ($result->getCollectCodeCoverageInformation() && ($coverage = $this->cleanupForCoverage())) {
            $result->getCodeCoverage()->append($coverage, $this, true, [], [], true);
        }

        try {
            $this->assertPhptExpectation($sections, $jobResult['stdout']);
        } catch (AssertionFailedError $e) {
            $failure = $e;

            if ($xfail !== false) {
                $failure = new IncompleteTestError($xfail, 0, $e);
            } elseif ($e instanceof ExpectationFailedException) {
                if ($e->getComparisonFailure()) {
                    $diff = $e->getComparisonFailure()->getDiff();
                } else {
                    $diff = $e->getMessage();
                }

                $hint    = $this->getLocationHintFromDiff($diff, $sections);
                $trace   = \array_merge($hint, \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS));
                $failure = new PHPTAssertionFailedError(
                    $e->getMessage(),
                    0,
                    $trace[0]['file'],
                    $trace[0]['line'],
                    $trace
                );
            }

            $result->addFailure($this, $failure, $time);
        } catch (Throwable $t) {
            $result->addError($this, $t, $time);
        }

        if ($xfail !== false && $result->allCompletelyImplemented()) {
            $result->addFailure($this, new IncompleteTestError('XFAIL section but test passes'), $time);
        }

        $this->runClean($sections);

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

    /**
     * Parse --INI-- section key value pairs and return as array.
     *
     * @param array|string
     */
    private function parseIniSection($content, $ini = []): array
    {
        if (\is_string($content)) {
            $content = \explode("\n", \trim($content));
        }

        foreach ($content as $setting) {
            if (\strpos($setting, '=') === false) {
                continue;
            }

            $setting = \explode('=', $setting, 2);
            $name    = \trim($setting[0]);
            $value   = \trim($setting[1]);

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

        foreach (\explode("\n", \trim($content)) as $e) {
            $e = \explode('=', \trim($e), 2);

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
            'EXPECTREGEX' => 'assertRegExp',
        ];

        $actual = \preg_replace('/\r\n/', "\n", \trim($output));

        foreach ($assertions as $sectionName => $sectionAssertion) {
            if (isset($sections[$sectionName])) {
                $sectionContent = \preg_replace('/\r\n/', "\n", \trim($sections[$sectionName]));
                $expected       = $sectionName === 'EXPECTREGEX' ? "/{$sectionContent}/" : $sectionContent;

                if ($expected === null) {
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

        if (!\strncasecmp('skip', \ltrim($jobResult['stdout']), 4)) {
            $message = '';

            if (\preg_match('/^\s*skip\s*(.+)\s*/i', $jobResult['stdout'], $skipMatch)) {
                $message = \substr($skipMatch[1], 2);
            }

            $hint  = $this->getLocationHint($message, $sections, 'SKIPIF');
            $trace = \array_merge($hint, \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS));
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

    private function runClean(array &$sections): void
    {
        $this->phpUtil->setStdin('');
        $this->phpUtil->setArgs('');

        if (isset($sections['CLEAN'])) {
            $cleanCode = $this->render($sections['CLEAN']);

            $this->phpUtil->runJob($cleanCode, self::SETTINGS);
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
            'REDIRECTTEST',
            'REQUEST',
            'POST',
            'PUT',
            'POST_RAW',
            'GZIP_POST',
            'DEFLATE_POST',
            'GET',
            'COOKIE',
            'HEADERS',
            'CGI',
            'EXPECTHEADERS',
            'EXTENSIONS',
            'PHPDBG',
        ];

        $lineNr = 0;

        foreach (\file($this->filename) as $line) {
            $lineNr++;

            if (\preg_match('/^--([_A-Z]+)--/', $line, $result)) {
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
            $sections['FILE'] = \rtrim($sections['FILEEOF'], "\r\n");
            unset($sections['FILEEOF']);
        }

        $this->parseExternal($sections);

        if (!$this->validate($sections)) {
            throw new Exception('Invalid PHPT file');
        }

        foreach ($unsupportedSections as $section) {
            if (isset($sections[$section])) {
                throw new Exception(
                    "PHPUnit does not support PHPT $section sections"
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
        $testDirectory = \dirname($this->filename) . \DIRECTORY_SEPARATOR;

        foreach ($allowSections as $section) {
            if (isset($sections[$section . '_EXTERNAL'])) {
                $externalFilename = \trim($sections[$section . '_EXTERNAL']);

                if (!\is_file($testDirectory . $externalFilename) ||
                    !\is_readable($testDirectory . $externalFilename)) {
                    throw new Exception(
                        \sprintf(
                            'Could not load --%s-- %s for PHPT file',
                            $section . '_EXTERNAL',
                            $testDirectory . $externalFilename
                        )
                    );
                }

                $sections[$section] = \file_get_contents($testDirectory . $externalFilename);
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
            if (\is_array($section)) {
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
        return \str_replace(
            [
                '__DIR__',
                '__FILE__',
            ],
            [
                "'" . \dirname($this->filename) . "'",
                "'" . $this->filename . "'",
            ],
            $code
        );
    }

    private function getCoverageFiles(): array
    {
        $baseDir  = \dirname(\realpath($this->filename)) . \DIRECTORY_SEPARATOR;
        $basename = \basename($this->filename, 'phpt');

        return [
            'coverage' => $baseDir . $basename . 'coverage',
            'job'      => $baseDir . $basename . 'php',
        ];
    }

    private function renderForCoverage(string &$job): void
    {
        $files = $this->getCoverageFiles();

        $template = new Text_Template(
            __DIR__ . '/../Util/PHP/Template/PhptTestCase.tpl'
        );

        $composerAutoload = '\'\'';

        if (\defined('PHPUNIT_COMPOSER_INSTALL') && !\defined('PHPUNIT_TESTSUITE')) {
            $composerAutoload = \var_export(PHPUNIT_COMPOSER_INSTALL, true);
        }

        $phar = '\'\'';

        if (\defined('__PHPUNIT_PHAR__')) {
            $phar = \var_export(__PHPUNIT_PHAR__, true);
        }

        $globals = '';

        if (!empty($GLOBALS['__PHPUNIT_BOOTSTRAP'])) {
            $globals = '$GLOBALS[\'__PHPUNIT_BOOTSTRAP\'] = ' . \var_export(
                $GLOBALS['__PHPUNIT_BOOTSTRAP'],
                true
                ) . ";\n";
        }

        $template->setVar(
            [
                'composerAutoload' => $composerAutoload,
                'phar'             => $phar,
                'globals'          => $globals,
                'job'              => $files['job'],
                'coverageFile'     => $files['coverage'],
            ]
        );

        \file_put_contents($files['job'], $job);
        $job = $template->render();
    }

    private function cleanupForCoverage(): array
    {
        $files    = $this->getCoverageFiles();
        $coverage = @\unserialize(\file_get_contents($files['coverage']));

        if ($coverage === false) {
            $coverage = [];
        }

        foreach ($files as $file) {
            @\unlink($file);
        }

        return $coverage;
    }

    private function stringifyIni(array $ini): array
    {
        $settings = [];

        foreach ($ini as $key => $value) {
            if (\is_array($value)) {
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

        foreach (\preg_split('/\r\n|\r|\n/', $message) as $line) {
            $line = \trim($line);

            if ($block === 'message' && $line === '--- Expected') {
                $block = 'expected';
            }

            if ($block === 'expected' && $line === '@@ @@') {
                $block = 'diff';
            }

            if ($block === 'diff') {
                if (\strpos($line, '+') === 0) {
                    $needle = $this->getCleanDiffLine($previousLine);

                    break;
                }

                if (\strpos($line, '-') === 0) {
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
        if (\preg_match('/^[\-+]([\'\"]?)(.*)\1$/', $line, $matches)) {
            $line = $matches[2];
        }

        return $line;
    }

    private function getLocationHint(string $needle, array $sections, ?string $sectionName = null): array
    {
        $needle = \trim($needle);

        if (empty($needle)) {
            return [[
                'file' => \realpath($this->filename),
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

        foreach ($search as $section) {
            if (!isset($sections[$section])) {
                continue;
            }

            if (isset($sections[$section . '_EXTERNAL'])) {
                $externalFile = \trim($sections[$section . '_EXTERNAL']);

                return [
                    [
                        'file' => \realpath(\dirname($this->filename) . \DIRECTORY_SEPARATOR . $externalFile),
                        'line' => 1,
                    ],
                    [
                        'file' => \realpath($this->filename),
                        'line' => ($sections[$section . '_EXTERNAL_offset'] ?? 0) + 1,
                    ],
                ];
            }

            $sectionOffset = $sections[$section . '_offset'] ?? 0;
            $offset        = $sectionOffset + 1;

            foreach (\preg_split('/\r\n|\r|\n/', $sections[$section]) as $line) {
                if (\strpos($line, $needle) !== false) {
                    return [[
                        'file' => \realpath($this->filename),
                        'line' => $offset,
                    ]];
                }
                $offset++;
            }
        }

        if ($sectionName) {
            // String not found in specified section, show user the start of the named section
            return [[
                'file' => \realpath($this->filename),
                'line' => $sectionOffset,
            ]];
        }

        // No section specified, show user start of code
        return [[
            'file' => \realpath($this->filename),
            'line' => 1,
        ]];
    }
}
