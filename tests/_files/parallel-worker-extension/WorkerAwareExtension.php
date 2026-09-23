<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelWorkerExtension;

use PHPUnit\Runner\Extension\ChildProcessExtension;
use PHPUnit\Runner\Extension\ChildProcessFacade;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

final class WorkerAwareExtension implements ChildProcessExtension
{
    public static ?ParameterCollection $workerParameters = null;
    public static int $shutdowns                         = 0;

    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
    }

    public function bootstrapChildProcess(Configuration $configuration, ChildProcessFacade $facade, ParameterCollection $parameters): void
    {
        self::$workerParameters = $parameters;

        $facade->registerSubscriber(new RecordingSubscriber);
    }

    public function shutdownChildProcess(): void
    {
        self::$shutdowns++;
    }
}
