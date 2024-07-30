<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use PHPUnit\Event\Facade;
use PHPUnit\Event\TestSuite\Started;
use PHPUnit\Event\TestSuite\StartedSubscriber;
use PHPUnit\Event\Subscriber;

/**
 * Represents a valid external logger as it implements \PHPUnit\Logger\ExternalLogger.
 */
class ExternalLogger implements \PHPUnit\Logging\ExternalLogger
{
    public static function createInstance(Facade $facade): object
    {
        return new ExternalLogger($facade);
    }
    
    public function __construct(Facade $facade)
    {
        $facade->registerSubscriber(new class implements Subscriber, StartedSubscriber
        {
            public function notify(Started $event): void
            {
                // $this->logger()->testSuiteStarted($event);
            }
        });
    }
}
