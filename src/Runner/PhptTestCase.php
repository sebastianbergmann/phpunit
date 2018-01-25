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

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\IncompleteTestError;
use PHPUnit\Framework\SelfDescribing;
use PHPUnit\Framework\SkippedTestError;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestResult;
use PHPUnit\Util\PHP\AbstractPhpProcess;
use SebastianBergmann\Timer\Timer;
use Text_Template;
use Throwable;

/**
 * Runner for PHPT test cases.
 */
class PhptTestCase implements Test, SelfDescribing
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
     * @var array
     */
    private $settings = [
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
        'xdebug.default_enable=0'
    ];

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
     *
     * @return int
     */
    public function count(): int
    {
        return 1;
    }

    /**
     * Runs a test and collects its result in a TestResult instance.
     *
     * @param TestResult $result
     *
     * @throws Exception
     * @throws \ReflectionException
     * @throws \SebastianBergmann\CodeCoverage\CoveredCodeNotExecutedException
     * @throws \SebastianBergmann\CodeCoverage\InvalidArgumentException
     * @throws \SebastianBergmann\CodeCoverage\MissingCoversAnnotationException
     * @throws \SebastianBergmann\CodeCoverage\RuntimeException
     * @throws \SebastianBergmann\CodeCoverage\UnintentionallyCoveredCodeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Exception
     *
     * @return TestResult
     */
    public function run(TestResult $result = null): TestResult
    {
        $sections = $this->parse();
        $code     = $this->render($sections['FILE']);

        if ($result === null) {
            $result = new TestResult;
        }

        $xfail    = false;
        $settings = $this->parseIniSection($this->settings);

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
            $this->renderForCoverage($settings);
        }

        Timer::start();

        $jobResult = $this->phpUtil->runJob($code, $this->stringifyIni($settings));
        $time      = Timer::stop();

        if ($result->getCollectCodeCoverageInformation() && ($coverage = $this->cleanupForCoverage())) {
            $result->getCodeCoverage()->append($coverage, $this, true, [], [], true);
        }

        try {
            $this->assertPhptExpectation($sections, $jobResult['stdout']);
        } catch (AssertionFailedError $e) {
            $failure = $e;
            if ($xfail !== false) {
                $failure = new IncompleteTestError($xfail, 0, $e);
            }
            $result->addFailure($this, $failure, $time);
        } catch (Throwable $t) {
            $result->addError($this, $t, $time);
        }

        if ($result->allCompletelyImplemented() && $xfail !== false) {
            $result->addFailure($this, new IncompleteTestError('XFAIL section but test passes'), $time);
        }

        $this->runClean($sections);

        $result->endTest($this, $time);

        return $result;
    }

    /**
     * Returns the name of the test case.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->toString();
    }

    /**
     * Returns a string representation of the test case.
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->filename;
    }

    /**
     * Parse --INI-- section key value pairs and return as array.
     *
     * @param array|string
     * @param mixed $content
     * @param mixed $ini
     *
     * @return array
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

    /**
     * @param string $content
     *
     * @return array<string, string>
     */
    private function parseEnvSection($content): array
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
     * @param array  $sections
     * @param string $output
     *
     * @throws Exception
     */
    private function assertPhptExpectation(array $sections, $output): void
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
                $assertion      = $sectionAssertion;
                $expected       = $sectionName === 'EXPECTREGEX' ? "/{$sectionContent}/" : $sectionContent;

                break;
            }
        }

        if (!isset($assertion)) {
            throw new Exception('No PHPT assertion found');
        }

        if (!isset($expected)) {
            throw new Exception('No PHPT expectation found');
        }

        Assert::$assertion($expected, $actual);
    }

    /**
     * @param            $sections
     * @param TestResult $result
     * @param array      $settings
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Exception
     *
     * @return bool
     */
    private function runSkip(&$sections, TestResult $result, $settings): bool
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

            $result->addFailure($this, new SkippedTestError($message), 0);
            $result->endTest($this, 0);

            return true;
        }

        return false;
    }

    /**
     * @param array<string, string> $sections
     */
    private function runClean(&$sections): void
    {
        $this->phpUtil->setStdin('');
        $this->phpUtil->setArgs('');

        if (isset($sections['CLEAN'])) {
            $cleanCode = $this->render($sections['CLEAN']);

            $this->phpUtil->runJob($cleanCode, $this->settings);
        }
    }

    /**
     * @throws Exception
     *
     * @return array
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
            'PHPDBG'
        ];

        foreach (\file($this->filename) as $line) {
            if (\preg_match('/^--([_A-Z]+)--/', $line, $result)) {
                $section            = $result[1];
                $sections[$section] = '';

                continue;
            }
            if (empty($section)) {
                throw new Exception('Invalid PHPT file');
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
                    'PHPUnit does not support this PHPT file'
                );
            }
        }

        return $sections;
    }

    /**
     * @param array<string, string> $sections
     *
     * @throws Exception
     */
    private function parseExternal(&$sections): void
    {
        $allowSections = [
            'FILE',
            'EXPECT',
            'EXPECTF',
            'EXPECTREGEX'
        ];
        $testDirectory = \dirname($this->filename) . DIRECTORY_SEPARATOR;

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

                unset($sections[$section . '_EXTERNAL']);
            }
        }
    }

    /**
     * @param array<string, string> $sections
     *
     * @return bool
     */
    private function validate(&$sections): bool
    {
        $requiredSections = [
            'FILE',
            [
                'EXPECT',
                'EXPECTF',
                'EXPECTREGEX'
            ]
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

    /**
     * @param string $code
     *
     * @return string
     */
    private function render($code): string
    {
        return \str_replace(
            [
                '__DIR__',
                '__FILE__'
            ],
            [
                "'" . \dirname($this->filename) . "'",
                "'" . $this->filename . "'"
            ],
            $code
        );
    }

    /**
     * @return array<string, string>
     */
    private function getCoverageFiles(): array
    {
        $baseDir          = \dirname($this->filename) . DIRECTORY_SEPARATOR;
        $basename         = \basename($this->filename, 'phpt');

        return [
            'coverage' => $baseDir . $basename . 'coverage',
            'job'      => $baseDir . $basename . 'php'
        ];
    }

    /**
     * @param array $settings
     */
    private function renderForCoverage(&$settings): void
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
            $globals = '$GLOBALS[\'__PHPUNIT_BOOTSTRAP\'] = ' . \var_export($GLOBALS['__PHPUNIT_BOOTSTRAP'], true) . ";\n";
        }

        $template->setVar(
            [
                'composerAutoload' => $composerAutoload,
                'phar'             => $phar,
                'globals'          => $globals,
                'job'              => $files['job'],
                'coverageFile'     => $files['coverage'],
                'autoPrependFile'  => \var_export(
                    !empty($settings['auto_prepend_file']) ? $settings['auto_prepend_file'] : false,
                    true
                )
            ]
        );

        \file_put_contents($files['job'], $template->render());

        $settings['auto_prepend_file'] = $files['job'];
    }

    /**
     * @return array
     */
    private function cleanupForCoverage(): array
    {
        $files    = $this->getCoverageFiles();
        $coverage = @\unserialize(\file_get_contents($files['coverage']));

        foreach ($files as $file) {
            @\unlink($file);
        }

        return $coverage;
    }

    /**
     * @param array $ini
     *
     * @return array
     */
    private function stringifyIni($ini): array
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
}
