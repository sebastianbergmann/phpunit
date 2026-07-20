<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\TestRunner;

use function assert;
use function bin2hex;
use function hrtime;
use function random_bytes;
use function serialize;
use function sprintf;
use function sys_get_temp_dir;
use function tempnam;
use function var_export;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Event\TestRunner\ChildProcessReason;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ProcessIsolationException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\Util\GlobalState;
use PHPUnit\Util\PHP\Job;
use PHPUnit\Util\PHP\JobRunnerRegistry;
use ReflectionClass;
use SebastianBergmann\Template\InvalidArgumentException;
use SebastianBergmann\Template\Template;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class SeparateProcessTestRunner
{
    /**
     * @throws \PHPUnit\Runner\Exception
     * @throws \PHPUnit\Util\Exception
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws ProcessIsolationException
     */
    public function run(TestCase $test, bool $preserveGlobalState, bool $requiresXdebug): void
    {
        $class = new ReflectionClass($test);

        $constants     = '';
        $globals       = '';
        $includedFiles = '';
        $iniSettings   = '';

        if ($preserveGlobalState) {
            $constants         = GlobalState::getConstantsAsString();
            $globalStateResult = GlobalState::exportGlobals();
            $globals           = $globalStateResult->globalsString();
            $includedFiles     = GlobalState::getIncludedFilesAsString();
            $iniSettings       = GlobalState::getIniSettingsAsString();

            foreach ($globalStateResult->skippedGlobals() as $skipped) {
                EventFacade::emitter()->testTriggeredPhpunitWarning(
                    $test->valueObjectForEvents(),
                    sprintf(
                        'Global variable %s was not preserved because it %s',
                        $skipped['name'],
                        $skipped['reason'],
                    ),
                );
            }
        }

        $coverage = CodeCoverage::instance()->isActive() ? 'true' : 'false';

        $data            = var_export(serialize($test->providedData()), true);
        $dataName        = var_export($test->dataName(), true);
        $dependencyInput = var_export(serialize($test->dependencyInput()), true);
        // must do these fixes because TestCaseMethod.tpl has unserialize('{data}') in it, and we can't break BC
        // the lines above used to use addcslashes() rather than var_export(), which breaks null byte escape sequences
        $data              = "'." . $data . ".'";
        $dataName          = "'.(" . $dataName . ").'";
        $dependencyInput   = "'." . $dependencyInput . ".'";
        $offset            = hrtime();
        $processResultFile = tempnam(sys_get_temp_dir(), 'phpunit_');

        if ($processResultFile === false) {
            // @codeCoverageIgnoreStart
            throw new ProcessIsolationException;
            // @codeCoverageIgnoreEnd
        }

        $processResultNonce = bin2hex(random_bytes(16));

        $file = $class->getFileName();

        assert($file !== false);

        $var = [
            'childProcessHead'               => ChildProcessBootstrap::headFragment($iniSettings),
            'childProcessConfiguration'      => ChildProcessBootstrap::configurationFragment(),
            'filename'                       => $file,
            'className'                      => $class->getName(),
            'methodName'                     => $test->name(),
            'collectCodeCoverageInformation' => $coverage,
            'data'                           => $data,
            'dataName'                       => $dataName,
            'dependencyInput'                => $dependencyInput,
            'repetition'                     => (string) $test->repetition(),
            'totalRepetitions'               => (string) $test->totalRepetitions(),
            'attempt'                        => (string) $test->attempt(),
            'maxAttempts'                    => (string) $test->maxAttempts(),
            'constants'                      => $constants,
            'globals'                        => $globals,
            'included_files'                 => $includedFiles,
            'name'                           => $test->name(),
            'offsetSeconds'                  => (string) $offset[0],
            'offsetNanoseconds'              => (string) $offset[1],
            'processResultFile'              => $processResultFile,
            'processResultNonce'             => $processResultNonce,
        ];

        $template = new Template(__DIR__ . '/templates/method.tpl');

        $template->setVar($var);

        $code = $template->render();

        assert($code !== '');

        JobRunnerRegistry::runTestJob(new Job($code, ChildProcessReason::TestRequiringProcessIsolation, requiresXdebug: $requiresXdebug), $processResultFile, $test, $processResultNonce);
    }
}
