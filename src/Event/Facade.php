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

final class Facade
{
    private ?TypeMap $typeMap = null;

    private ?Emitter $emitter = null;

    /**
     * @param class-string $subscriberInterface
     * @param class-string $eventClass
     */
    public function registerTypeMapping(string $subscriberInterface, string $eventClass): void
    {
        $this->typeMap()->addMapping($subscriberInterface, $eventClass);
    }

    public function emitter(): Emitter
    {
        if ($this->emitter === null) {
            $this->emitter = new Emitter(new Dispatcher($this->typeMap()));
        }

        return $this->emitter;
    }

    private function typeMap(): TypeMap
    {
        if ($this->typeMap === null) {
            $this->typeMap = new TypeMap();

            $this->typeMap->addMapping(Execution\BeforeExecutionSubscriber::class, Execution\BeforeExecution::class);
            $this->typeMap->addMapping(Run\AfterRunSubscriber::class, Run\AfterRun::class);
            $this->typeMap->addMapping(Run\BeforeRunSubscriber::class, Run\BeforeRun::class);
            $this->typeMap->addMapping(Test\AfterLastTestSubscriber::class, Test\AfterLastTest::class);
            $this->typeMap->addMapping(Test\AfterTestSubscriber::class, Test\AfterTest::class);
            $this->typeMap->addMapping(Test\BeforeFirstTestSubscriber::class, Test\BeforeFirstTest::class);
            $this->typeMap->addMapping(Test\BeforeTestSubscriber::class, Test\BeforeTest::class);
            $this->typeMap->addMapping(TestSuite\AfterTestSuiteSubscriber::class, TestSuite\AfterTestSuite::class);
            $this->typeMap->addMapping(TestSuite\BeforeTestSuiteSubscriber::class, TestSuite\BeforeTestSuite::class);
        }

        return $this->typeMap;
    }
}
