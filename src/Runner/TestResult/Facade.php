<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestRunner\TestResult;

use function array_filter;
use function count;
use function str_contains;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Runner\DeprecationCollector\Facade as DeprecationCollectorFacade;
use PHPUnit\TestRunner\IssueFilter;
use PHPUnit\TextUI\Configuration\Configuration;
use PHPUnit\TextUI\Configuration\Registry as ConfigurationRegistry;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Facade
{
    private static ?Collector $collector = null;

    public static function init(): void
    {
        self::collector();
    }

    public static function result(): TestResult
    {
        return self::collector()->result();
    }

    public static function shouldStop(): bool
    {
        $configuration = ConfigurationRegistry::get();
        $collector     = self::collector();

        $numberOfErrors   = $collector->numberOfErroredTests();
        $numberOfFailures = $collector->numberOfFailedTests();
        $numberOfWarnings = $collector->numberOfWarnings();
        $numberOfRisky    = $collector->numberOfRiskyTests();

        $stopOnDefect = $configuration->stopOnDefectThreshold();

        if ($stopOnDefect > 0 && ($numberOfErrors + $numberOfFailures + $numberOfWarnings + $numberOfRisky) >= $stopOnDefect) {
            return true;
        }

        $stopOnError = $configuration->stopOnErrorThreshold();

        if ($stopOnError > 0 && $numberOfErrors >= $stopOnError) {
            return true;
        }

        $stopOnFailure = $configuration->stopOnFailureThreshold();

        if ($stopOnFailure > 0 && $numberOfFailures >= $stopOnFailure) {
            return true;
        }

        $stopOnWarning = $configuration->stopOnWarningThreshold();

        if ($stopOnWarning > 0 && $numberOfWarnings >= $stopOnWarning) {
            return true;
        }

        $stopOnRisky = $configuration->stopOnRiskyThreshold();

        if ($stopOnRisky > 0 && $numberOfRisky >= $stopOnRisky) {
            return true;
        }

        if (self::stopOnDeprecation($configuration)) {
            return true;
        }

        $stopOnNotice = $configuration->stopOnNoticeThreshold();

        if ($stopOnNotice > 0 && $collector->numberOfNotices() >= $stopOnNotice) {
            return true;
        }

        $stopOnIncomplete = $configuration->stopOnIncompleteThreshold();

        if ($stopOnIncomplete > 0 && $collector->numberOfIncompleteTests() >= $stopOnIncomplete) {
            return true;
        }

        $stopOnSkipped = $configuration->stopOnSkippedThreshold();

        if ($stopOnSkipped > 0 && $collector->numberOfSkippedTests() >= $stopOnSkipped) {
            return true;
        }

        return false;
    }

    private static function collector(): Collector
    {
        if (self::$collector === null) {
            $configuration = ConfigurationRegistry::get();

            self::$collector = new Collector(
                EventFacade::instance(),
                new IssueFilter($configuration->source()),
            );
        }

        return self::$collector;
    }

    private static function stopOnDeprecation(Configuration $configuration): bool
    {
        $threshold = $configuration->stopOnDeprecationThreshold();

        if ($threshold === 0) {
            return false;
        }

        $deprecations = DeprecationCollectorFacade::filteredDeprecations();

        if ($configuration->hasSpecificDeprecationToStopOn()) {
            $deprecations = array_filter(
                $deprecations,
                static fn (string $deprecation) => str_contains(
                    $deprecation,
                    $configuration->specificDeprecationToStopOn(),
                ),
            );
        }

        return count($deprecations) >= $threshold;
    }
}
