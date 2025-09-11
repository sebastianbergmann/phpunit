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
use function get_include_path;
use function hrtime;
use function serialize;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;
use function unserialize;
use function var_export;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\TextUI\Configuration\Registry as ConfigurationRegistry;
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
final class SeparateProcessTestRunner implements IsolatedTestRunner
{
    /**
     * @throws \PHPUnit\Runner\Exception
     * @throws \PHPUnit\Util\Exception
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws ProcessIsolationException
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

        $coverage = CodeCoverage::instance()->isActive() ? 'true' : 'false';

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

        $file = $class->getFileName();

        assert($file !== false);

        $var = [
            'bootstrap'                      => $bootstrap,
            'composerAutoload'               => $composerAutoload,
            'phar'                           => $phar,
            'filename'                       => $file,
            'className'                      => $class->getName(),
            'collectCodeCoverageInformation' => $coverage,
            'data'                           => $data,
            'dataName'                       => $dataName,
            'dependencyInput'                => $dependencyInput,
            'constants'                      => $constants,
            'globals'                        => $globals,
            'include_path'                   => $includePath,
            'included_files'                 => $includedFiles,
            'iniSettings'                    => $iniSettings,
            'name'                           => $test->name(),
            'offsetSeconds'                  => (string) $offset[0],
            'offsetNanoseconds'              => (string) $offset[1],
            'serializedConfiguration'        => $serializedConfiguration,
            'processResultFile'              => $processResultFile,
        ];

        if (!$runEntireClass) {
            $var['methodName'] = $test->name();
        }

        $template->setVar($var);

        $code = $template->render();

        assert($code !== '');

        JobRunnerRegistry::runTestJob(new Job($code), $processResultFile, $test);

        @unlink($serializedConfiguration);
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
