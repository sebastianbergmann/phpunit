<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use function assert;
use function defined;
use function file_exists;
use function file_get_contents;
use function get_include_path;
use function hrtime;
use function restore_error_handler;
use function serialize;
use function set_error_handler;
use function sys_get_temp_dir;
use function tempnam;
use function trim;
use function unlink;
use function unserialize;
use function var_export;
use ErrorException;
use PHPUnit\Event\Code\TestMethodBuilder;
use PHPUnit\Event\Code\ThrowableBuilder;
use PHPUnit\Event\Facade;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\TestRunner\TestResult\PassedTests;
use PHPUnit\TextUI\Configuration\Registry as ConfigurationRegistry;
use PHPUnit\Util\GlobalState;
use PHPUnit\Util\PHP\Job;
use PHPUnit\Util\PHP\JobRunnerRegistry;
use PHPUnit\Util\PHP\PhpProcessException;
use ReflectionClass;
use SebastianBergmann\CodeCoverage\StaticAnalysisCacheNotConfiguredException;
use SebastianBergmann\Template\InvalidArgumentException;
use SebastianBergmann\Template\Template;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class SeparateProcessTestRunner implements IsolatedTestRunner
{
    /**
     * @throws \PHPUnit\Runner\Exception
     * @throws \PHPUnit\Util\Exception
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws ProcessIsolationException
     * @throws StaticAnalysisCacheNotConfiguredException
     */
    public function run(TestCase $test, bool $runEntireClass, bool $preserveGlobalState): void
    {
        $class = new ReflectionClass($test);

        if ($runEntireClass) {
            $template = new Template(
                __DIR__ . '/templates/class.tpl',
            );
        } else {
            $template = new Template(
                __DIR__ . '/templates/method.tpl',
            );
        }

        $bootstrap     = '';
        $constants     = '';
        $globals       = '';
        $includedFiles = '';
        $iniSettings   = '';

        if (ConfigurationRegistry::get()->hasBootstrap()) {
            $bootstrap = ConfigurationRegistry::get()->bootstrap();
        }

        if ($preserveGlobalState) {
            $constants     = GlobalState::getConstantsAsString();
            $globals       = GlobalState::getGlobalsAsString();
            $includedFiles = GlobalState::getIncludedFilesAsString();
            $iniSettings   = GlobalState::getIniSettingsAsString();
        }

        $coverage         = CodeCoverage::instance()->isActive() ? 'true' : 'false';
        $linesToBeIgnored = var_export(CodeCoverage::instance()->linesToBeIgnored(), true);

        if (defined('PHPUNIT_COMPOSER_INSTALL')) {
            $composerAutoload = var_export(PHPUNIT_COMPOSER_INSTALL, true);
        } else {
            $composerAutoload = '\'\'';
        }

        if (defined('__PHPUNIT_PHAR__')) {
            $phar = var_export(__PHPUNIT_PHAR__, true);
        } else {
            $phar = '\'\'';
        }

        $data            = var_export(serialize($test->providedData()), true);
        $dataName        = var_export($test->dataName(), true);
        $dependencyInput = var_export(serialize($test->dependencyInput()), true);
        $includePath     = var_export(get_include_path(), true);
        // must do these fixes because TestCaseMethod.tpl has unserialize('{data}') in it, and we can't break BC
        // the lines above used to use addcslashes() rather than var_export(), which breaks null byte escape sequences
        $data                    = "'." . $data . ".'";
        $dataName                = "'.(" . $dataName . ").'";
        $dependencyInput         = "'." . $dependencyInput . ".'";
        $includePath             = "'." . $includePath . ".'";
        $offset                  = hrtime();
        $serializedConfiguration = $this->saveConfigurationForChildProcess();
        $processResultFile       = tempnam(sys_get_temp_dir(), 'phpunit_');

        $var = [
            'bootstrap'                      => $bootstrap,
            'composerAutoload'               => $composerAutoload,
            'phar'                           => $phar,
            'filename'                       => $class->getFileName(),
            'className'                      => $class->getName(),
            'collectCodeCoverageInformation' => $coverage,
            'linesToBeIgnored'               => $linesToBeIgnored,
            'data'                           => $data,
            'dataName'                       => $dataName,
            'dependencyInput'                => $dependencyInput,
            'constants'                      => $constants,
            'globals'                        => $globals,
            'include_path'                   => $includePath,
            'included_files'                 => $includedFiles,
            'iniSettings'                    => $iniSettings,
            'name'                           => $test->name(),
            'offsetSeconds'                  => $offset[0],
            'offsetNanoseconds'              => $offset[1],
            'serializedConfiguration'        => $serializedConfiguration,
            'processResultFile'              => $processResultFile,
        ];

        if (!$runEntireClass) {
            $var['methodName'] = $test->name();
        }

        $template->setVar($var);

        $code = $template->render();

        assert($code !== '');

        $this->runTestJob($code, $test, $processResultFile);

        @unlink($serializedConfiguration);
    }

    /**
     * @psalm-param non-empty-string $code
     *
     * @throws Exception
     * @throws NoPreviousThrowableException
     * @throws PhpProcessException
     */
    private function runTestJob(string $code, Test $test, string $processResultFile): void
    {
        $result = JobRunnerRegistry::run(new Job($code));

        $processResult = '';

        if (file_exists($processResultFile)) {
            $processResult = file_get_contents($processResultFile);

            @unlink($processResultFile);
        }

        $this->processChildResult(
            $test,
            $processResult,
            $result->stderr(),
        );
    }

    /**
     * @throws Exception
     * @throws NoPreviousThrowableException
     */
    private function processChildResult(Test $test, string $stdout, string $stderr): void
    {
        if (!empty($stderr)) {
            $exception = new Exception(trim($stderr));

            assert($test instanceof TestCase);

            Facade::emitter()->testErrored(
                TestMethodBuilder::fromTestCase($test),
                ThrowableBuilder::from($exception),
            );

            return;
        }

        set_error_handler(
            /**
             * @throws ErrorException
             */
            static function (int $errno, string $errstr, string $errfile, int $errline): never
            {
                throw new ErrorException($errstr, $errno, $errno, $errfile, $errline);
            },
        );

        try {
            $childResult = unserialize($stdout);

            restore_error_handler();

            if ($childResult === false) {
                $exception = new AssertionFailedError('Test was run in child process and ended unexpectedly');

                assert($test instanceof TestCase);

                Facade::emitter()->testErrored(
                    TestMethodBuilder::fromTestCase($test),
                    ThrowableBuilder::from($exception),
                );

                Facade::emitter()->testFinished(
                    TestMethodBuilder::fromTestCase($test),
                    0,
                );
            }
        } catch (ErrorException $e) {
            restore_error_handler();

            $childResult = false;

            $exception = new Exception(trim($stdout), 0, $e);

            assert($test instanceof TestCase);

            Facade::emitter()->testErrored(
                TestMethodBuilder::fromTestCase($test),
                ThrowableBuilder::from($exception),
            );
        }

        if ($childResult !== false) {
            if (!empty($childResult['output'])) {
                $output = $childResult['output'];
            }

            Facade::instance()->forward($childResult['events']);
            PassedTests::instance()->import($childResult['passedTests']);

            assert($test instanceof TestCase);

            $test->setResult($childResult['testResult']);
            $test->addToAssertionCount($childResult['numAssertions']);

            if (CodeCoverage::instance()->isActive() && $childResult['codeCoverage'] instanceof \SebastianBergmann\CodeCoverage\CodeCoverage) {
                CodeCoverage::instance()->codeCoverage()->merge(
                    $childResult['codeCoverage'],
                );
            }
        }

        if (!empty($output)) {
            print $output;
        }
    }

    /**
     * @throws ProcessIsolationException
     */
    private function saveConfigurationForChildProcess(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'phpunit_');

        if ($path === false) {
            throw new ProcessIsolationException;
        }

        if (!ConfigurationRegistry::saveTo($path)) {
            throw new ProcessIsolationException;
        }

        return $path;
    }
}
