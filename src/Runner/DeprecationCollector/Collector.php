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

use PHPUnit\Event\EventFacadeIsSealedException;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Test\DeprecationTriggered;
use PHPUnit\Event\UnknownSubscriberTypeException;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Collector
{
    /**
     * @psalm-var list<non-empty-string>
     */
    private array $deprecations = [];

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    public function __construct(Facade $facade)
    {
        $facade->registerSubscribers(
            new TestPreparedSubscriber($this),
            new TestTriggeredDeprecationSubscriber($this),
        );
    }

    /**
     * @psalm-return list<non-empty-string>
     */
    public function deprecations(): array
    {
        return $this->deprecations;
    }

    public function testPrepared(): void
    {
        $this->deprecations = [];
    }

    public function testTriggeredDeprecation(DeprecationTriggered $event): void
    {
        $this->deprecations[] = $event->message();
    }
}
