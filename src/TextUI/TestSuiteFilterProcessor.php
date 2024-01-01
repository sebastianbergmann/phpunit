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

use function array_map;
use PHPUnit\Event;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\Filter\Factory;
use PHPUnit\TextUI\Configuration\Configuration;
use PHPUnit\TextUI\Configuration\FilterNotConfiguredException;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class TestSuiteFilterProcessor
{
    private Factory $filterFactory;

    public function __construct(Factory $factory = new Factory)
    {
        $this->filterFactory = $factory;
    }

    /**
     * @throws Event\RuntimeException
     * @throws FilterNotConfiguredException
     */
    public function process(Configuration $configuration, TestSuite $suite): void
    {
        if (!$configuration->hasFilter() &&
            !$configuration->hasGroups() &&
            !$configuration->hasExcludeGroups() &&
            !$configuration->hasExcludeFilter() &&
            !$configuration->hasTestsCovering() &&
            !$configuration->hasTestsUsing()) {
            return;
        }

        if ($configuration->hasExcludeGroups()) {
            $this->filterFactory->addExcludeGroupFilter(
                $configuration->excludeGroups(),
            );
        }

        if ($configuration->hasGroups()) {
            $this->filterFactory->addIncludeGroupFilter(
                $configuration->groups(),
            );
        }

        if ($configuration->hasTestsCovering()) {
            $this->filterFactory->addIncludeGroupFilter(
                array_map(
                    static fn (string $name): string => '__phpunit_covers_' . $name,
                    $configuration->testsCovering(),
                ),
            );
        }

        if ($configuration->hasTestsUsing()) {
            $this->filterFactory->addIncludeGroupFilter(
                array_map(
                    static fn (string $name): string => '__phpunit_uses_' . $name,
                    $configuration->testsUsing(),
                ),
            );
        }

        if ($configuration->hasExcludeFilter()) {
            $this->filterFactory->addExcludeNameFilter(
                $configuration->excludeFilter(),
            );
        }

        if ($configuration->hasFilter()) {
            $this->filterFactory->addIncludeNameFilter(
                $configuration->filter(),
            );
        }

        $suite->injectFilter($this->filterFactory);

        Event\Facade::emitter()->testSuiteFiltered(
            Event\TestSuite\TestSuiteBuilder::from($suite),
        );
    }
}
