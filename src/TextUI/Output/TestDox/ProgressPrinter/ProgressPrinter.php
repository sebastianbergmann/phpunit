<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Output\TestDox\ProgressPrinter;

use PHPUnit\Event\EventFacadeIsSealedException;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\UnknownSubscriberTypeException;
use PHPUnit\Util\Printer;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ProgressPrinter
{
    private Printer $printer;
    private bool $colors;

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    public function __construct(Printer $printer, bool $colors)
    {
        $this->printer = $printer;
        $this->colors  = $colors;

        $this->registerSubscribers();
    }

    public function testPrepared(Prepared $event): void
    {
    }

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    private function registerSubscribers(): void
    {
        Facade::registerSubscriber(new TestPreparedSubscriber($this));
    }
}
