<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelWorkerExtension\ProcessOfOrigin;

use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParallelWorkerExtension;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\Runner\Extension\WorkerFacade;
use PHPUnit\TextUI\Configuration\Configuration;

/**
 * Registers the same subscriber in the main process and in the worker
 * processes: a test that runs in a worker is to be counted there, and a test
 * that runs in the main process is to be counted there.
 */
final class Extension implements ParallelWorkerExtension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $facade->registerSubscriber(new CountingSubscriber);
    }

    public function bootstrapWorker(Configuration $configuration, WorkerFacade $facade, ParameterCollection $parameters): void
    {
        $facade->registerSubscriber(new CountingSubscriber);
    }

    public function shutdownWorker(): void
    {
    }
}
