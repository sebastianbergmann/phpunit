<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelWorkerExtension\Recycling;

use const FILE_APPEND;
use const LOCK_EX;
use const PHP_EOL;
use function file_put_contents;
use function getenv;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParallelWorkerExtension;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\Runner\Extension\WorkerFacade;
use PHPUnit\TextUI\Configuration\Configuration;

/**
 * Records, per worker process, that it was bootstrapped and that it was shut
 * down, so that the test can check that every worker process that was
 * bootstrapped — including those that replaced a recycled one — was shut
 * down as well.
 */
final class Extension implements ParallelWorkerExtension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
    }

    public function bootstrapWorker(Configuration $configuration, WorkerFacade $facade, ParameterCollection $parameters): void
    {
        $this->log('bootstrapped');
    }

    public function shutdownWorker(): void
    {
        $this->log('shut down');
    }

    private function log(string $what): void
    {
        file_put_contents(
            (string) getenv('PHPUNIT_TEST_WORKER_LIFECYCLE_LOG'),
            getenv('PHPUNIT_WORKER_TOKEN') . ' ' . $what . PHP_EOL,
            FILE_APPEND | LOCK_EX,
        );
    }
}
