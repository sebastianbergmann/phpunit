<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\DeprecationCollector;

use PHPUnit\Event\CollectingDispatcher;
use PHPUnit\Event\EventFacadeIsSealedException;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Event\UnknownSubscriberTypeException;
use PHPUnit\TestRunner\IssueFilter;
use PHPUnit\TextUI\Configuration\Registry as ConfigurationRegistry;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Facade
{
    private static null|Collector|InIsolationCollector $collector = null;
    private static bool $inIsolation                              = false;

    public static function init(): void
    {
        self::collector();
    }

    /**
     * Prepares the collector for a process that runs tests in isolation from
     * the main process and forwards their events to it: the child process of
     * a test that runs in process isolation, or a worker process of a
     * parallel test run.
     *
     * A worker process runs more than one test, so the deprecations that the
     * expectations of a test are verified against are forgotten when the next
     * test is prepared, as they are in the main process.
     *
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    public static function initForIsolation(CollectingDispatcher $dispatcher): void
    {
        $collector = self::collector();

        self::$inIsolation = true;

        if ($collector instanceof Collector) {
            $dispatcher->registerSubscriber(new TestPreparedSubscriber($collector));
        }
    }

    /**
     * @return list<non-empty-string>
     */
    public static function deprecations(): array
    {
        return self::collector()->deprecations();
    }

    /**
     * @return list<non-empty-string>
     */
    public static function filteredDeprecations(): array
    {
        /*
         * A process that runs tests in isolation from the main process does not
         * decide whether the test run is stopped: the main process decides that,
         * from the deprecations that the isolated process forwards to it.
         */
        if (self::$inIsolation) {
            return [];
        }

        return self::collector()->filteredDeprecations();
    }

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    public static function collector(): Collector|InIsolationCollector
    {
        if (self::$collector !== null) {
            return self::$collector;
        }

        $issueFilter = new IssueFilter(
            ConfigurationRegistry::get()->source(),
        );

        if (self::$inIsolation) {
            self::$collector = new InIsolationCollector(
                $issueFilter,
            );

            return self::$collector;
        }

        self::$collector = new Collector(
            EventFacade::instance(),
            $issueFilter,
        );

        return self::$collector;
    }
}
