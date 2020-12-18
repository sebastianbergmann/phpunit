<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event;

use function array_fill_keys;
use function array_key_exists;
use function get_class;

final class Dispatcher
{
    /**
     * @var array<class-string, class-string>
     */
    private static array $map = [
        Run\AfterRunSubscriber::class              => Run\AfterRun::class,
        Run\BeforeRunSubscriber::class             => Run\BeforeRun::class,
        Test\AfterLastTestSubscriber::class        => Test\AfterLastTest::class,
        Test\AfterTestSubscriber::class            => Test\AfterTest::class,
        Test\BeforeFirstTestSubscriber::class      => Test\BeforeFirstTest::class,
        Test\BeforeTestSubscriber::class           => Test\BeforeTest::class,
        TestSuite\AfterTestSuiteSubscriber::class  => TestSuite\AfterTestSuite::class,
        TestSuite\BeforeTestSuiteSubscriber::class => TestSuite\BeforeTestSuite::class,
    ];

    /**
     * @var array<string, array<int, Subscriber>>
     */
    private array $subscribers;

    public function __construct()
    {
        $this->subscribers = array_fill_keys(self::$map, []);
    }

    public function register(Subscriber $subscriber): void
    {
        foreach (self::$map as $subscriberInterfaceName => $eventClassName) {
            if ($subscriber instanceof $subscriberInterfaceName) {
                $this->subscribers[$eventClassName][] = $subscriber;

                return;
            }
        }
    }

    public function dispatch(Event $event): void
    {
        $eventClassName = get_class($event);

        if (!array_key_exists($eventClassName, $this->subscribers)) {
            return;
        }

        foreach ($this->subscribers[$eventClassName] as $subscriber) {
            $subscriber->notify($event);
        }
    }
}
