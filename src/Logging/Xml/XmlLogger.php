<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Logging\Xml;

use PHPUnit\Event\EventFacadeIsSealedException;
use PHPUnit\Event\Facade;
use PHPUnit\Event\UnknownSubscriberTypeException;
use PHPUnit\TextUI\Output\Printer;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class XmlLogger
{
    private readonly Printer $printer;

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    public function __construct(Printer $printer, Facade $facade)
    {
        $this->printer = $printer;

        $this->registerSubscribers($facade);
    }

    public function flush(): void
    {
        $this->printer->print('todo');

        $this->printer->flush();
    }

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    private function registerSubscribers(Facade $facade): void
    {
        $facade->registerSubscribers(
            new TestRunnerExecutionFinishedSubscriber($this),
        );
    }
}
