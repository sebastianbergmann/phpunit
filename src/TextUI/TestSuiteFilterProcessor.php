<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI;

use const FILE_IGNORE_NEW_LINES;
use const FILE_SKIP_EMPTY_LINES;
use function array_map;
use function file;
use PHPUnit\Event;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\Filter\Factory;
use PHPUnit\TextUI\Configuration\Configuration;
use PHPUnit\TextUI\Configuration\FilterNotConfiguredException;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class TestSuiteFilterProcessor
{
    /**
     * @throws Event\RuntimeException
     * @throws FilterNotConfiguredException
     * @throws RuntimeException
     */
    public function process(Configuration $configuration, TestSuite $suite): void
    {
        $factory = new Factory;

        if (!$configuration->hasFilter() &&
            !$configuration->hasExcludeFilter() &&
            !$configuration->hasTestIdFilterFile() &&
            !$configuration->hasTestIdFilter() &&
            !$configuration->hasGroups() &&
            !$configuration->hasExcludeGroups() &&
            !$configuration->hasTestsCovering() &&
            !$configuration->hasTestsUsing() &&
            !$configuration->hasTestsRequiringPhpExtension()) {
            return;
        }

        if ($configuration->hasExcludeGroups()) {
            $factory->addExcludeGroupFilter(
                $configuration->excludeGroups(),
            );
        }

        if ($configuration->hasGroups()) {
            $factory->addIncludeGroupFilter(
                $configuration->groups(),
            );
        }

        if ($configuration->hasTestsCovering()) {
            $factory->addIncludeGroupFilter(
                array_map(
                    static fn (string $name): string => '__phpunit_covers_' . $name,
                    $configuration->testsCovering(),
                ),
            );
        }

        if ($configuration->hasTestsUsing()) {
            $factory->addIncludeGroupFilter(
                array_map(
                    static fn (string $name): string => '__phpunit_uses_' . $name,
                    $configuration->testsUsing(),
                ),
            );
        }

        if ($configuration->hasTestsRequiringPhpExtension()) {
            $factory->addIncludeGroupFilter(
                array_map(
                    static fn (string $name): string => '__phpunit_requires_php_extension' . $name,
                    $configuration->testsRequiringPhpExtension(),
                ),
            );
        }

        if ($configuration->hasTestIdFilterFile()) {
            $lines = @file($configuration->testIdFilterFile(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            if ($lines === false) {
                throw new RuntimeException('Cannot read from ' . $configuration->testIdFilterFile());
            }

            if ($lines !== []) {
                $factory->addTestIdFilter($lines);
            }
        }

        if ($configuration->hasTestIdFilter()) {
            $factory->addTestIdFilter([$configuration->testIdFilter()]);
        }

        if ($configuration->hasExcludeFilter()) {
            $factory->addExcludeNameFilter(
                $configuration->excludeFilter(),
            );
        }

        if ($configuration->hasFilter()) {
            $factory->addIncludeNameFilter(
                $configuration->filter(),
            );
        }

        $suite->injectFilter($factory);

        Event\Facade::emitter()->testSuiteFiltered(
            Event\TestSuite\TestSuiteBuilder::from($suite),
        );
    }
}
