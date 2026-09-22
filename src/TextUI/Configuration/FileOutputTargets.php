<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Configuration;

use PHPUnit\TextUI\CliArguments\Configuration as CliConfiguration;

/**
 * Enumerates the paths that a test run would write to.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class FileOutputTargets
{
    /**
     * @return list<FileOutputTarget>
     */
    public static function fromConfiguration(Configuration $configuration, CliConfiguration $cliConfiguration): array
    {
        $targets = [];

        if ($configuration->hasCacheDirectory()) {
            $targets[] = new FileOutputTarget('cache directory', $configuration->cacheDirectory());
        }

        if ($configuration->recordTestRunHistory()) {
            $targets[] = new FileOutputTarget('test run history', $configuration->testRunHistoryFile());
        }

        if ($configuration->hasLogfileJunit() && $configuration->logfileJunit() !== '') {
            $targets[] = new FileOutputTarget('JUnit XML log', $configuration->logfileJunit());
        }

        if ($configuration->hasLogfileOtr()) {
            $targets[] = new FileOutputTarget('Open Test Reporting XML log', $configuration->logfileOtr());
        }

        if ($configuration->hasLogfileTeamcity() && $configuration->logfileTeamcity() !== '') {
            $targets[] = new FileOutputTarget('TeamCity log', $configuration->logfileTeamcity());
        }

        if ($configuration->hasLogfileTestdoxHtml() && $configuration->logfileTestdoxHtml() !== '') {
            $targets[] = new FileOutputTarget('TestDox HTML log', $configuration->logfileTestdoxHtml());
        }

        if ($configuration->hasLogfileTestdoxText() && $configuration->logfileTestdoxText() !== '') {
            $targets[] = new FileOutputTarget('TestDox text log', $configuration->logfileTestdoxText());
        }

        if ($configuration->hasLogEventsText()) {
            $targets[] = new FileOutputTarget('event log', $configuration->logEventsText());
        }

        if ($configuration->hasLogEventsVerboseText()) {
            $targets[] = new FileOutputTarget('verbose event log', $configuration->logEventsVerboseText());
        }

        if ($configuration->hasCoverageClover()) {
            $targets[] = new FileOutputTarget('Clover XML code coverage report', $configuration->coverageClover());
        }

        if ($configuration->hasCoverageCobertura()) {
            $targets[] = new FileOutputTarget('Cobertura XML code coverage report', $configuration->coverageCobertura());
        }

        if ($configuration->hasCoverageCrap4j()) {
            $targets[] = new FileOutputTarget('Crap4J XML code coverage report', $configuration->coverageCrap4j());
        }

        if ($configuration->hasCoverageHtml()) {
            $targets[] = new FileOutputTarget('HTML code coverage report', $configuration->coverageHtml());
        }

        if ($configuration->hasCoverageJsonl()) {
            $targets[] = new FileOutputTarget('JSONL code coverage report', $configuration->coverageJsonl());
        }

        if ($configuration->hasCoverageOpenClover()) {
            $targets[] = new FileOutputTarget('OpenClover XML code coverage report', $configuration->coverageOpenClover());
        }

        if ($configuration->hasCoveragePhp()) {
            $targets[] = new FileOutputTarget('PHP code coverage report', $configuration->coveragePhp());
        }

        if ($configuration->hasCoverageText()) {
            $targets[] = new FileOutputTarget('text code coverage report', $configuration->coverageText());
        }

        if ($configuration->hasCoverageXml()) {
            $targets[] = new FileOutputTarget('XML code coverage report', $configuration->coverageXml());
        }

        if ($configuration->hasGenerateBaseline()) {
            $targets[] = new FileOutputTarget('baseline', $configuration->generateBaseline());
        }

        if ($cliConfiguration->hasListTestsXml() && $cliConfiguration->listTestsXml() !== '') {
            $targets[] = new FileOutputTarget('list of tests in XML format', $cliConfiguration->listTestsXml());
        }

        return $targets;
    }
}
