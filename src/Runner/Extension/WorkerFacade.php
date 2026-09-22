<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Extension;

use PHPUnit\Event\Subscriber;

/**
 * What a parallel worker process offers an extension that is bootstrapped in
 * it: the registration of subscribers that receive the events of the tests
 * the worker runs.
 *
 * Nothing else that the main process offers an extension applies in a worker.
 * Output is produced by the main process, so there is no output to replace;
 * tracers and code coverage are the main process' concerns as well.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
interface WorkerFacade
{
    public function registerSubscribers(Subscriber ...$subscribers): void;

    public function registerSubscriber(Subscriber $subscriber): void;
}
