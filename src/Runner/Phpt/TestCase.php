<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Phpt;

use const DEBUG_BACKTRACE_IGNORE_ARGS;
use const DIRECTORY_SEPARATOR;
use function array_merge;
use function basename;
use function debug_backtrace;
use function dirname;
use function explode;
use function extension_loaded;
use function file_exists;
use function file_get_contents;
use function is_array;
use function is_file;
use function ltrim;
use function ob_get_clean;
use function ob_start;
use function preg_match;
use function preg_replace;
use function preg_split;
use function realpath;
use function sprintf;
use function str_contains;
use function str_starts_with;
use function strncasecmp;
use function substr;
use function trim;
use function unlink;
use function unserialize;
use PHPUnit\Event\Code\Phpt;
use PHPUnit\Event\Code\ThrowableBuilder;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExecutionOrderDependency;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\IncompleteTestError;
use PHPUnit\Framework\PhptAssertionFailedError;
use PHPUnit\Framework\Reorderable;
use PHPUnit\Framework\SelfDescribing;
use PHPUnit\Framework\Test;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\Runner\CodeCoverageFileExistsException;
use PHPUnit\Runner\Exception;
use PHPUnit\Util\PHP\Job;
use PHPUnit\Util\PHP\JobRunnerRegistry;
use SebastianBergmann\CodeCoverage\Data\RawCodeCoverageData;
use SebastianBergmann\CodeCoverage\InvalidArgumentException;
use SebastianBergmann\CodeCoverage\ReflectionException;
use SebastianBergmann\CodeCoverage\Test\TestSize\TestSize;
use SebastianBergmann\CodeCoverage\Test\TestStatus\TestStatus;
use SebastianBergmann\CodeCoverage\TestIdMissingException;
use SebastianBergmann\CodeCoverage\UnintentionallyCoveredCodeException;
use staabm\SideEffectsDetector\SideEffect;
use staabm\SideEffectsDetector\SideEffectsDetector;
use Throwable;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @see https://qa.php.net/phpt_details.php
 */
final readonly class TestCase implements Reorderable, SelfDescribing, Test
{
    /**
     * @var non-empty-string
     */
    private string $filename;

    /**
     * @param non-empty-string $filename
     */
    public function __construct(string $filename)
    {
        $this->filename = $filename;

        $this->ensureCoverageFileDoesNotExist();
    }

    public function count(): int
    {
        return 1;
    }

    /**
     * @throws \PHPUnit\Framework\Exception
     * @throws \SebastianBergmann\Template\InvalidArgumentException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws ReflectionException
     * @throws TestIdMissingException
     * @throws UnintentionallyCoveredCodeException
     */
    public function run(): void
    {
        $emitter = EventFacade::emitter();
        $parser  = new Parser;

        $emitter->testPreparationStarted(
            $this->valueObjectForEvents(),
        );

        try {
            $sections = $parser->parse($this->filename);
        } catch (Exception $e) {
            $emitter->testPrepared($this->valueObjectForEvents());
            $emitter->testErrored($this->valueObjectForEvents(), ThrowableBuilder::from($e));
            $emitter->testFinished($this->valueObjectForEvents(), 0);

            return;
        }

        $code                 = (new Renderer)->render($this->filename, $sections['FILE']);
        $xfail                = false;
        $environmentVariables = [];
        $phpSettings          = $parser->parseIniSection($this->settings(CodeCoverage::instance()->isActive()));
        $input                = null;
        $arguments            = [];

        $emitter->testPrepared($this->valueObjectForEvents());

        if (isset($sections['INI'])) {
            $phpSettings = $parser->parseIniSection($sections['INI'], $phpSettings);
        }

        if (isset($sections['ENV'])) {
            $environmentVariables = $parser->parseEnvSection($sections['ENV']);
        }

        if ($this->shouldTestBeSkipped($sections, $phpSettings)) {
            return;
        }

        if (isset($sections['XFAIL'])) {
            $xfail = trim($sections['XFAIL']);
        }

        if (isset($sections['STDIN'])) {
            $input = $sections['STDIN'];
        }

        if (isset($sections['ARGS'])) {
            $arguments = explode(' ', $sections['ARGS']);
        }

        if (CodeCoverage::instance()->isActive()) {
            $codeCoverageCacheDirectory = null;

            if (CodeCoverage::instance()->codeCoverage()->cachesStaticAnalysis()) {
                $codeCoverageCacheDirectory = CodeCoverage::instance()->codeCoverage()->cacheDirectory();
            }

            (new Renderer)->renderForCoverage(
                $code,
                CodeCoverage::instance()->codeCoverage()->collectsBranchAndPathCoverage(),
                $codeCoverageCacheDirectory,
                $this->coverageFiles(),
            );
        }

        $jobResult = JobRunnerRegistry::run(
            new Job(
                $code,
                $this->stringifyIni($phpSettings),
                $environmentVariables,
                $arguments,
                $input,
                true,
            ),
        );

        EventFacade::emitter()->childProcessFinished($jobResult->stdout(), $jobResult->stderr());

        $output = $jobResult->stdout();

        if (CodeCoverage::instance()->isActive()) {
            $coverage = $this->cleanupForCoverage();

            CodeCoverage::instance()->codeCoverage()->start($this->filename, TestSize::large());

            CodeCoverage::instance()->codeCoverage()->append(
                $coverage,
                $this->filename,
                true,
                TestStatus::unknown(),
            );
        }

        $passed = true;

        try {
            $this->assertPhptExpectation($sections, $output);
        } catch (AssertionFailedError $e) {
            $failure = $e;

            if ($xfail !== false) {
                $failure = new IncompleteTestError($xfail, 0, $e);
            } elseif ($e instanceof ExpectationFailedException) {
                $comparisonFailure = $e->getComparisonFailure();

                if ($comparisonFailure !== null) {
                    $diff = $comparisonFailure->getDiff();
                } else {
                    $diff = $e->getMessage();
                }

                $hint    = $this->locationHintFromDiff($diff, $sections);
                $trace   = array_merge($hint, debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
                $failure = new PhptAssertionFailedError(
                    $e->getMessage(),
                    0,
                    (string) $trace[0]['file'],
                    (int) $trace[0]['line'],
                    $trace,
                    $comparisonFailure !== null ? $diff : '',
                );
            }

            if ($failure instanceof IncompleteTestError) {
                $emitter->testMarkedAsIncomplete($this->valueObjectForEvents(), ThrowableBuilder::from($failure));
            } else {
                $emitter->testFailed($this->valueObjectForEvents(), ThrowableBuilder::from($failure), null);
            }

            $passed = false;
        } catch (Throwable $t) {
            $emitter->testErrored($this->valueObjectForEvents(), ThrowableBuilder::from($t));

            $passed = false;
        }

        if ($passed) {
            $emitter->testPassed($this->valueObjectForEvents());
        }

        $this->runClean($sections, CodeCoverage::instance()->isActive());

        $emitter->testFinished($this->valueObjectForEvents(), 1);
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
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public function valueObjectForEvents(): Phpt
    {
        return new Phpt($this->filename);
    }

    /**
     * @param array<non-empty-string, non-empty-string> $sections
     *
     * @throws Exception
     * @throws ExpectationFailedException
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

                /** @phpstan-ignore staticMethod.dynamicName */
                Assert::$sectionAssertion($expected, $actual);

                return;
            }
        }

        throw new InvalidPhptFileException;
    }

    /**
     * @param array<non-empty-string, non-empty-string>                         $sections
     * @param array<non-empty-string, array<non-empty-string>|non-empty-string> $settings
     */
    private function shouldTestBeSkipped(array $sections, array $settings): bool
    {
        if (!isset($sections['SKIPIF'])) {
            return false;
        }

        $skipIfCode = (new Renderer)->render($this->filename, $sections['SKIPIF']);

        if ($this->shouldRunInSubprocess($sections, $skipIfCode)) {
            $jobResult = JobRunnerRegistry::run(
                new Job(
                    $skipIfCode,
                    $this->stringifyIni($settings),
                ),
            );

            $output = $jobResult->stdout();

            EventFacade::emitter()->childProcessFinished($output, $jobResult->stderr());
        } else {
            $output = $this->runCodeInLocalSandbox($skipIfCode);
        }

        $this->triggerRunnerWarningOnPhpErrors('SKIPIF', $output);

        if (strncasecmp('skip', ltrim($output), 4) === 0) {
            $message = '';

            if (preg_match('/^\s*skip\s*(.+)\s*/i', $output, $skipMatch)) {
                $message = substr($skipMatch[1], 2);
            }

            EventFacade::emitter()->testSkipped(
                $this->valueObjectForEvents(),
                $message,
            );

            EventFacade::emitter()->testFinished($this->valueObjectForEvents(), 0);

            return true;
        }

        return false;
    }

    /**
     * @param array<non-empty-string, non-empty-string> $sections
     */
    private function shouldRunInSubprocess(array $sections, string $cleanCode): bool
    {
        if (isset($sections['INI'])) {
            // to get per-test INI settings, we need a dedicated subprocess
            return true;
        }

        $detector    = new SideEffectsDetector;
        $sideEffects = $detector->getSideEffects($cleanCode);

        if ($sideEffects === []) {
            // no side-effects
            return false;
        }

        foreach ($sideEffects as $sideEffect) {
            if ($sideEffect === SideEffect::STANDARD_OUTPUT) {
                // stdout is fine, we will catch it using output-buffering
                continue;
            }

            if ($sideEffect === SideEffect::INPUT_OUTPUT) {
                // IO is fine, as it doesn't pollute the main process
                continue;
            }

            return true;
        }

        return false;
    }

    private function runCodeInLocalSandbox(string $code): string
    {
        $code = preg_replace('/^<\?(?:php)?|\?>\s*+$/', '', $code);
        $code = preg_replace('/declare\S?\([^)]+\)\S?;/', '', $code);

        // wrap in immediately invoked function to isolate local-side-effects of $code from our own process
        $code = '(function() {' . $code . '})();';
        ob_start();
        @eval($code);

        return ob_get_clean();
    }

    /**
     * @param array<non-empty-string, non-empty-string> $sections
     */
    private function runClean(array $sections, bool $collectCoverage): void
    {
        if (!isset($sections['CLEAN'])) {
            return;
        }

        $cleanCode = (new Renderer)->render($this->filename, $sections['CLEAN']);

        if ($this->shouldRunInSubprocess($sections, $cleanCode)) {
            $jobResult = JobRunnerRegistry::run(
                new Job(
                    $cleanCode,
                    $this->settings($collectCoverage),
                ),
            );

            $output = $jobResult->stdout();

            EventFacade::emitter()->childProcessFinished($jobResult->stdout(), $jobResult->stderr());
        } else {
            $output = $this->runCodeInLocalSandbox($cleanCode);
        }

        $this->triggerRunnerWarningOnPhpErrors('CLEAN', $output);
    }

    /**
     * @phpstan-ignore return.internalClass
     */
    private function cleanupForCoverage(): RawCodeCoverageData
    {
        /**
         * @phpstan-ignore staticMethod.internalClass
         */
        $coverage = RawCodeCoverageData::fromXdebugWithoutPathCoverage([]);
        $files    = $this->coverageFiles();

        $buffer = false;

        if (is_file($files['coverage'])) {
            $buffer = @file_get_contents($files['coverage']);
        }

        if ($buffer !== false) {
            $coverage = @unserialize(
                $buffer,
                [
                    'allowed_classes' => [
                        RawCodeCoverageData::class,
                    ],
                ],
            );

            if ($coverage === false) {
                /**
                 * @phpstan-ignore staticMethod.internalClass
                 */
                $coverage = RawCodeCoverageData::fromXdebugWithoutPathCoverage([]);
            }
        }

        foreach ($files as $file) {
            @unlink($file);
        }

        return $coverage;
    }

    /**
     * @return array{coverage: non-empty-string, job: non-empty-string}
     */
    private function coverageFiles(): array
    {
        $baseDir  = dirname(realpath($this->filename)) . DIRECTORY_SEPARATOR;
        $basename = basename($this->filename, 'phpt');

        return [
            'coverage' => $baseDir . $basename . 'coverage',
            'job'      => $baseDir . $basename . 'php',
        ];
    }

    /**
     * @param array<non-empty-string, array<non-empty-string>|non-empty-string> $ini
     *
     * @return list<non-empty-string>
     */
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

    /**
     * @param array<non-empty-string, non-empty-string> $sections
     *
     * @return non-empty-list<array{file: non-empty-string, line: int}>
     */
    private function locationHintFromDiff(string $message, array $sections): array
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
                if (str_starts_with($line, '+')) {
                    $needle = $this->cleanDiffLine($previousLine);

                    break;
                }

                if (str_starts_with($line, '-')) {
                    $needle = $this->cleanDiffLine($line);

                    break;
                }
            }

            if ($line !== '') {
                $previousLine = $line;
            }
        }

        return $this->locationHint($needle, $sections);
    }

    private function cleanDiffLine(string $line): string
    {
        if (preg_match('/^[\-+]([\'\"]?)(.*)\1$/', $line, $matches)) {
            $line = $matches[2];
        }

        return $line;
    }

    /**
     * @param array<non-empty-string, non-empty-string> $sections
     *
     * @return non-empty-list<array{file: non-empty-string, line: int}>
     */
    private function locationHint(string $needle, array $sections): array
    {
        $needle = trim($needle);

        if ($needle === '') {
            return [[
                'file' => realpath($this->filename),
                'line' => 1,
            ]];
        }

        $search = [
            // 'FILE',
            'EXPECT',
            'EXPECTF',
            'EXPECTREGEX',
        ];

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
                if (str_contains($line, $needle)) {
                    return [
                        [
                            'file' => realpath($this->filename),
                            'line' => $offset,
                        ],
                    ];
                }

                $offset++;
            }
        }

        return [
            [
                'file' => realpath($this->filename),
                'line' => 1,
            ],
        ];
    }

    /**
     * @return list<string>
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
            if ($collectCoverage) {
                $settings[] = 'xdebug.mode=coverage';
            }
        }

        return $settings;
    }

    private function triggerRunnerWarningOnPhpErrors(string $section, string $output): void
    {
        if (str_contains($output, 'Parse error:')) {
            EventFacade::emitter()->testRunnerTriggeredPhpunitWarning(
                sprintf(
                    '%s section triggered a parse error: %s',
                    $section,
                    $output,
                ),
            );
        }

        if (str_contains($output, 'Fatal error:')) {
            EventFacade::emitter()->testRunnerTriggeredPhpunitWarning(
                sprintf(
                    '%s section triggered a fatal error: %s',
                    $section,
                    $output,
                ),
            );
        }
    }

    /**
     * @throws CodeCoverageFileExistsException
     */
    private function ensureCoverageFileDoesNotExist(): void
    {
        $files = $this->coverageFiles();

        if (file_exists($files['coverage'])) {
            throw new CodeCoverageFileExistsException(
                sprintf(
                    'File %s exists, PHPT test %s will not be executed',
                    $files['coverage'],
                    $this->filename,
                ),
            );
        }
    }
}
