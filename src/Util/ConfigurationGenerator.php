<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use function str_replace;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ConfigurationGenerator
{
    /**
     * @var string
     */
    private const TEMPLATE = <<<'EOT'
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/{phpunit_version}/phpunit.xsd"
         bootstrap="{bootstrap_script}"
         cacheResultFile="{cache_directory}/test-results"
         executionOrder="depends,defects"
         forceCoversAnnotation="true"
         beStrictAboutCoversAnnotation="true"
         beStrictAboutOutputDuringTests="true"
         beStrictAboutTodoAnnotatedTests="true"
         convertDeprecationsToExceptions="true"
         verbose="true">
    <testsuites>
        <testsuite name="default">
            <directory>{tests_directory}</directory>
        </testsuite>
    </testsuites>

    <filter>
        <whitelist processUncoveredFilesFromWhitelist="true">
            <directory suffix=".php">{src_directory}</directory>
        </whitelist>
    </filter>
</phpunit>

EOT;

    public function generateDefaultConfiguration(string $phpunitVersion, string $bootstrapScript, string $testsDirectory, string $srcDirectory, string $cacheDirectory): string
    {
        return str_replace(
            [
                '{phpunit_version}',
                '{bootstrap_script}',
                '{tests_directory}',
                '{src_directory}',
                '{cache_directory}',
            ],
            [
                $phpunitVersion,
                $bootstrapScript,
                $testsDirectory,
                $srcDirectory,
                $cacheDirectory,
            ],
            self::TEMPLATE
        );
    }
}
