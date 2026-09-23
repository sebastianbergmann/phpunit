<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelWorkerExtension\Worker;

use const FILE_APPEND;
use const LOCK_EX;
use const PHP_EOL;
use function file_put_contents;
use function getenv;
use PHPUnit\Runner\Extension\ChildProcessExtension;
use PHPUnit\Runner\Extension\ChildProcessFacade;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

final class Extension implements ChildProcessExtension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        // Nothing is registered in the main process: what this extension
        // does, it does inside the process that runs the tests.
    }

    public function bootstrapChildProcess(Configuration $configuration, ChildProcessFacade $facade, ParameterCollection $parameters): void
    {
        $facade->registerSubscribers(
            new PreparationStartedSubscriber,
            new FinishedSubscriber,
        );
    }

    public function shutdownChildProcess(): void
    {
        file_put_contents(
            (string) getenv('PHPUNIT_TEST_FINISHED_LOG'),
            'worker ' . getenv('PHPUNIT_WORKER_ID') . ' shut down' . PHP_EOL,
            FILE_APPEND | LOCK_EX,
        );
    }
}
