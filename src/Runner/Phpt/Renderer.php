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

use function assert;
use function defined;
use function dirname;
use function file_put_contents;
use function str_replace;
use function var_export;
use PHPUnit\TextUI\Configuration\Registry as ConfigurationRegistry;
use SebastianBergmann\Template\InvalidArgumentException;
use SebastianBergmann\Template\Template;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @see https://qa.php.net/phpt_details.php
 */
final readonly class Renderer
{
    /**
     * @param non-empty-string $phptFile
     * @param non-empty-string $code
     *
     * @return non-empty-string
     */
    public function render(string $phptFile, string $code): string
    {
        return str_replace(
            [
                '__DIR__',
                '__FILE__',
            ],
            [
                "'" . dirname($phptFile) . "'",
                "'" . $phptFile . "'",
            ],
            $code,
        );
    }

    /**
     * @param non-empty-string                                         $job
     * @param array{coverage: non-empty-string, job: non-empty-string} $files
     *
     * @param-out non-empty-string $job
     *
     * @throws InvalidArgumentException
     */
    public function renderForCoverage(string &$job, bool $pathCoverage, ?string $codeCoverageCacheDirectory, array $files): void
    {
        $template = new Template(
            __DIR__ . '/templates/phpt.tpl',
        );

        $composerAutoload = '\'\'';

        if (defined('PHPUNIT_COMPOSER_INSTALL')) {
            $composerAutoload = var_export(PHPUNIT_COMPOSER_INSTALL, true);
        }

        $phar = '\'\'';

        if (defined('__PHPUNIT_PHAR__')) {
            $phar = var_export(__PHPUNIT_PHAR__, true);
        }

        if ($codeCoverageCacheDirectory === null) {
            $codeCoverageCacheDirectory = 'null';
        } else {
            $codeCoverageCacheDirectory = "'" . $codeCoverageCacheDirectory . "'";
        }

        $bootstrap = '';

        if (ConfigurationRegistry::get()->hasBootstrap()) {
            $bootstrap = ConfigurationRegistry::get()->bootstrap();
        }

        $template->setVar(
            [
                'bootstrap'                  => $bootstrap,
                'composerAutoload'           => $composerAutoload,
                'phar'                       => $phar,
                'job'                        => $files['job'],
                'coverageFile'               => $files['coverage'],
                'driverMethod'               => $pathCoverage ? 'forLineAndPathCoverage' : 'forLineCoverage',
                'codeCoverageCacheDirectory' => $codeCoverageCacheDirectory,
            ],
        );

        file_put_contents($files['job'], $job);

        $rendered = $template->render();

        assert($rendered !== '');

        $job = $rendered;
    }
}
