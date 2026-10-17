<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelWorkerExtension\EventsOfThisProcess;

use function getenv;
use PHPUnit\Runner\Extension\ChildProcessExtension;
use PHPUnit\Runner\Extension\ChildProcessFacade;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

/**
 * Registers the same subscriber in the main process and in the child
 * processes: a test that runs in a worker or in a separate process is to be
 * counted there, and a test that runs in the main process is to be counted
 * there. In the main process, the subscriber is registered for the events of
 * that process only, so that it is not notified of the replayed events of the
 * tests that ran in a worker or in a separate process.
 */
final class Extension implements ChildProcessExtension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $facade->registerSubscriberForEventsOfThisProcess(new CountingSubscriber('main process'));
    }

    public function bootstrapChildProcess(Configuration $configuration, ChildProcessFacade $facade, ParameterCollection $parameters): void
    {
        $process = 'separate process';

        if (getenv('PHPUNIT_WORKER_ID') !== false) {
            $process = 'worker process';
        }

        $facade->registerSubscriber(new CountingSubscriber($process));
    }

    public function shutdownChildProcess(): void
    {
    }
}
