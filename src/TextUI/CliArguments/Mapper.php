<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\CliArguments;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Mapper
{
    /**
     * @throws Exception
     */
    public function mapToLegacyArray(Configuration $arguments): array
    {
        $result = [
            'extensions'            => [],
            'listGroups'            => false,
            'listSuites'            => false,
            'listTests'             => false,
            'listTestsXml'          => false,
            'unavailableExtensions' => [],
        ];

        if ($arguments->hasFilter()) {
            $result['filter'] = $arguments->filter();
        }

        if ($arguments->hasGroups()) {
            $result['groups'] = $arguments->groups();
        }

        if ($arguments->hasExcludeGroups()) {
            $result['excludeGroups'] = $arguments->excludeGroups();
        }

        if ($arguments->hasTestsCovering()) {
            $result['testsCovering'] = $arguments->testsCovering();
        }

        if ($arguments->hasTestsUsing()) {
            $result['testsUsing'] = $arguments->testsUsing();
        }

        if ($arguments->hasIncludePath()) {
            $result['includePath'] = $arguments->includePath();
        }

        if ($arguments->hasExtensions()) {
            $result['extensions'] = $arguments->extensions();
        }

        if ($arguments->hasUnavailableExtensions()) {
            $result['unavailableExtensions'] = $arguments->unavailableExtensions();
        }

        if ($arguments->hasTestdoxGroups()) {
            $result['testdoxGroups'] = $arguments->testdoxGroups();
        }

        if ($arguments->hasTestdoxExcludeGroups()) {
            $result['testdoxExcludeGroups'] = $arguments->testdoxExcludeGroups();
        }

        if ($arguments->hasNoOutput()) {
            $result['noOutput'] = $arguments->noOutput();
        }

        if ($arguments->hasCoverageFilter()) {
            $result['coverageFilter'] = $arguments->coverageFilter();
        }

        if ($arguments->hasRandomOrderSeed()) {
            $result['randomOrderSeed'] = $arguments->randomOrderSeed();
        }

        return $result;
    }
}
