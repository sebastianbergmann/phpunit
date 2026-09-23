<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ChildProcessExtension\EndToEnd;

use PHPUnit\Runner\Extension\ChildProcessExtension;
use PHPUnit\Runner\Extension\ChildProcessFacade;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

final class Extension implements ChildProcessExtension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        Log::write('bootstrap() with label ' . $parameters->get('label'));
    }

    public function bootstrapChildProcess(Configuration $configuration, ChildProcessFacade $facade, ParameterCollection $parameters): void
    {
        Log::write('bootstrapChildProcess() with label ' . $parameters->get('label'));

        $facade->registerSubscribers(
            new PreparedSubscriber,
            new FinishedSubscriber,
        );
    }

    public function shutdownChildProcess(): void
    {
        Log::write('shutdownChildProcess()');

        print 'this output is not shown';
    }
}
